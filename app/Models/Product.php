<?php
require_once "BaseModel.php";

class Product extends BaseModel
{
    /** tên bảng trong CSDL */
    protected static string $table = "products";

    /* ───────────────────────  CRUD  ─────────────────────── */

    /** Thêm sản phẩm */
    public static function create(
        string $name,
        string $desc,
        float  $price,
        string $image,
        int    $stock,
        string $brand,
        float  $rating = 0.0        // rating mặc định = 0
    ): bool {
        $sql = "
            INSERT INTO products
                (name, description, price, image, stock, brand, rating)
            VALUES (?,?,?,?,?,?,?)
        ";
        $stmt = Database::prepare($sql, "ssdsi sd", [
            $name, $desc, $price, $image, $stock, $brand, $rating
        ]);
        return $stmt->execute();
    }

    /** Cập nhật sản phẩm */
    public static function update(
        int    $id,
        string $name,
        string $desc,
        float  $price,
        string $image,
        int    $stock,
        string $brand,
        float  $rating
    ): bool {
        $sql = "
            UPDATE products SET
                name = ?, description = ?, price = ?, image = ?,
                stock = ?, brand = ?, rating = ?
            WHERE id = ?
        ";
        $stmt = Database::prepare($sql, "ssdsisdi", [
            $name, $desc, $price, $image, $stock, $brand, $rating, $id
        ]);
        return $stmt->execute();
    }

    /** Xoá sản phẩm */
    public static function delete(int $id): bool
    {
        $stmt = Database::prepare("DELETE FROM products WHERE id = ?", "i", [$id]);
        return $stmt->execute();
    }

    /* ─────────────────  TRUY VẤN PHỤC VỤ TRANG HOME  ───────────────── */

    /** Tìm kiếm theo tên */
    public static function searchByName(string $keyword): array
    {
        $like = "%$keyword%";
        $stmt = Database::prepare(
            "SELECT * FROM products WHERE name LIKE ?",
            "s",
            [$like]
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Lọc theo hãng (brand) */
    public static function getByBrand(string $brand): array
    {
        $stmt = Database::prepare(
            "SELECT * FROM products WHERE brand = ?",
            "s",
            [$brand]
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Lấy sản phẩm mới nhất */
    public static function getLatest(int $limit = 6): array
    {
        $stmt = Database::prepare(
            "SELECT * FROM products ORDER BY created_at DESC LIMIT ?",
            "i",
            [$limit]
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Top‑rated (đánh giá cao nhất) */
    public static function getTopRated(int $limit = 5): array
    {
        $stmt = Database::prepare(
            "SELECT * FROM products ORDER BY rating DESC LIMIT ?",
            "i",
            [$limit]
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Sản phẩm có nhiều review nhất */
    public static function getMostReviewed(int $limit = 5): array
    {
        $sql = "
            SELECT p.*, COUNT(r.id) AS review_count
            FROM products p
            JOIN reviews r ON p.id = r.product_id
            GROUP BY p.id
            ORDER BY review_count DESC
            LIMIT ?
        ";
        $stmt = Database::prepare($sql, "i", [$limit]);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function getDistinctBrands(): array {
        $stmt = Database::prepare("SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand != '' ORDER BY brand ASC");
        if (!$stmt || !$stmt->execute()) return []; // Thêm kiểm tra lỗi cơ bản
        $result = $stmt->get_result();
        if (!$result) return [];
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return array_column($data, 'brand'); // Trả về mảng tên hãng
    }
}
