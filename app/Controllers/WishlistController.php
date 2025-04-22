<?php
// Web/app/Controllers/WishlistController.php

require_once BASE_PATH . '/app/Controllers/BaseController.php';
require_once BASE_PATH . '/app/Models/Wishlist.php'; // Model vừa tạo

class WishlistController extends BaseController {

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        // Tất cả các chức năng wishlist đều yêu cầu đăng nhập
        if (!isset($_SESSION['user_id'])) {
            // Lưu lại trang người dùng muốn truy cập (nếu có thể)
            $intendedPage = $_SERVER['REQUEST_URI'] ?? '?page=home';
            if (strpos($intendedPage, 'wishlist_add') !== false || strpos($intendedPage, 'wishlist_remove') !== false) {
                // Nếu đang cố add/remove, lưu trang trước đó thay vì trang add/remove
                $intendedPage = $_SERVER['HTTP_REFERER'] ?? '?page=home';
            }
            $_SESSION['redirect_after_login'] = $intendedPage;

            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Vui lòng đăng nhập để sử dụng danh sách yêu thích.'];
            $this->redirect('?page=login');
            exit; // Ngăn không cho chạy tiếp
        }
    }

    /**
     * Hiển thị trang danh sách yêu thích của người dùng hiện tại
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        // Gọi model để lấy danh sách sản phẩm kèm thông tin
        $wishlistItems = Wishlist::getByUser($userId);

        $flashMessage = $_SESSION['flash_message'] ?? null;
        if ($flashMessage) unset($_SESSION['flash_message']);

        $this->render('wishlist', [
            'wishlistItems' => $wishlistItems,
            'flashMessage' => $flashMessage
        ]);
    }

    /**
     * Xử lý việc thêm sản phẩm vào danh sách yêu thích (thường từ link GET)
     */
    public function add() {
        $productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $userId = $_SESSION['user_id'];
        // Lấy URL trang trước đó để quay lại
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '?page=shop_grid';

        if (!$productId) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID sản phẩm không hợp lệ.'];
        } else {
            // Gọi model để thêm
            $success = Wishlist::add($userId, $productId);
            if ($success) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đã thêm vào danh sách yêu thích!'];
            } else {
                // Kiểm tra xem có phải đã tồn tại không
                if (Wishlist::isWishlisted($userId, $productId)) {
                    $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'Sản phẩm này đã ở trong danh sách yêu thích.'];
                } else {
                    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Lỗi khi thêm sản phẩm yêu thích.'];
                }
            }
        }
        // Chuyển hướng về trang trước đó (trang sản phẩm hoặc trang shop)
        $this->redirect($redirectUrl);
    }

    /**
     * Xử lý việc xóa sản phẩm khỏi danh sách yêu thích (thường từ link GET)
     */
    public function remove() {
        $productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $userId = $_SESSION['user_id'];
        // Lấy URL trang trước đó
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '?page=wishlist'; // Nếu xóa từ trang wishlist thì quay lại wishlist

        if (!$productId) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID sản phẩm không hợp lệ.'];
        } else {
            // Gọi model để xóa
            $success = Wishlist::remove($userId, $productId);
            if ($success) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đã xóa khỏi danh sách yêu thích.'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Lỗi khi xóa hoặc sản phẩm không có trong danh sách.'];
            }
        }
        // Chuyển hướng về trang trước đó
        $this->redirect($redirectUrl);
    }
}