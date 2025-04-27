<?php

namespace App\Models;
use App\Core\Database;

class Review extends BaseModel
{
    protected static string $table = 'reviews';

    /**
     * Create a new review
     */
    public static function create(int $productId, int $userId, string $content, ?int $rating = null): bool 
    {
        $sql = "INSERT INTO reviews (product_id, user_id, content, rating, created_at) VALUES (?, ?, ?, ?, NOW())";
        
        // Define the types of the parameters: integer, integer, string, integer (iis without rating, iisi with rating)
        $types = "iisi";

        // Parameters for the prepared statement.
        $params = [$productId, $userId, $content, $rating];

        // Prepare the SQL statement with the parameters and types.
        $stmt = Database::prepare($sql, $types, $params);        
        // Execute the statement.
        $success = $stmt->execute();
        $stmt->close();        
        return $success;
    }

    /**
     * Get reviews by product ID
     */
    public static function getByProduct(int $productId): array
    {   
        $sql = "SELECT r.*, u.username
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.product_id = ?
                ORDER BY r.created_at DESC";
        $stmt = Database::prepare($sql, "i", [$productId]);        
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();            
            // Assign default username if the username is null (e.g., if the user has been deleted).
            foreach ($data as &$row) {
                if (!isset($row['username'])) {
                    $row['username'] = 'Khách';
                }
            }           
            return $data;
        }
        if ($stmt) $stmt->close(); // Close the statement if it was prepared.
        return []; // Return an empty array if there was an error or no reviews found.
    }

    /**
     * Delete reviews by product ID
     * @param int $productId
     * @return bool
     */
    public static function deleteByProduct(int $productId): bool
    {
        $sql = "DELETE FROM reviews WHERE product_id = ?";
        $stmt = Database::prepare($sql, "i", [$productId]);
        return $stmt->execute();
    }

    /**
     * Count reviews by product ID
     */
    public static function countByProduct(int $productId): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM reviews WHERE product_id = ?";
        $stmt = Database::prepare($sql, "i", [$productId]);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $stmt->close();
            return $row ? (int)$row['cnt'] : 0;
        }
        if ($stmt) $stmt->close(); 
        return 0;
    }

    /**
     * Update average rating for product
     **/
    public static function updateProductAverageRating(int $productId): bool
    {
        $sqlAvg = "SELECT AVG(rating) as avg_rating FROM reviews WHERE product_id = ? AND rating IS NOT NULL";
        $stmtAvg = Database::prepare($sqlAvg, "i", [$productId]);
        $avgRating = 0;
        if ($stmtAvg && $stmtAvg->execute()) {
            $result = $stmtAvg->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            if ($row && $row['avg_rating'] !== null) {
                $avgRating = round((float)$row['avg_rating'], 1); // Round to 1 decimal place.
            }
            $stmtAvg->close();
        } else {
            if ($stmtAvg) $stmtAvg->close();
            return false; // Error when calculating the average.
        }

        $sqlUpd = "UPDATE products SET rating = ? WHERE id = ?";
        $stmtUpd = Database::prepare($sqlUpd, "di", [$avgRating, $productId]);
        if ($stmtUpd && $stmtUpd->execute()) {
            $stmtUpd->close();
            return true;
        }
        if ($stmtUpd) $stmtUpd->close();
        return false; 
    }
}