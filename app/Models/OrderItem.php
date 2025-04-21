<?php
require_once 'BaseModel.php';

class OrderItem extends BaseModel
{
    /** Tên bảng */
    protected static string $table = 'order_items';

    /* ──────────────────────────── CREATE ──────────────────────────── */
    /** Thêm mới một item vào đơn */
    public static function create(int $orderId, int $productId, int $quantity, float $price): bool
    {
        $sql = "
            INSERT INTO order_items
                (order_id, product_id, quantity, price)
            VALUES (?,?,?,?)
        ";
        $stmt = Database::prepare($sql, "iiid", [
            $orderId, $productId, $quantity, $price
        ]);
        return $stmt->execute();
    }

    /* ──────────────────────── READ ──────────────────────── */
    /** Lấy array các item thô theo order_id */
    public static function getItemsByOrder(int $orderId): array
    {
        $sql = "SELECT * FROM order_items WHERE order_id = ?";
        $stmt = Database::prepare($sql, "i", [$orderId]);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Lấy chi tiết item kèm thông tin sản phẩm (name, image, stock) */
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
        $stmt = Database::prepare($sql, "i", [$orderId]);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /* ───────────────────── UPDATE ───────────────────── */
    /** Cập nhật số lượng cho một item */
    public static function updateQuantity(int $orderId, int $productId, int $quantity): bool
    {
        $sql = "
            UPDATE order_items
            SET quantity = ?
            WHERE order_id = ? AND product_id = ?
        ";
        $stmt = Database::prepare($sql, "iii", [
            $quantity, $orderId, $productId
        ]);
        return $stmt->execute();
    }

    /* ───────────────────── DELETE ───────────────────── */
    /** Xoá một item khỏi đơn */
    public static function delete(int $orderId, int $productId): bool
    {
        $sql = "
            DELETE FROM order_items
            WHERE order_id = ? AND product_id = ?
        ";
        $stmt = Database::prepare($sql, "ii", [$orderId, $productId]);
        return $stmt->execute();
    }

    /** Xoá toàn bộ item của một order */
    public static function deleteByOrder(int $orderId): bool
    {
        $sql = "DELETE FROM order_items WHERE order_id = ?";
        $stmt = Database::prepare($sql, "i", [$orderId]);
        return $stmt->execute();
    }

    /* ───────────────────── UTILITY ───────────────────── */
    /** Tính tổng tiền của toàn bộ order */
    public static function calcTotal(int $orderId): float
    {
        $sql = "
            SELECT SUM(quantity * price) AS total
            FROM order_items
            WHERE order_id = ?
        ";
        $stmt = Database::prepare($sql, "i", [$orderId]);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return isset($row['total']) ? (float)$row['total'] : 0.0;
    }
}
