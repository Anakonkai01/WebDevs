<?php
namespace App\Models;
use App\Core\Database;

class OrderItem extends BaseModel
{
    // Tên bảng trong database
    protected static string $table = 'order_items';

    /*
     * Tạo một order item mới
     */
    public static function create(int $orderId, int $productId, int $quantity, float $price): bool
    {
        $sql = "
            INSERT INTO order_items
                (order_id, product_id, quantity, price)
            VALUES (?,?,?,?)
        ";
        // Chuẩn bị câu lệnh SQL
        $stmt = Database::prepare($sql, "iiid", [
            $orderId, $productId, $quantity, $price
        ]);
        // Thực thi và trả về kết quả
        return $stmt->execute();
    }

    // Lấy danh sách order items theo order ID
    public static function getItemsByOrder(int $orderId): array
    {
        $sql = "SELECT * FROM order_items WHERE order_id = ?";
        // Chuẩn bị câu lệnh SQL
        $stmt = Database::prepare($sql, "i", [$orderId]);
        // Thực thi câu lệnh
        $stmt->execute();
        // Lấy tất cả các dòng và trả về mảng
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Lấy thông tin chi tiết order item và thông tin sản phẩm
    public static function getDetailedByOrder(int $orderId): array
    {
        $sql = "
            SELECT
                oi.order_id,
                oi.product_id,
                oi.quantity,
                oi.price      AS item_price,
                p.name        AS product_name,
                p.image       AS product_image,
                p.stock       AS product_stock
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ";
        // Chuẩn bị câu lệnh SQL
        $stmt = Database::prepare($sql, "i", [$orderId]);
        // Thực thi câu lệnh
        $stmt->execute();
        // Lấy tất cả các dòng và trả về mảng
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Cập nhật số lượng của order item
    public static function updateQuantity(int $orderId, int $productId, int $quantity): bool
    {
        $sql = "
            UPDATE order_items
            SET quantity = ?
            WHERE order_id = ? AND product_id = ?
        ";
        // Chuẩn bị câu lệnh SQL
        $stmt = Database::prepare($sql, "iii", [
            $quantity, $orderId, $productId
        ]);
        // Thực thi và trả về kết quả
        return $stmt->execute();
    }

    /*
     * Xóa một order item
     */
    public static function delete(int $orderId, int $productId): bool
    {
        $sql = "
            DELETE FROM order_items
            WHERE order_id = ? AND product_id = ?
        ";
        // Chuẩn bị câu lệnh SQL
        $stmt = Database::prepare($sql, "ii", [$orderId, $productId]);
        // Thực thi và trả về kết quả
        return $stmt->execute();
    }

    // Xóa tất cả order item của order
    public static function deleteByOrder(int $orderId): bool
    {
        $sql = "DELETE FROM order_items WHERE order_id = ?";
        // Chuẩn bị câu lệnh SQL
        $stmt = Database::prepare($sql, "i", [$orderId]);
        return $stmt->execute();
    }

    // Tính tổng tiền của order
    public static function calcTotal(int $orderId): float
   {
        $sql = "
            SELECT SUM(quantity * price) AS total
            FROM order_items
            WHERE order_id = ?
        ";
        // Chuẩn bị câu lệnh SQL
        $stmt = Database::prepare($sql, "i", [$orderId]);
        // Thực thi câu lệnh
        $stmt->execute();

        // Lấy kết quả
        $row = $stmt->get_result()->fetch_assoc();

        // Kiểm tra và trả về tổng tiền
        return isset($row['total']) ? (float)$row['total'] : 0.0;
    }
}
