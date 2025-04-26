<?php
// Web/app/Controllers/WishlistController.php

namespace App\Controllers;

use App\Models\Wishlist;
use App\Models\Product; // Cần thiết để kiểm tra sản phẩm tồn tại (tùy chọn nhưng nên có)

class WishlistController extends BaseController {

    // Hàm __construct kiểm tra đăng nhập
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        // Chuyển hướng hoặc trả về lỗi JSON nếu chưa đăng nhập
        if (!isset($_SESSION['user_id'])) {
            $isAjax = isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1;
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401); // Unauthorized
                ob_clean();
                echo json_encode([
                    'success' => false,
                    'message' => 'Vui lòng đăng nhập để sử dụng danh sách yêu thích.',
                    'login_required' => true // Gửi cờ này cho JS biết cần chuyển hướng
                ]);
                exit;
            } else {
                // Logic redirect cũ cho request thường
                $intendedPage = $_SERVER['REQUEST_URI'] ?? '?page=home';
                // Nếu request là add/remove, quay lại trang trước đó thay vì trang add/remove
                if (strpos($intendedPage, 'wishlist_add') !== false || strpos($intendedPage, 'wishlist_remove') !== false) {
                    $intendedPage = $_SERVER['HTTP_REFERER'] ?? '?page=home';
                }
                $_SESSION['redirect_after_login'] = $intendedPage;
                $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Vui lòng đăng nhập để sử dụng danh sách yêu thích.'];
                $this->redirect('?page=login');
                exit;
            }
        }
    }

    // Hàm index() để hiển thị trang wishlist
    public function index() {
        $userId = $_SESSION['user_id'];
        $wishlistItems = Wishlist::getByUser($userId);
        // Lấy flash message (nếu có) từ BaseController hoặc trực tiếp
        // $flashMessage = $_SESSION['flash_message'] ?? null;
        // if ($flashMessage) unset($_SESSION['flash_message']);
        // BaseController đã xử lý flash message và truyền vào render
        $this->render('wishlist', [
            'wishlistItems' => $wishlistItems,
            // 'flashMessage' => $flashMessage, // Không cần truyền lại nếu BaseController đã xử lý
            'pageTitle' => 'Danh sách yêu thích'
        ]);
    }

    /**
     * Xử lý việc thêm sản phẩm vào danh sách yêu thích (hỗ trợ AJAX)
     */
    public function add() {
        $productIdInput = $_REQUEST['id'] ?? null;
        $productId = filter_var($productIdInput, FILTER_VALIDATE_INT);
        $userId = $_SESSION['user_id'];
        $isAjax = isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1;

        // Cấu trúc phản hồi JSON mặc định
        $response = ['success' => false, 'message' => 'ID sản phẩm không hợp lệ.', 'wishlistItemCount' => 0];

        if ($productId === false || $productId <= 0) {
            // Giữ nguyên message mặc định
        } else {
            // (Nên có) Kiểm tra xem sản phẩm có tồn tại không
            if (!Product::find($productId)) {
                 $response['message'] = 'Sản phẩm không tồn tại.';
            } else {
                $success = Wishlist::add($userId, $productId);
                if ($success) {
                    $response = ['success' => true, 'message' => 'Đã thêm vào danh sách yêu thích!'];
                } else {
                    // Kiểm tra xem có phải đã tồn tại sẵn không
                    if (Wishlist::isWishlisted($userId, $productId)) {
                        $response = ['success' => false, 'message' => 'Sản phẩm này đã ở trong danh sách yêu thích.', 'already_added' => true];
                    } else {
                        $response['message'] = 'Lỗi khi thêm sản phẩm yêu thích.'; // Lỗi DB hoặc lỗi khác
                    }
                }
            }
        }

        // Luôn lấy số lượng wishlist hiện tại để trả về cho AJAX
        $wishlistIds = Wishlist::getWishlistedProductIds($userId);
        $response['wishlistItemCount'] = count($wishlistIds);

        // --- Trả về kết quả ---
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            // Đặt mã HTTP phù hợp (ví dụ: 400 Bad Request nếu ID không hợp lệ)
            if (!$response['success'] && ($productId === false || $productId <= 0)) {
                http_response_code(400);
            } else if (!$response['success'] && !isset($response['already_added'])) {
                 http_response_code(500); // Lỗi server nếu không thêm được mà không phải do đã tồn tại
            }
            ob_clean();
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            // Xử lý cho request non-AJAX (ít xảy ra nếu JS chạy đúng)
            $_SESSION['flash_message'] = ['type' => $response['success'] ? 'success' : 'error', 'message' => $response['message']];
            $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '?page=shop_grid';
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
        $productIdInput = $_REQUEST['id'] ?? null;
        $productId = filter_var($productIdInput, FILTER_VALIDATE_INT);
        $userId = $_SESSION['user_id'];
        $isAjax = isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1;

        $response = ['success' => false, 'message' => 'ID sản phẩm không hợp lệ.', 'wishlistItemCount' => 0];

        if ($productId === false || $productId <= 0) {
           // Giữ nguyên message mặc định
        } else {
            $success = Wishlist::remove($userId, $productId);
            if ($success) {
                $response = ['success' => true, 'message' => 'Đã xóa khỏi danh sách yêu thích.'];
            } else {
                $response['message'] = 'Lỗi khi xóa hoặc sản phẩm không có trong danh sách.';
            }
        }

        // Luôn lấy số lượng wishlist hiện tại
        $wishlistIds = Wishlist::getWishlistedProductIds($userId);
        $response['wishlistItemCount'] = count($wishlistIds);

        // --- Trả về kết quả ---
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
             if (!$response['success'] && ($productId === false || $productId <= 0)) {
                http_response_code(400);
            } else if (!$response['success']) {
                 http_response_code(500); // Lỗi server nếu xóa không thành công
            }
            ob_clean();
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        } else {
             // Xử lý cho request non-AJAX
            $_SESSION['flash_message'] = ['type' => $response['success'] ? 'success' : 'error', 'message' => $response['message']];
             $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '?page=wishlist'; // Thường là quay lại trang wishlist
            if (!isset($_REQUEST['redirect']) || $_REQUEST['redirect'] !== 'no') {
                $this->redirect($redirectUrl);
            }
            exit;
        }
    }
}