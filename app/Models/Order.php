<?php
// Web/app/Models/Order.php
require_once 'BaseModel.php';

class Order extends BaseModel
{
    protected static string $table = 'orders';

    /**
     * Tạo đơn hàng mới và trả về ID của đơn hàng vừa tạo
     *
     * @param int $user_id ID người dùng
     * @param float $total Tổng tiền
     * @param string $customer_name Tên người nhận
     * @param string $customer_address Địa chỉ nhận
     * @param string $customer_phone SĐT người nhận
     * @param string|null $customer_email Email người nhận (có thể null)
     * @param string|null $notes Ghi chú (có thể null)
     * @param string $status Trạng thái ban đầu (mặc định 'Pending')
     * @return int|false ID đơn hàng nếu thành công, false nếu thất bại
     */
    public static function createAndGetId(
        int $user_id,
        float $total,
        string $customer_name,
        string $customer_address,
        string $customer_phone,
        ?string $customer_email = null,
        ?string $notes = null,
        string $status = 'Pending'
    ) {
        $sql = "INSERT INTO orders(user_id, total, customer_name, customer_address, customer_phone, customer_email, notes, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"; // Thêm created_at
        $stmt = Database::prepare($sql, "idssssss", [ // Kiểu dữ liệu: integer, double, string, string, string, string, string, string
            $user_id, $total, $customer_name, $customer_address, $customer_phone, $customer_email, $notes, $status
        ]);

        if ($stmt && $stmt->execute()) {
            // Trả về ID của bản ghi vừa được chèn
            $lastId = $stmt->insert_id;
            $stmt->close(); // Đóng statement
            return $lastId;
        }
        if ($stmt) $stmt->close(); // Đóng nếu execute lỗi
        return false; // Trả về false nếu có lỗi
    }

    // Các hàm find(), getByUser(), delete() giữ nguyên từ BaseModel hoặc class Order cũ
    public static function find(int $id): ?array
    {
        return parent::find($id); // dùng lại từ BaseModel
    }

    /**
     * Lấy danh sách đơn hàng của user, hỗ trợ lọc và phân trang
     * @param int $user_id ID người dùng
     * @param string $statusFilter Trạng thái cần lọc ('all', 'Pending', 'Processing', 'Shipped', etc.)
     * @param int|null $limit Số lượng đơn hàng mỗi trang (null để lấy tất cả)
     * @param int $offset Vị trí bắt đầu lấy
     * @return array Mảng các đơn hàng
     */
    public static function getByUser(int $user_id, string $statusFilter = 'all', ?int $limit = null, int $offset = 0): array
    {
        $params = [$user_id];
        $types = "i";
        $sql = "SELECT * FROM orders WHERE user_id = ?";

        // Thêm điều kiện lọc theo status nếu không phải 'all'
        if ($statusFilter !== 'all' && !empty($statusFilter)) {
            $sql .= " AND status = ?";
            $params[] = $statusFilter;
            $types .= "s";
        }

        $sql .= " ORDER BY created_at DESC"; // Sắp xếp mới nhất trước

        // Thêm LIMIT và OFFSET nếu có $limit
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= "ii";
        }

        $stmt = Database::prepare($sql, $types, $params);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
            return $data;
        }
        if ($stmt) $stmt->close();
        return [];
    }

    /**
     * Đếm tổng số đơn hàng của user, hỗ trợ lọc theo trạng thái
     * @param int $user_id ID người dùng
     * @param string $statusFilter Trạng thái cần lọc ('all', 'Pending', 'Processing', 'Shipped', etc.)
     * @return int Tổng số đơn hàng
     */
    public static function countByUser(int $user_id, string $statusFilter = 'all'): int
    {
        $params = [$user_id];
        $types = "i";
        $sql = "SELECT COUNT(*) as total FROM orders WHERE user_id = ?";

        // Thêm điều kiện lọc theo status nếu không phải 'all'
        if ($statusFilter !== 'all' && !empty($statusFilter)) {
            $sql .= " AND status = ?";
            $params[] = $statusFilter;
            $types .= "s";
        }

        $stmt = Database::prepare($sql, $types, $params);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $stmt->close();
            return $row ? (int)$row['total'] : 0;
        }
        if ($stmt) $stmt->close();
        return 0;
    }


    /**
     * Cập nhật trạng thái cho một đơn hàng cụ thể
     * @param int $orderId ID đơn hàng
     * @param string $newStatus Trạng thái mới
     * @return bool True nếu cập nhật thành công, False nếu thất bại
     */
    public static function updateStatus(int $orderId, string $newStatus): bool
    {
        $sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = Database::prepare($sql, "si", [$newStatus, $orderId]);
        if ($stmt && $stmt->execute()) {
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            // Trả về true nếu có đúng 1 dòng bị ảnh hưởng
            return $affectedRows === 1;
        }
        if ($stmt) $stmt->close();
        return false;
    }

    public static function delete(int $id): bool
    {
        // Lưu ý: Nên kiểm tra xem có được phép xóa đơn hàng không (ví dụ: chỉ xóa đơn Pending?)
        // Hoặc có thể thêm cột is_deleted thay vì xóa hẳn
        $sql = "DELETE FROM orders WHERE id = ?";
        $stmt = Database::prepare($sql, "i", [$id]);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
        // Khi xóa order, các order_items liên quan cũng nên được xóa (do có ON DELETE CASCADE trong DB)
    }
}