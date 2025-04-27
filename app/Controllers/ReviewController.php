<?php
namespace App\Controllers;
use App\Models\Review;

class ReviewController extends BaseController
{
    public function __construct() // Constructor to start the session
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function addReview() // Function to add a new review
    {
        // Check if the request is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); 
            $this->render('errors/405', ['message' => 'Phương thức không được phép.']);
            return;
        }

        // check if the user is logged in
        if (!isset($_SESSION['user_id'])) {
            $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            $redirectTarget = '?page=product_detail&id=' . ($productId ?: '') . '#add-review-form';
            $redirectParam = '&redirect=' . urlencode($redirectTarget);
            //set message to flash_message
            $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Bạn cần đăng nhập để gửi đánh giá.'];
            $this->redirect('?page=login' . $redirectParam);
            return;
        }
        //get user id
        $userId = $_SESSION['user_id'];

        //get data from post
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        $content = trim($_POST['content'] ?? '');
        $ratingInput = $_POST['rating'] ?? null;
        $errors = [];
        
        //check product id
        if ($productId === false) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID sản phẩm không hợp lệ khi gửi đánh giá.'];
            $this->redirect('?page=shop_grid');
            return;
        }
        // set url for redirect
        $redirectUrl = '?page=product_detail&id=' . $productId . '#reviews-content';
        //check content
        if (empty($content)) {
            $errors['content'] = "Vui lòng nhập nội dung đánh giá.";
        } elseif (mb_strlen($content) < 10) {
            $errors['content'] = "Nội dung đánh giá cần ít nhất 10 ký tự.";
        }
        //check rating
        $rating = null;
        if ($ratingInput !== null && $ratingInput !== '') {
            $validatedRating = filter_var($ratingInput, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 5]]);
            if ($validatedRating === false) {
                $errors['rating'] = "Điểm đánh giá không hợp lệ (phải từ 1 đến 5 sao).";
            } else {$rating = $validatedRating;}
        } 
        // Handle errors
        if (!empty($errors)) { // check if error
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST; 
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Vui lòng kiểm tra lại thông tin đánh giá.']; 
            $this->redirect($redirectUrl); 
            return;
        }
        // create review
        $success = Review::create($productId, $userId, $content, $rating);
        if ($success) { // if create success
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cảm ơn bạn đã gửi đánh giá!'];
            //update rating
            Review::updateProductAverageRating($productId);
            //remove data
            unset($_SESSION['form_errors'], $_SESSION['form_data']);
        } else {
            //if create error
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Đã xảy ra lỗi khi lưu đánh giá vào cơ sở dữ liệu. Vui lòng thử lại.'];
            $_SESSION['form_data'] = $_POST;
        }
        $this->redirect($redirectUrl);
    }
}
