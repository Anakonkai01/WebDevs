<?php
// Web/public/index.php
ini_set('display_errors', 1); error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';

$page = $_GET['page'] ?? 'home';

use App\Controllers\BaseController;
use App\Controllers\HomeController;
use App\Controllers\ProductController;
use App\Controllers\CartController;
use App\Controllers\UserController;
use App\Controllers\OrderController;
use App\Controllers\ReviewController;
use App\Controllers\WishlistController;

// Đảm bảo session được khởi tạo sớm
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

switch ($page) {
    // --- Các route cơ bản ---
    case 'home': (new HomeController())->index(); break;
    case 'shop_grid': (new ProductController())->shopGrid(); break;
    case 'product_detail':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id && $id > 0) { (new ProductController())->detail($id); }
        else { (new BaseController())->showNotFoundPage('ID sản phẩm không hợp lệ.'); }
        break;
    case 'contact': (new HomeController())->contact(); break;

    // --- Giỏ hàng ---
    case 'cart_add': (new CartController())->add(); break;
    case 'cart': (new CartController())->index(); break;
    case 'cart_update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { (new CartController())->update(); }
        else { (new BaseController())->redirect('?page=cart'); }
        break;
    case 'cart_remove': (new CartController())->remove(); break;
    case 'reorder': (new CartController())->reorder(); break;

    // --- Người dùng (Auth) ---
    case 'login': (new UserController())->showLoginForm(); break;
    case 'handle_login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { (new UserController())->handleLogin(); }
        else { (new BaseController())->redirect('?page=login'); }
        break;
    case 'register': (new UserController())->showRegisterForm(); break;
    case 'handle_register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { (new UserController())->handleRegister(); }
        else { (new BaseController())->redirect('?page=register'); }
        break;
    case 'logout': (new UserController())->logout(); break;
    // --- Các route forgot_password, reset_password ĐÃ BỊ XÓA ---

    // --- Hồ sơ người dùng ---
    case 'profile': (new UserController())->showProfile(); break;
    case 'edit_profile': (new UserController())->showEditProfileForm(); break;
    case 'handle_update_profile':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { (new UserController())->handleUpdateProfile(); }
        else { (new BaseController())->redirect('?page=profile'); }
        break;
    case 'change_password': (new UserController())->showChangePasswordForm(); break;
    case 'handle_change_password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { (new UserController())->handleChangePassword(); }
        else { (new BaseController())->redirect('?page=change_password'); }
        break;

    // --- Đơn hàng ---
    case 'checkout': (new OrderController())->showCheckoutForm(); break;
    case 'handle_checkout':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { (new OrderController())->placeOrder(); }
        else { (new BaseController())->redirect('?page=cart'); }
        break;
    case 'order_success': (new OrderController())->showSuccessPage(); break;
    case 'order_history': (new OrderController())->orderHistory(); break;
    case 'order_detail':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id && $id > 0) { (new OrderController())->orderDetail($id); }
        else { (new BaseController())->showNotFoundPage('ID đơn hàng không hợp lệ.'); }
        break;
    case 'cancel_order': (new OrderController())->cancelOrder(); break;

    // --- Đánh giá ---
    case 'review_add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { (new ReviewController())->addReview(); }
        else { (new BaseController())->redirect($_SERVER['HTTP_REFERER'] ?? '?page=home'); }
        break;

    // --- Yêu thích ---
    case 'wishlist': (new WishlistController())->index(); break;
    case 'wishlist_add': (new WishlistController())->add(); break;
    case 'wishlist_remove': (new WishlistController())->remove(); break;

    // --- Trang không tìm thấy ---
    default:
        (new BaseController())->showNotFoundPage();
        break;
}