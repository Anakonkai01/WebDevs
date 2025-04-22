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

    public static function getByUser(int $user_id): array
    {
        $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC"; // Sắp xếp mới nhất trước
        $stmt = Database::prepare($sql, "i", [$user_id]);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $data;
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