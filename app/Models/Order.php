<?php
// Web/app/Models/Order.php
namespace App\Models;

use App\Core\Database;
// BaseModel cùng namespace
class Order extends BaseModel
{
    // tên bảng trong database
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
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = Database::prepare($sql, "idssssss", [
            $user_id, $total, $customer_name, $customer_address, $customer_phone, $customer_email, $notes, $status
        ]);

        if ($stmt && $stmt->execute()) {
            $lastId = $stmt->insert_id;
            $stmt->close();
            return $lastId;
        }
        if ($stmt) $stmt->close();
        return false;
    }

    // tìm đơn hàng theo id
    public static function find(int $id): ?array
    {
        return parent::find($id);
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

        if ($statusFilter !== 'all' && !empty($statusFilter)) {
            $sql .= " AND status = ?";
            $params[] = $statusFilter;
            $types .= "s";
        }

        $sql .= " ORDER BY created_at DESC";

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

    // đếm số lượng đơn hàng của user
    public static function countByUser(int $user_id, string $statusFilter = 'all'): int
    {
        $params = [$user_id];
        $types = "i";
        $sql = "SELECT COUNT(*) as total FROM orders WHERE user_id = ?";

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

    // cập nhật trạng thái đơn hàng
    public static function updateStatus(int $orderId, string $newStatus): bool
    {
        $sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = Database::prepare($sql, "si", [$newStatus, $orderId]);
        if ($stmt && $stmt->execute()) {
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            return $affectedRows === 1;
        }
        if ($stmt) $stmt->close();
        return false;
    }

    // xóa đơn hàng
    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM orders WHERE id = ?";
        $stmt = Database::prepare($sql, "i", [$id]);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}