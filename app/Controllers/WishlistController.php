<?php
// Web/app/Controllers/WishlistController.php

require_once BASE_PATH . '/app/Controllers/BaseController.php';
require_once BASE_PATH . '/app/Models/Wishlist.php';

class WishlistController extends BaseController {

    // Hàm __construct
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            // --- START: AJAX Login Check ---
            // *** SỬA: Dùng $_REQUEST cho $isAjax ***
            if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1) {
                header('Content-Type: application/json; charset=utf-8');
                ob_clean();
                echo json_encode([
                    'success' => false,
                    'message' => 'Vui lòng đăng nhập để sử dụng danh sách yêu thích.',
                    'login_required' => true
                ]);
                exit;
            }
            // --- END: AJAX Login Check ---

            // Logic redirect cũ cho request thường
            $intendedPage = $_SERVER['REQUEST_URI'] ?? '?page=home';
            if (strpos($intendedPage, 'wishlist_add') !== false || strpos($intendedPage, 'wishlist_remove') !== false) {
                $intendedPage = $_SERVER['HTTP_REFERER'] ?? '?page=home';
            }
            $_SESSION['redirect_after_login'] = $intendedPage;
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Vui lòng đăng nhập để sử dụng danh sách yêu thích.'];
            $this->redirect('?page=login');
            exit;
        }
    }

    // Hàm index() giữ nguyên
    public function index() {
        $userId = $_SESSION['user_id'];
        $wishlistItems = Wishlist::getByUser($userId);
        $flashMessage = $_SESSION['flash_message'] ?? null;
        if ($flashMessage) unset($_SESSION['flash_message']);
        $this->render('wishlist', [
            'wishlistItems' => $wishlistItems,
            'flashMessage' => $flashMessage,
            'pageTitle' => 'Danh sách yêu thích'
        ]);
    }

    /**
     * Xử lý việc thêm sản phẩm vào danh sách yêu thích (hỗ trợ AJAX)
     */
    public function add() {
        // *** SỬA: Dùng $_REQUEST và filter_var ***
        $productIdInput = $_REQUEST['id'] ?? null;
        $productId = filter_var($productIdInput, FILTER_VALIDATE_INT);
        $userId = $_SESSION['user_id'];
        // *** SỬA: Dùng $_REQUEST cho $isAjax ***
        $isAjax = isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1;
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '?page=shop_grid';

        $response = ['success' => false, 'message' => 'ID sản phẩm không hợp lệ.'];

        // Kiểm tra $productId sau khi lọc
        if ($productId !== false && $productId > 0) { // filter_var trả về false nếu không hợp lệ
            $success = Wishlist::add($userId, $productId);
            if ($success) {
                $response = ['success' => true, 'message' => 'Đã thêm vào danh sách yêu thích!'];
            } else {
                if (Wishlist::isWishlisted($userId, $productId)) {
                    $response = ['success' => false, 'message' => 'Sản phẩm này đã ở trong danh sách yêu thích.', 'already_added' => true];
                } else {
                    $response = ['success' => false, 'message' => 'Lỗi khi thêm sản phẩm yêu thích.'];
                }
            }
        } else {
            $response['message'] = 'ID sản phẩm không hợp lệ hoặc bị thiếu.'; // Cập nhật thông báo lỗi nếu cần
        }

        // Lấy số lượng wishlist hiện tại để trả về cho AJAX
        if ($isAjax) {
            $wishlistIds = Wishlist::getWishlistedProductIds($userId);
            $response['wishlistItemCount'] = count($wishlistIds);
        }

        // --- Trả về kết quả ---
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            ob_clean();
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            $_SESSION['flash_message'] = ['type' => $response['success'] ? 'success' : 'error', 'message' => $response['message']];
            // *** SỬA: Dùng $_REQUEST cho redirect check ***
            if (!isset($_REQUEST['redirect']) || $_REQUEST['redirect'] !== 'no') {
                $this->redirect($redirectUrl);
            }
            exit;
        }
    }

    /**
     * Xử lý việc xóa sản phẩm khỏi danh sách yêu thích (hỗ trợ AJAX)
     */
    public function remove() {
        // *** SỬA: Dùng $_REQUEST và filter_var ***
        $productIdInput = $_REQUEST['id'] ?? null; // <-- Dòng 105 (hoặc gần đó)
        $productId = filter_var($productIdInput, FILTER_VALIDATE_INT);
        $userId = $_SESSION['user_id'];
        // *** SỬA: Dùng $_REQUEST cho $isAjax ***
        $isAjax = isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1;
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '?page=wishlist';

        $response = ['success' => false, 'message' => 'ID sản phẩm không hợp lệ.'];

        // Kiểm tra $productId sau khi lọc
        if ($productId !== false && $productId > 0) {
            $success = Wishlist::remove($userId, $productId);
            if ($success) {
                $response = ['success' => true, 'message' => 'Đã xóa khỏi danh sách yêu thích.'];
            } else {
                $response = ['success' => false, 'message' => 'Lỗi khi xóa hoặc sản phẩm không có trong danh sách.'];
            }
        } else {
            $response['message'] = 'ID sản phẩm không hợp lệ hoặc bị thiếu.'; // Cập nhật thông báo lỗi nếu cần
        }

        // Lấy số lượng wishlist hiện tại để trả về cho AJAX
        if ($isAjax) {
            $wishlistIds = Wishlist::getWishlistedProductIds($userId);
            $response['wishlistItemCount'] = count($wishlistIds);
        }

        // --- Trả về kết quả ---
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            ob_clean();
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            $_SESSION['flash_message'] = ['type' => $response['success'] ? 'success' : 'error', 'message' => $response['message']];
            // *** SỬA: Dùng $_REQUEST cho redirect check ***
            if (!isset($_REQUEST['redirect']) || $_REQUEST['redirect'] !== 'no') {
                $this->redirect($redirectUrl);
            }
            exit;
        }
    }
}