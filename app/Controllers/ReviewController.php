<?php
// Web/app/Controllers/ReviewController.php

namespace App\Controllers;

use App\Models\Review;
use App\Models\Product;

class ReviewController extends BaseController {

    public function __construct() {
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
            $this->render('errors/405', ['message' => 'Phương thức không được phép.']);
            return;
        }

        // 2. Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            // Đảm bảo redirect về đúng trang sản phẩm và anchor
            $redirectTarget = '?page=product_detail&id=' . ($productId ?: '') . '#add-review-form';
            $redirectParam = '&redirect=' . urlencode($redirectTarget);

            $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Bạn cần đăng nhập để gửi đánh giá.'];
            $this->redirect('?page=login' . $redirectParam);
            return;
        }
        $userId = $_SESSION['user_id'];

        // 3. Lấy dữ liệu từ POST và Validate
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        $content = trim($_POST['content'] ?? '');
        $ratingInput = $_POST['rating'] ?? null; // Lấy giá trị rating thô

        $errors = [];

        if ($productId === false) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID sản phẩm không hợp lệ khi gửi đánh giá.'];
            $this->redirect('?page=shop_grid'); // Redirect về shop nếu ID sản phẩm sai
            return;
        }
        // URL để chuyển hướng về trang sản phẩm (dùng cả khi lỗi và thành công)
        $redirectUrl = '?page=product_detail&id=' . $productId . '#reviews-content'; // Cuộn tới phần reviews sau khi xử lý

        // --- Validate Content ---
        if (empty($content)) {
            $errors['content'] = "Vui lòng nhập nội dung đánh giá.";
        } elseif (mb_strlen($content) < 10) { // Sử dụng mb_strlen
            $errors['content'] = "Nội dung đánh giá cần ít nhất 10 ký tự.";
        }

        // --- Validate Rating ---
        $rating = null; // Giá trị mặc định là null (DB cho phép NULL)
        if ($ratingInput !== null && $ratingInput !== '') {
            $validatedRating = filter_var($ratingInput, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 5]]);
            if ($validatedRating === false) {
                $errors['rating'] = "Điểm đánh giá không hợp lệ (phải từ 1 đến 5 sao).";
            } else {
                $rating = $validatedRating; // Gán giá trị hợp lệ
            }
        }



        // --- Xử lý nếu có lỗi validation ---
        if (!empty($errors)) {
            // Lưu lỗi và dữ liệu cũ vào session để hiển thị lại trên form
             $_SESSION['form_errors'] = $errors;
             $_SESSION['form_data'] = $_POST; // Lưu toàn bộ dữ liệu đã nhập (bao gồm cả rating đã chọn)
             $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Vui lòng kiểm tra lại thông tin đánh giá.']; // Thông báo chung
            $this->redirect($redirectUrl); // Chuyển hướng về trang sản phẩm kèm lỗi
            return;
        }

        // 4. Gọi Model để lưu đánh giá
        $success = Review::create($productId, $userId, $content, $rating); // Truyền $rating (là int hoặc null)

        // 5. Đặt thông báo và chuyển hướng
        if ($success) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cảm ơn bạn đã gửi đánh giá!'];

            // --- QUAN TRỌNG: Cập nhật rating trung bình của sản phẩm ---
            Review::updateProductAverageRating($productId);
            // --- KẾT THÚC CẬP NHẬT ---

            // Xóa dữ liệu form cũ nếu thành công
            unset($_SESSION['form_errors'], $_SESSION['form_data']);

        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Đã xảy ra lỗi khi lưu đánh giá vào cơ sở dữ liệu. Vui lòng thử lại.'];
             // Lưu lại dữ liệu form nếu insert lỗi
             $_SESSION['form_data'] = $_POST;
        }

        $this->redirect($redirectUrl); // Chuyển hướng về trang chi tiết sản phẩm
    }
}