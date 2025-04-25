<?php
namespace App\Models;

use App\Core\Database;
// BaseModel cùng namespace

class Review extends BaseModel
{
    /** Tên bảng trong Database **/
    protected static string $table = 'reviews';

    /* ──────────────────────── CREATE ──────────────────────── */
    /**
     * Thêm đánh giá cho sản phẩm *** CẬP NHẬT: Thêm $userId ***
     *
     * @param int    $productId  ID sản phẩm
     * @param int    $userId     ID người dùng đánh giá
     * @param string $content    Nội dung đánh giá
     * @param int|null $rating     Điểm đánh giá (1-5, tùy chọn)
     * @return bool              True nếu thành công
     */
    public static function create(int $productId, int $userId, string $content, ?int $rating = null): bool // Thêm $userId và $rating
    {
        // Cập nhật SQL và tham số
        $sql = "INSERT INTO reviews (product_id, user_id, content, rating, created_at) VALUES (?, ?, ?, ?, NOW())";
        // Kiểu dữ liệu: integer, integer, string, integer (iis nếu không có rating, iisi nếu có)
        $types = "iisi";
        $params = [$productId, $userId, $content, $rating];

        $stmt = Database::prepare($sql, $types, $params);
        if (!$stmt) return false; // Lỗi chuẩn bị statement

        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    /* ──────────────────────── READ ──────────────────────── */
    /**
     * Lấy danh sách đánh giá theo sản phẩm KÈM TÊN USER *** CẬP NHẬT: Dùng LEFT JOIN ***
     *
     * @param int $productId   ID sản phẩm
     * @return array           Mảng các review (bao gồm username)
     */
    public static function getByProduct(int $productId): array
    {
        // Luôn dùng LEFT JOIN để lấy username nếu có user_id
        $sql = "SELECT r.*, u.username
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id -- Dùng LEFT JOIN phòng trường hợp user bị xóa (user_id=NULL)
                WHERE r.product_id = ?
                ORDER BY r.created_at DESC";
        $stmt = Database::prepare($sql, "i", [$productId]);

        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
            // Gán username mặc định nếu là NULL (ví dụ: do user bị xóa)
            foreach ($data as &$row) {
                if (!isset($row['username'])) {
                    $row['username'] = 'Khách'; // Hoặc 'Người dùng ẩn danh'
                }
            }
            unset($row); // Hủy tham chiếu biến cuối cùng
            return $data;
        }
        if ($stmt) $stmt->close();
        return []; // Trả về mảng rỗng nếu lỗi
    }

    /**
     * Xóa tất cả đánh giá của sản phẩm (Giữ nguyên)
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
     * Đếm số lượng review của sản phẩm (Giữ nguyên)
     *
     * @param int $productId
     * @return int
     */
    public static function countByProduct(int $productId): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM reviews WHERE product_id = ?";
        $stmt = Database::prepare($sql, "i", [$productId]);
        if($stmt && $stmt->execute()){
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $stmt->close();
            return $row ? (int)$row['cnt'] : 0;
        }
        if ($stmt) $stmt->close();
        return 0;
    }

    /**
     * (TÙY CHỌN) Tính toán và cập nhật rating trung bình cho sản phẩm
     * Hàm này nên được gọi sau khi thêm/xóa/sửa review
     * @param int $productId
     * @return bool
     */
     public static function updateProductAverageRating(int $productId): bool
     {
         // Tính rating trung bình từ bảng reviews (chỉ tính các review có rating)
         $sqlAvg = "SELECT AVG(rating) as avg_rating FROM reviews WHERE product_id = ? AND rating IS NOT NULL";
         $stmtAvg = Database::prepare($sqlAvg, "i", [$productId]);
         $avgRating = 0;
         if ($stmtAvg && $stmtAvg->execute()) {
             $result = $stmtAvg->get_result();
             $row = $result ? $result->fetch_assoc() : null;
             if ($row && $row['avg_rating'] !== null) {
                 $avgRating = round((float)$row['avg_rating'], 1); // Làm tròn 1 chữ số thập phân
             }
             $stmtAvg->close();
         } else {
              if ($stmtAvg) $stmtAvg->close();
             return false; // Lỗi khi tính trung bình
         }

         // Cập nhật vào bảng products
         $sqlUpd = "UPDATE products SET rating = ? WHERE id = ?";
         $stmtUpd = Database::prepare($sqlUpd, "di", [$avgRating, $productId]); // double, integer
          if ($stmtUpd && $stmtUpd->execute()) {
               $stmtUpd->close();
             return true;
         }
          if ($stmtUpd) $stmtUpd->close();
         return false; // Lỗi khi cập nhật product
     }
}