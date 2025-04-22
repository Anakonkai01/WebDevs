<?php
// Web/app/Controllers/ReviewController.php

require_once BASE_PATH . '/app/Controllers/BaseController.php';
require_once BASE_PATH . '/app/Models/Review.php'; // Model để tạo review
require_once BASE_PATH . '/app/Models/Product.php'; // Model để kiểm tra SP tồn tại (tùy chọn)

class ReviewController extends BaseController {

    public function __construct() {
        // Đảm bảo session đã được khởi động
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Xử lý việc thêm đánh giá mới từ form POST
     */
    public function addReview() {
        // 1. Chỉ chấp nhận phương thức POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo "<h2>Lỗi 405: Phương thức không được phép.</h2>";
            return;
        }

        // 2. Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            // Lấy product_id từ form (nếu có) để chuyển hướng về sau khi đăng nhập
            $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            $redirectParam = $productId ? '&redirect=' . urlencode('?page=product_detail&id=' . $productId) : '';

            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Bạn cần đăng nhập để gửi đánh giá.'];
            $this->redirect('?page=login' . $redirectParam); // Chuyển đến trang đăng nhập
            return;
        }

        // 3. Lấy dữ liệu từ POST và Validate
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        $content = trim($_POST['content'] ?? '');
        // $userId = $_SESSION['user_id']; // Lấy userId nếu bảng reviews cần

        $errors = [];

        // Kiểm tra Product ID hợp lệ
        if ($productId === false) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID sản phẩm không hợp lệ.'];
            // Không có ID sản phẩm hợp lệ, chuyển về trang shop
            $this->redirect('?page=shop_grid');
            return;
        }

        // Tạo URL để chuyển hướng về trang sản phẩm (dùng cả khi lỗi và thành công)
        $redirectUrl = '?page=product_detail&id=' . $productId;

        // Kiểm tra nội dung đánh giá
        if (empty($content)) {
            $errors[] = "Vui lòng nhập nội dung đánh giá.";
        } elseif (mb_strlen($content) < 10) { // Ví dụ: Kiểm tra độ dài tối thiểu
            $errors[] = "Nội dung đánh giá cần ít nhất 10 ký tự.";
        }

        // (Tùy chọn) Kiểm tra xem sản phẩm có thực sự tồn tại không
        /*
        $productExists = Product::find($productId);
        if (!$productExists) {
             $errors[] = "Sản phẩm bạn đang đánh giá không tồn tại.";
        }
        */

        if (!empty($errors)) {
            // Nếu có lỗi, lưu lỗi vào session và quay lại trang sản phẩm
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => implode('<br>', $errors)];
            $this->redirect($redirectUrl);
            return;
        }

        // 4. Gọi Model để lưu đánh giá
        // Lưu ý: Hàm Review::create hiện tại chỉ nhận productId và content
        // Nếu bạn muốn lưu cả user_id, bạn cần sửa lại hàm create trong Review Model
        $success = Review::create($productId, $content);

        // 5. Đặt thông báo và chuyển hướng
        if ($success) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cảm ơn bạn đã gửi đánh giá!'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Đã xảy ra lỗi khi lưu đánh giá. Vui lòng thử lại.'];
        }

        $this->redirect($redirectUrl); // Chuyển hướng về trang chi tiết sản phẩm
    }
}