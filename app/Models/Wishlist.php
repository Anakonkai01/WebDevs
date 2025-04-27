<?php

namespace App\Models;

use App\Core\Database;
// Wishlist không kế thừa BaseModel

class Wishlist {

    // thêm sản phẩm vào wishlist
    public static function add(int $userId, int $productId): bool {
        $sql = "INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)";
        $stmt = Database::prepare($sql, "ii", [$userId, $productId]);
        if ($stmt && $stmt->execute()) {
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            return $affectedRows === 1;
        }
        if ($stmt) $stmt->close();
        return false;
    }

    // xóa sản phẩm khỏi wishlist
    public static function remove(int $userId, int $productId): bool {
        $sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
        $stmt = Database::prepare($sql, "ii", [$userId, $productId]);
        if ($stmt && $stmt->execute()) {
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            return $affectedRows === 1;
        }
        if ($stmt) $stmt->close();
        return false;
    }

    // lấy danh sách sản phẩm yêu thích
    public static function getByUser(int $userId): array {
        $sql = "SELECT
                    p.id, p.name, p.price, p.image, p.stock, p.brand,
                    w.added_at
                FROM wishlist w
                JOIN products p ON w.product_id = p.id
                WHERE w.user_id = ?
                ORDER BY w.added_at DESC";
        $stmt = Database::prepare($sql, "i", [$userId]);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
            return $data;
        }
        if ($stmt) $stmt->close();
        return [];
    }

    // kiểm tra sản phẩm có trong wishlist không
    public static function isWishlisted(int $userId, int $productId): bool {
        $sql = "SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?";
        $stmt = Database::prepare($sql, "ii", [$userId, $productId]);
        if ($stmt && $stmt->execute()) {
            $stmt->store_result();
            $numRows = $stmt->num_rows;
            $stmt->close();
            return $numRows > 0;
        }
        if ($stmt) $stmt->close();
        return false;
    }

    // lấy mảng id sản phẩm trong wishlist
    public static function getWishlistedProductIds(int $userId): array {
        $sql = "SELECT product_id FROM wishlist WHERE user_id = ?";
        $stmt = Database::prepare($sql, "i", [$userId]);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
            return array_column($data, 'product_id');
        }
        if ($stmt) $stmt->close();
        return [];
    }
}