<?php
namespace App\Models;

use App\Core\Database;
use Exception; 

class Product extends BaseModel
{
    /** tên bảng trong CSDL */
    protected static string $table = "products";

    /* ───────────────────────  CRUD  ─────────────────────── */
    // ... (Các hàm create, update, delete giữ nguyên) ...
    /** Thêm sản phẩm */
    public static function create(
        string $name, string $desc, float  $price, string $image,
        int $stock, string $brand, float $rating = 0.0,
        ?string $screen_size = null, ?string $screen_tech = null, ?string $cpu = null, ?string $ram = null,
        ?string $storage = null, ?string $rear_camera = null, ?string $front_camera = null,
        ?string $battery_capacity = null, ?string $os = null, ?string $dimensions = null, ?string $weight = null
    ): bool {
        $sql = "
            INSERT INTO products
                (name, description, price, image, stock, brand, rating,
                 screen_size, screen_tech, cpu, ram, storage, rear_camera, front_camera, battery_capacity, os, dimensions, weight)
            VALUES (?,?,?,?,?,?,?, ?,?,?,?,?,?,?, ?,?,?,?)
        ";
        $stmt = Database::prepare($sql, "ssdsisd sssssssssss", [
            $name, $desc, $price, $image, $stock, $brand, $rating,
            $screen_size, $screen_tech, $cpu, $ram, $storage, $rear_camera, $front_camera, $battery_capacity, $os, $dimensions, $weight
        ]);
         if ($stmt) {
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        }
        return false;
    }

    /** Cập nhật sản phẩm */
    public static function update(
        int $id, string $name, string $desc, float $price, string $image,
        int $stock, string $brand, float $rating,
        ?string $screen_size = null, ?string $screen_tech = null, ?string $cpu = null, ?string $ram = null,
        ?string $storage = null, ?string $rear_camera = null, ?string $front_camera = null,
        ?string $battery_capacity = null, ?string $os = null, ?string $dimensions = null, ?string $weight = null
    ): bool {
        $sql = "
            UPDATE products SET
                name = ?, description = ?, price = ?, image = ?,
                stock = ?, brand = ?, rating = ?,
                screen_size = ?, screen_tech = ?, cpu = ?, ram = ?, storage = ?,
                rear_camera = ?, front_camera = ?, battery_capacity = ?, os = ?,
                dimensions = ?, weight = ?
            WHERE id = ?
        ";
        $stmt = Database::prepare($sql, "ssdsisd sssssssssss i", [
            $name, $desc, $price, $image, $stock, $brand, $rating,
            $screen_size, $screen_tech, $cpu, $ram, $storage, $rear_camera, $front_camera,
            $battery_capacity, $os, $dimensions, $weight, $id
        ]);
         if ($stmt) {
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        }
        return false;
    }

    /** Xoá sản phẩm */
    public static function delete(int $id): bool
    {
        $stmt = Database::prepare("DELETE FROM products WHERE id = ?", "i", [$id]);
         if ($stmt) {
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        }
        return false;
    }

    /* ─────────────────  TRUY VẤN PHỤC VỤ TRANG HOME  ───────────────── */
     /** Tìm kiếm theo tên */
    public static function searchByName(string $keyword): array
    {
        $like = "%$keyword%";
        $stmt = Database::prepare("SELECT * FROM products WHERE name LIKE ?", "s", [$like]);
        if ($stmt && $stmt->execute()) { $result = $stmt->get_result(); $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : []; $stmt->close(); return $data; }
        if ($stmt) $stmt->close(); return [];
    }
    /** Lọc theo hãng (brand) */
    public static function getByBrand(string $brand, int $limit = 12): array // Added limit parameter
    {
        if ($brand === "All" || empty($brand)){ return self::getLatest($limit); } // Return latest if 'All' or empty
        $stmt = Database::prepare("SELECT * FROM products WHERE brand = ? LIMIT ?", "si", [$brand, $limit]);
        if ($stmt && $stmt->execute()) { $result = $stmt->get_result(); $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : []; $stmt->close(); return $data; }
        if ($stmt) $stmt->close(); return [];
    }
    /** Lấy sản phẩm mới nhất */
    public static function getLatest(int $limit = 6): array
    {
        $stmt = Database::prepare("SELECT * FROM products ORDER BY created_at DESC LIMIT ?", "i", [$limit]);
        if ($stmt && $stmt->execute()) { $result = $stmt->get_result(); $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : []; $stmt->close(); return $data; }
        if ($stmt) $stmt->close(); return [];
    }
    /** Top‑rated (đánh giá cao nhất) */
    public static function getTopRated(int $limit = 5): array
    {
        $stmt = Database::prepare("SELECT * FROM products ORDER BY rating DESC LIMIT ?", "i", [$limit]);
        if ($stmt && $stmt->execute()) { $result = $stmt->get_result(); $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : []; $stmt->close(); return $data; }
        if ($stmt) $stmt->close(); return [];
    }
    /** Sản phẩm có nhiều review nhất */
    public static function getMostReviewed(int $limit = 5): array
    {
        // Fixed: Use LEFT JOIN to include products with 0 reviews if necessary, order by count DESC then created_at DESC
        $sql = "SELECT p.*, COUNT(r.id) AS review_count
                FROM products p
                LEFT JOIN reviews r ON p.id = r.product_id
                GROUP BY p.id
                ORDER BY review_count DESC, p.created_at DESC
                LIMIT ?";
        $stmt = Database::prepare($sql, "i", [$limit]);
        if ($stmt && $stmt->execute()) { $result = $stmt->get_result(); $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : []; $stmt->close(); return $data; }
        if ($stmt) $stmt->close(); return [];
    }
     public static function getDistinctBrands(): array {
        $stmt = Database::prepare("SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand != '' ORDER BY brand ASC");
        if ($stmt && $stmt->execute()) { $result = $stmt->get_result(); $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : []; $stmt->close(); return array_column($data, 'brand'); }
        if ($stmt) $stmt->close(); return [];
    }

    /* ───────────────── TRUY VẤN PHỤC VỤ TRANG SHOP GRID ──────────────── */

    /**
     * Lấy danh sách sản phẩm đã lọc, sắp xếp và phân trang
     * @param array $filters Mảng lọc (brand, min_price, max_price, search, specs...)
     * @param string $sort Chuỗi sắp xếp
     * @param int $limit Số lượng
     * @param int $offset Vị trí bắt đầu
     * @return array Mảng sản phẩm
     */
    public static function getFilteredProducts(array $filters = [], string $sort = 'created_at_desc', int $limit = 9, int $offset = 0): array
    {
        $sql = "SELECT * FROM " . self::$table;
        list($whereClause, $params, $types) = self::buildWhereClause($filters);

        if (!empty($whereClause)) {
            $sql .= " WHERE " . $whereClause;
        }

        // ORDER BY
        $sql .= self::buildOrderByClause($sort);

        // LIMIT và OFFSET
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit; $params[] = $offset; $types .= "ii";

        // Thực thi
        $stmt = Database::prepare($sql, $types, $params);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
            return $data;
        }
        if ($stmt) $stmt->close();
        error_log("SQL Error getFilteredProducts: " . ($stmt ? $stmt->error : Database::conn()->error));
        return [];
    }

    /**
     * Đếm tổng số sản phẩm thỏa mãn bộ lọc
     * @param array $filters Mảng lọc
     * @return int Tổng số sản phẩm
     */
    public static function countFilteredProducts(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM " . self::$table;
        list($whereClause, $params, $types) = self::buildWhereClause($filters);

        if (!empty($whereClause)) {
            $sql .= " WHERE " . $whereClause;
        }

        // Thực thi
        $stmt = Database::prepare($sql, $types, $params);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $stmt->close();
            return $row ? (int)$row['total'] : 0;
        }
        if ($stmt) $stmt->close();
        error_log("SQL Error countFilteredProducts: " . ($stmt ? $stmt->error : Database::conn()->error));
        return 0;
    }

    /**
     * Helper: Xây dựng mệnh đề WHERE và các tham số/types tương ứng
     * @param array $filters
     * @return array [string $whereClause, array $params, string $types]
     */
    private static function buildWhereClause(array $filters): array
    {
        $whereClauses = [];
        $params = [];
        $types = "";

        // --- IMPORTANT: Define allowed spec filters here to prevent SQL injection ---
        $allowedSpecFilters = ['ram', 'cpu', 'screen_size', 'storage', 'os', 'battery_capacity', 'screen_tech'];

        // Brand Filter
        if (!empty($filters['brand']) && $filters['brand'] !== 'All') {
            $whereClauses[] = "brand = ?"; $params[] = $filters['brand']; $types .= "s";
        }
        // Price Filters
        if (!empty($filters['min_price'])) {
            $whereClauses[] = "price >= ?"; $params[] = (float)$filters['min_price']; $types .= "d";
        }
        if (!empty($filters['max_price'])) {
            $whereClauses[] = "price <= ?"; $params[] = (float)$filters['max_price']; $types .= "d";
        }
        // Search Filter
        if (!empty($filters['search'])) {
            $searchTerm = "%" . trim($filters['search']) . "%";
            // Search in name and description (can add more fields)
            $whereClauses[] = "(name LIKE ? OR description LIKE ?)";
            $params[] = $searchTerm; $params[] = $searchTerm; $types .= "ss";
        }

        // Spec Filters (Iterate through allowed specs)
        foreach ($allowedSpecFilters as $spec) {
            if (!empty($filters[$spec]) && $filters[$spec] !== 'all') {
                // Use backticks for safety
                $whereClauses[] = "`" . $spec . "` = ?";
                $params[] = $filters[$spec]; // Assuming specs are strings
                $types .= "s";
            }
        }

        // Combine clauses
        $whereClause = implode(" AND ", $whereClauses);

        return [$whereClause, $params, $types];
    }

    /**
     * Helper: Xây dựng mệnh đề ORDER BY
     * @param string $sort
     * @return string
     */
    private static function buildOrderByClause(string $sort): string
    {
        switch ($sort) {
            case 'price_asc': return " ORDER BY price ASC";
            case 'price_desc': return " ORDER BY price DESC";
            case 'name_asc': return " ORDER BY name ASC";
            case 'name_desc': return " ORDER BY name DESC";
            case 'rating_desc': return " ORDER BY rating DESC, created_at DESC"; // Added secondary sort
            case 'created_at_desc':
            default: return " ORDER BY created_at DESC";
        }
    }


    /**
     * Lấy các giá trị duy nhất cho một cột thông số kỹ thuật cụ thể
     * @param string $specColumn Tên cột (ví dụ: 'ram', 'cpu', 'screen_size')
     * @return array Mảng các giá trị duy nhất
     */
    public static function getDistinctValuesForSpec(string $specColumn): array
    {
        // Danh sách các cột specs được phép truy vấn
        $allowedColumns = ['ram', 'cpu', 'screen_size', 'storage', 'os', 'battery_capacity', 'screen_tech']; // SAME AS buildWhereClause
        if (!in_array($specColumn, $allowedColumns)) {
            error_log("Attempted to query distinct values for invalid spec column: " . $specColumn);
            return [];
        }

        // Sử dụng backticks ` ` cho tên cột để an toàn
        $sql = "SELECT DISTINCT `" . $specColumn . "` FROM " . self::$table . " WHERE `" . $specColumn . "` IS NOT NULL AND `" . $specColumn . "` != '' ORDER BY `" . $specColumn . "` ASC";

        $stmt = Database::prepare($sql); // Không cần tham số
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
            // Filter out any potential empty strings just in case
            return array_filter(array_column($data, $specColumn), function($value) {
                return $value !== null && $value !== '';
            });
        }
        if ($stmt) $stmt->close();
        error_log("SQL Error getDistinctValuesForSpec ($specColumn): " . Database::conn()->error);
        return [];
    }


    /** Lấy giá Min và Max của tất cả sản phẩm */
    public static function getMinMaxPrice(): ?array {
        $sql = "SELECT MIN(price) as min_price, MAX(price) as max_price FROM " . self::$table;
        $result = Database::query($sql);
        $data = $result ? $result->fetch_assoc() : null;
        // Ensure values are numeric or null
        if ($data) {
            $data['min_price'] = is_numeric($data['min_price']) ? (float)$data['min_price'] : null;
            $data['max_price'] = is_numeric($data['max_price']) ? (float)$data['max_price'] : null;
        }
        return $data;
    }

    /* ───────────────── QUẢN LÝ KHO ──────────────── */
     /**
     * Giảm số lượng tồn kho của sản phẩm.
     * @param int $productId ID sản phẩm
     * @param int $quantity Số lượng cần giảm
     * @return bool True nếu thành công, False nếu thất bại
     */
    public static function decreaseStock(int $productId, int $quantity): bool
    {
        // Ensure quantity is positive
        if ($quantity <= 0) {
            error_log("Attempted to decrease stock by non-positive quantity ($quantity) for product ID $productId.");
            return false;
        }
        $sql = "UPDATE " . self::$table . " SET stock = stock - ? WHERE id = ? AND stock >= ?";
        $stmt = Database::prepare($sql, "iii", [$quantity, $productId, $quantity]);
        if ($stmt && $stmt->execute()) {
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            // Success only if exactly one row was affected
            return $affectedRows === 1;
        }
        if ($stmt) $stmt->close();
        error_log("Failed to decrease stock for product ID $productId. Error: " . ($stmt ? $stmt->error : Database::conn()->error));
        return false;
    }

    /**
     * Lấy số lượng tồn kho hiện tại
     * @param int $productId
     * @return int|null Số lượng tồn kho hoặc null nếu không tìm thấy SP
     */
    public static function getStock(int $productId) : ?int {
        $product = self::find($productId); // Dùng lại hàm find từ BaseModel/Product
        // Check if stock key exists and is numeric
        return ($product && isset($product['stock']) && is_numeric($product['stock'])) ? (int)$product['stock'] : null;
    }

    /**
     * Tăng số lượng tồn kho của sản phẩm.
     * @param int $productId ID sản phẩm
     * @param int $quantity Số lượng cần tăng
     * @return bool True nếu thành công, False nếu thất bại
     */
    public static function increaseStock(int $productId, int $quantity): bool
    {
        if ($quantity <= 0) {
            error_log("Attempted to increase stock by non-positive quantity ($quantity) for product ID $productId.");
            return false;
         }
        $sql = "UPDATE " . self::$table . " SET stock = stock + ? WHERE id = ?";
        $stmt = Database::prepare($sql, "ii", [$quantity, $productId]);
        if ($stmt && $stmt->execute()) {
            $success = $stmt->affected_rows === 1;
            $stmt->close();
             if (!$success) error_log("Stock increase affected 0 rows for product ID $productId (Maybe product doesn't exist?).");
            return $success;
        }
        if ($stmt) $stmt->close();
        error_log("Failed to increase stock for product ID $productId. Error: " . ($stmt ? $stmt->error : Database::conn()->error));
        return false;
    }
} // End Class Product