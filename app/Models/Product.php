<?php
namespace App\Models; // <--- Namespace

use App\Core\Database; // <-- Use Database
use Exception;          // <-- Use Exception global (nếu có dùng try-catch)
// BaseModel cùng namespace, không cần use

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
        if ($brand === "All"){
            return self::getLatest(12);
        }
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



    /* ───────────────── TRUY VẤN PHỤC VỤ TRANG SHOP GRID ──────────────── */

    /**
     * Lấy danh sách sản phẩm đã lọc, sắp xếp và phân trang
     *
     * @param array $filters Mảng chứa các bộ lọc (['brand' => 'Apple', 'min_price' => 1000, 'max_price' => 5000, 'search' => 'pro'])
     * @param string $sort Chuỗi sắp xếp (ví dụ: 'price_asc', 'name_desc')
     * @param int $limit Số lượng sản phẩm mỗi trang
     * @param int $offset Vị trí bắt đầu lấy
     * @return array Mảng các sản phẩm
     */
    public static function getFilteredProducts(array $filters = [], string $sort = 'created_at_desc', int $limit = 9, int $offset = 0): array
    {
        $sql = "SELECT * FROM " . self::$table;
        $whereClauses = [];
        $params = [];
        $types = "";

        // Xây dựng mệnh đề WHERE
        if (!empty($filters['brand']) && $filters['brand'] !== 'All') {
            $whereClauses[] = "brand = ?";
            $params[] = $filters['brand'];
            $types .= "s";
        }
        if (!empty($filters['min_price'])) {
            $whereClauses[] = "price >= ?";
            $params[] = $filters['min_price'];
            $types .= "d";
        }
        if (!empty($filters['max_price'])) {
            $whereClauses[] = "price <= ?";
            $params[] = $filters['max_price'];
            $types .= "d";
        }
        if (!empty($filters['search'])) {
            $searchTerm = "%" . $filters['search'] . "%";
            $whereClauses[] = "(name LIKE ? OR description LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }
        // Lưu ý: Chưa có lọc color, size vì DB không có cột tương ứng

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        // Xây dựng mệnh đề ORDER BY
        switch ($sort) {
            case 'price_asc':
                $sql .= " ORDER BY price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY price DESC";
                break;
            case 'name_asc':
                $sql .= " ORDER BY name ASC";
                break;
            case 'name_desc':
                $sql .= " ORDER BY name DESC";
                break;
            case 'rating_desc':
                $sql .= " ORDER BY rating DESC";
                break;
            case 'created_at_desc':
            default:
                $sql .= " ORDER BY created_at DESC";
                break;
        }

        // Thêm LIMIT và OFFSET
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        // Chuẩn bị và thực thi
        $stmt = Database::prepare($sql, $types, $params);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }
        return []; // Trả về mảng rỗng nếu có lỗi
    }

    /**
     * Đếm tổng số sản phẩm thỏa mãn bộ lọc
     *
     * @param array $filters Mảng chứa các bộ lọc (giống như getFilteredProducts)
     * @return int Tổng số sản phẩm
     */
    public static function countFilteredProducts(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM " . self::$table;
        $whereClauses = [];
        $params = [];
        $types = "";

        // Xây dựng mệnh đề WHERE (Tương tự getFilteredProducts)
        if (!empty($filters['brand']) && $filters['brand'] !== 'All') {
            $whereClauses[] = "brand = ?";
            $params[] = $filters['brand'];
            $types .= "s";
        }
        if (!empty($filters['min_price'])) {
            $whereClauses[] = "price >= ?";
            $params[] = $filters['min_price'];
            $types .= "d";
        }
        if (!empty($filters['max_price'])) {
            $whereClauses[] = "price <= ?";
            $params[] = $filters['max_price'];
            $types .= "d";
        }
        if (!empty($filters['search'])) {
            $searchTerm = "%" . $filters['search'] . "%";
            $whereClauses[] = "(name LIKE ? OR description LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        // Chuẩn bị và thực thi
        $stmt = Database::prepare($sql, $types, $params);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            return $row ? (int)$row['total'] : 0;
        }
        return 0; // Trả về 0 nếu có lỗi
    }

    /** Lấy giá Min và Max của tất cả sản phẩm */
    public static function getMinMaxPrice(): ?array {
        $sql = "SELECT MIN(price) as min_price, MAX(price) as max_price FROM " . self::$table;
        $result = Database::query($sql);
        return $result ? $result->fetch_assoc() : null;
    }
    // Ghi chú: Chưa có hàm getDistinctColors, getDistinctSizes vì DB chưa hỗ trợ




    // ... (Các phương thức khác giữ nguyên) ...

    /**
     * Giảm số lượng tồn kho của sản phẩm.
     * !!! CẢNH BÁO: Nên sử dụng transaction trong Controller khi gọi hàm này
     * !!! cùng với việc tạo order và order items để đảm bảo tính toàn vẹn dữ liệu.
     *
     * @param int $productId ID sản phẩm
     * @param int $quantity Số lượng cần giảm
     * @return bool True nếu thành công, False nếu thất bại (ví dụ: không đủ hàng)
     */
    public static function decreaseStock(int $productId, int $quantity): bool
    {
        // Lấy stock hiện tại để kiểm tra lại trước khi trừ (an toàn hơn)
        $currentStock = self::getStock($productId);
        if ($currentStock === null || $currentStock < $quantity) {
            return false; // Không tìm thấy sản phẩm hoặc không đủ hàng
        }

        $sql = "UPDATE " . self::$table . " SET stock = stock - ? WHERE id = ? AND stock >= ?";
        // Thêm AND stock >= ? để tránh lỗi race condition (dù transaction là giải pháp tốt nhất)
        $stmt = Database::prepare($sql, "iii", [$quantity, $productId, $quantity]);
        if ($stmt && $stmt->execute()) {
            // Kiểm tra xem có đúng 1 dòng được cập nhật không
            return $stmt->affected_rows === 1;
        }
        return false;
    }

    /**
     * Lấy số lượng tồn kho hiện tại
     * @param int $productId
     * @return int|null Số lượng tồn kho hoặc null nếu không tìm thấy SP
     */
    public static function getStock(int $productId) : ?int {
        $product = self::find($productId); // Dùng lại hàm find từ BaseModel/Product
        return $product ? (int)$product['stock'] : null;
    }




    /**
     * Tăng số lượng tồn kho của sản phẩm.
     * !!! CẢNH BÁO: Nên sử dụng transaction trong Controller khi gọi hàm này
     * !!! cùng với việc cập nhật trạng thái đơn hàng (ví dụ: khi hủy đơn).
     *
     * @param int $productId ID sản phẩm
     * @param int $quantity Số lượng cần tăng
     * @return bool True nếu thành công, False nếu thất bại
     */
    public static function increaseStock(int $productId, int $quantity): bool
    {
        // Đảm bảo số lượng tăng là dương
        if ($quantity <= 0) {
            return false;
        }

        $sql = "UPDATE " . self::$table . " SET stock = stock + ? WHERE id = ?";
        $stmt = Database::prepare($sql, "ii", [$quantity, $productId]);
        if ($stmt && $stmt->execute()) {
            // Kiểm tra xem có đúng 1 dòng được cập nhật không
            $success = $stmt->affected_rows === 1;
            $stmt->close();
            return $success;
        }
        if ($stmt) $stmt->close();
        return false;
    }
}
