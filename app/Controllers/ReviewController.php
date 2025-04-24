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
            // Có thể render view lỗi thay vì echo
            $this->render('errors/405', ['message' => 'Phương thức không được phép.']);
            return;
        }

        // 2. Kiểm tra đăng nhập *** QUAN TRỌNG ***
        if (!isset($_SESSION['user_id'])) {
            $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            $redirectParam = $productId ? '&redirect=' . urlencode('?page=product_detail&id=' . $productId . '#add-review-form') : ''; // Thêm #add-review-form để cuộn tới form

            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Bạn cần đăng nhập để gửi đánh giá.'];
            $this->redirect('?page=login' . $redirectParam); // Chuyển đến trang đăng nhập
            return;
        }
        // *** Lấy user_id từ session ***
        $userId = $_SESSION['user_id'];

        // 3. Lấy dữ liệu từ POST và Validate
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        $content = trim($_POST['content'] ?? '');
        $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 5]]); // Lấy rating nếu có form input tên là 'rating'

        $errors = [];

        if ($productId === false) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID sản phẩm không hợp lệ.'];
            $this->redirect('?page=shop_grid');
            return;
        }
        // Tạo URL để chuyển hướng về trang sản phẩm (dùng cả khi lỗi và thành công)
        $redirectUrl = '?page=product_detail&id=' . $productId . '#reviews-section'; // Thêm #reviews-section để cuộn tới phần reviews

        if (empty($content)) {
            $errors[] = "Vui lòng nhập nội dung đánh giá.";
        } elseif (mb_strlen($content) < 10) {
            $errors[] = "Nội dung đánh giá cần ít nhất 10 ký tự.";
        }
        // (Tùy chọn) Validate rating nếu bạn thêm trường rating
        // if ($rating === false || $rating === null) {
        //     $errors[] = "Vui lòng chọn điểm đánh giá.";
        // }

        if (!empty($errors)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => implode('<br>', $errors)];
            // (Tùy chọn) Lưu lại nội dung đã nhập vào session để điền lại form
            // $_SESSION['form_data']['review_content'] = $content;
            $this->redirect($redirectUrl);
            return;
        }

        // 4. Gọi Model để lưu đánh giá *** CẬP NHẬT: Truyền $userId và $rating ***
        $success = Review::create($productId, $userId, $content, $rating); // Truyền cả $userId và $rating

        // 5. Đặt thông báo và chuyển hướng
        if ($success) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cảm ơn bạn đã gửi đánh giá!'];

            // (TÙY CHỌN) Cập nhật rating trung bình của sản phẩm sau khi thêm review thành công
            // Bỏ comment dòng dưới nếu bạn đã tạo hàm updateProductAverageRating trong Review Model
            // Review::updateProductAverageRating($productId);

        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Đã xảy ra lỗi khi lưu đánh giá. Vui lòng thử lại.'];
        }

        $this->redirect($redirectUrl); // Chuyển hướng về trang chi tiết sản phẩm
    }
}