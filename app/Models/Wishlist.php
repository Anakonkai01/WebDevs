<?php

namespace App\Models;

use App\Core\Database; // Import Database

class Wishlist
{

    /** 
     * Add a product to wishlist
     */
    public static function add(int $userId, int $productId): bool
    {
        $sql = "INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)"; // SQL to insert into wishlist
        $stmt = Database::prepare($sql, "ii", [$userId, $productId]);

        if ($stmt && $stmt->execute()) {
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            return $affectedRows === 1; // Return true if added
        }
        if ($stmt) {
            $stmt->close();
        }
        return false; // return false if have error
    }

    /** 
     * Remove a product from wishlist
     */
    public static function remove(int $userId, int $productId): bool
    {
        $sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?"; // SQL to delete wishlist
        $stmt = Database::prepare($sql, "ii", [$userId, $productId]);

        if ($stmt && $stmt->execute()) {
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            return $affectedRows === 1; // return true if removed
        }
        if ($stmt) {
            $stmt->close();
        }
        return false; // return false if have error
    }

    /**
     * Gets a user's wishlist products along with product details.
     */
    public static function getByUser(int $userId): array
    {
        $sql = "SELECT
                    p.id, p.name, p.price, p.image, p.stock, p.brand,
                    w.added_at
                FROM wishlist w
                JOIN products p ON w.product_id = p.id
                WHERE w.user_id = ?
                ORDER BY w.added_at DESC"; // SQL to get wishlist
        $stmt = Database::prepare($sql, "i", [$userId]);

        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
            return $data; // return data of wishlist
        }
        if ($stmt) {
            $stmt->close();
        }
        return []; // return empty if have error
    }

    /** Check if a product in wishlist
     */
    public static function isWishlisted(int $userId, int $productId): bool
    {
        $sql = "SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?"; // SQL check product in wishlist
        $stmt = Database::prepare($sql, "ii", [$userId, $productId]);
        if ($stmt && $stmt->execute()) {
            $stmt->store_result();
            $numRows = $stmt->num_rows;
            $stmt->close();
            return $numRows > 0; // return true if in wishlist
        }
        if ($stmt) {
            $stmt->close();
        }
        return false; // return false if have error
    }

    /** Get all id of product in wishlist
     */
    public static function getWishlistedProductIds(int $userId): array {
        $sql = "SELECT product_id FROM wishlist WHERE user_id = ?"; // SQL get all ids
        $stmt = Database::prepare($sql, "i", [$userId]);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
            return array_column($data, 'product_id'); // return array of id
        }
        if ($stmt) $stmt->close();
        return []; // return empty if error
    }
}