<?php
require_once __DIR__ . '/BaseModel.php';

class Review extends BaseModel
{
    /** Tên bảng trong Database **/
    protected static string $table = 'reviews';

    /* ──────────────────────── CREATE ──────────────────────── */
    /**
     * Thêm đánh giá cho sản phẩm
     *
     * @param int    $productId  ID sản phẩm
     * @param string $content    Nội dung đánh giá
     * @return bool              True nếu thành công
     */
    public static function create(int $productId, string $content): bool
    {
        $sql = "INSERT INTO reviews (product_id, content) VALUES (?, ?)";
        $stmt = Database::prepare($sql, "is", [$productId, $content]);
        return $stmt->execute();
    }

    /* ──────────────────────── READ ──────────────────────── */
    /**
     * Lấy danh sách đánh giá theo sản phẩm
     *
     * @param int $productId   ID sản phẩm
     * @return array           Mảng các review (id, product_id, content, created_at)
     */
    public static function getByProduct(int $productId): array
    {
        $sql = "SELECT * FROM reviews WHERE product_id = ? ORDER BY created_at DESC";
        $stmt = Database::prepare($sql, "i", [$productId]);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Xóa tất cả đánh giá của sản phẩm (nếu cần)
     *
     * @param int $productId  ID sản phẩm
     * @return bool           True nếu thành công
     */
    public static function deleteByProduct(int $productId): bool
    {
        $sql = "DELETE FROM reviews WHERE product_id = ?";
        $stmt = Database::prepare($sql, "i", [$productId]);
        return $stmt->execute();
    }

    /**
     * Đếm số lượng review của sản phẩm
     *
     * @param int $productId
     * @return int
     */
    public static function countByProduct(int $productId): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM reviews WHERE product_id = ?";
        $stmt = Database::prepare($sql, "i", [$productId]);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return isset($row['cnt']) ? (int)$row['cnt'] : 0;
    }
}
