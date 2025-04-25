<?php
// Web/app/Models/Wishlist.php

namespace App\Models;

use App\Core\Database;
// Wishlist không kế thừa BaseModel

class Wishlist {

    /**
     * Thêm sản phẩm vào danh sách yêu thích. Bỏ qua nếu đã tồn tại.
     * @param int $userId
     * @param int $productId
     * @return bool True nếu thêm mới thành công, False nếu đã tồn tại hoặc lỗi.
     */
    public static function add(int $userId, int $productId): bool {
        // INSERT IGNORE sẽ không báo lỗi nếu cặp (user_id, product_id) đã có do UNIQUE KEY
        $sql = "INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)";
        $stmt = Database::prepare($sql, "ii", [$userId, $productId]);
        if ($stmt && $stmt->execute()) {
            // execute thành công. Kiểm tra affected_rows để biết có insert thực sự không
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            return $affectedRows === 1; // Chỉ true khi thực sự thêm mới được 1 dòng
        }
        if ($stmt) $stmt->close();
        return false; // Lỗi
    }

    /**
     * Xóa sản phẩm khỏi danh sách yêu thích.
     * @param int $userId
     * @param int $productId
     * @return bool True nếu xóa thành công (có dòng bị ảnh hưởng), False nếu không tìm thấy hoặc lỗi.
     */
    public static function remove(int $userId, int $productId): bool {
        $sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
        $stmt = Database::prepare($sql, "ii", [$userId, $productId]);
        if ($stmt && $stmt->execute()) {
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            return $affectedRows === 1; // Chỉ true khi có đúng 1 dòng bị xóa
        }
        if ($stmt) $stmt->close();
        return false;
    }

    /**
     * Lấy danh sách sản phẩm yêu thích của người dùng (kèm thông tin sản phẩm).
     * @param int $userId
     * @return array Mảng chứa thông tin các sản phẩm trong wishlist
     */
    public static function getByUser(int $userId): array {
        $sql = "SELECT
                    p.id, p.name, p.price, p.image, p.stock, p.brand, -- Lấy cột cần thiết từ products
                    w.added_at -- Lấy ngày thêm từ wishlist
                FROM wishlist w
                JOIN products p ON w.product_id = p.id
                WHERE w.user_id = ?
                ORDER BY w.added_at DESC"; // Sắp xếp sản phẩm mới thêm lên đầu
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

    /**
     * Kiểm tra xem một sản phẩm có trong wishlist của người dùng không.
     * @param int $userId
     * @param int $productId
     * @return bool True nếu có, False nếu không.
     */
    public static function isWishlisted(int $userId, int $productId): bool {
        $sql = "SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?";
        $stmt = Database::prepare($sql, "ii", [$userId, $productId]);
        if ($stmt && $stmt->execute()) {
            $stmt->store_result(); // Quan trọng để lấy num_rows
            $numRows = $stmt->num_rows;
            $stmt->close();
            return $numRows > 0;
        }
        if ($stmt) $stmt->close();
        return false;
    }

    /**
     * Lấy mảng các ID sản phẩm trong wishlist của người dùng.
     * Dùng để kiểm tra nhanh trạng thái yêu thích trên trang danh sách sản phẩm.
     * @param int $userId
     * @return array Ví dụ: [5, 12, 23]
     */
    public static function getWishlistedProductIds(int $userId): array {
        $sql = "SELECT product_id FROM wishlist WHERE user_id = ?";
        $stmt = Database::prepare($sql, "i", [$userId]);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
            // Trả về một mảng chỉ chứa giá trị của cột 'product_id'
            return array_column($data, 'product_id');
        }
        if ($stmt) $stmt->close();
        return [];
    }
}