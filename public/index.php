<?php
// Web/public/index.php
ini_set('display_errors', 1); // Bật hiển thị lỗi để dễ debug
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__)); // Định nghĩa đường dẫn gốc của dự án
require_once BASE_PATH . '/vendor/autoload.php'; // Nạp file autoload của Composer

// Lấy trang yêu cầu từ URL, mặc định là 'home'
$page = $_GET['page'] ?? 'home';

// Import các Controller cần thiết
use App\Controllers\BaseController;
use App\Controllers\HomeController;
use App\Controllers\ProductController;
use App\Controllers\CartController;
use App\Controllers\UserController;
use App\Controllers\OrderController;
use App\Controllers\ReviewController;
use App\Controllers\WishlistController;

// Đảm bảo session được khởi tạo sớm (nếu chưa có)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Định tuyến (Routing) dựa trên tham số 'page'
switch ($page) {
    // --- Các route cơ bản ---
    case 'home':
        (new HomeController())->index();
        break;
    case 'shop_grid':
        (new ProductController())->shopGrid();
        break;
    case 'product_detail':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id && $id > 0) {
            (new ProductController())->detail($id);
        } else {
            // Hiển thị trang 404 nếu ID không hợp lệ
            (new BaseController())->showNotFoundPage('ID sản phẩm không hợp lệ.');
        }
        break;
    case 'contact':
        (new HomeController())->contact();
        break;

    // --- Giỏ hàng ---
    case 'cart_add':
        (new CartController())->add();
        break;
    case 'cart':
        (new CartController())->index();
        break;
    case 'cart_update':
        // Chỉ xử lý nếu request là POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new CartController())->update();
        } else {
            (new BaseController())->redirect('?page=cart'); // Chuyển hướng về giỏ hàng nếu không phải POST
        }
        break;
    case 'cart_remove':
        (new CartController())->remove();
        break;
    case 'reorder': // Đặt lại đơn hàng cũ
        (new CartController())->reorder();
        break;

    // --- Người dùng (Auth) ---
    case 'login':
        (new UserController())->showLoginForm();
        break;
    case 'handle_login':
        // Chỉ xử lý nếu request là POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new UserController())->handleLogin();
        } else {
            (new BaseController())->redirect('?page=login');
        }
        break;
    case 'register':
        (new UserController())->showRegisterForm();
        break;
    case 'handle_register':
        // Chỉ xử lý nếu request là POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new UserController())->handleRegister();
        } else {
            (new BaseController())->redirect('?page=register');
        }
        break;
    case 'logout':
        (new UserController())->logout();
        break;

    // --- Xác thực Email (Routes Mới) ---
    case 'verify_email': // Hiển thị form nhập mã xác thực email
        (new UserController())->showVerifyEmailForm();
        break;
    case 'handle_verify_email': // Xử lý mã xác thực email
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new UserController())->handleVerifyEmail();
        } else {
            (new BaseController())->redirect('?page=verify_email');
        }
        break;

    // --- Quên Mật Khẩu (Quy trình mới dùng Mã Code) ---
    case 'forgot_password': // Hiển thị form quên mật khẩu (nhập email)
        (new UserController())->showForgotPasswordForm();
        break;
    case 'handle_forgot_password': // Xử lý yêu cầu quên mật khẩu -> Gửi mã code
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new UserController())->handleForgotPasswordRequest();
        } else {
            (new BaseController())->redirect('?page=forgot_password');
        }
        break;
    case 'enter_reset_code': // Hiển thị form nhập mã đặt lại mật khẩu
        (new UserController())->showEnterResetCodeForm();
        break;
    case 'handle_enter_reset_code': // Xử lý mã đặt lại mật khẩu
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new UserController())->handleEnterResetCode();
        } else {
            (new BaseController())->redirect('?page=enter_reset_code');
        }
        break;
    case 'reset_password_from_code': // Hiển thị form đặt mật khẩu mới (SAU KHI nhập code)
        (new UserController())->showResetPasswordFormFromCode();
        break;
    case 'handle_reset_password_from_code': // Xử lý đặt mật khẩu mới (SAU KHI nhập code)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new UserController())->handleResetPasswordFromCode();
        } else {
            (new BaseController())->redirect('?page=login'); // Hoặc forgot_password
        }
        break;

    // --- Hồ sơ người dùng ---
    case 'profile':
        (new UserController())->showProfile();
        break;
    case 'edit_profile':
        (new UserController())->showEditProfileForm();
        break;
    case 'handle_update_profile':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new UserController())->handleUpdateProfile();
        } else {
            (new BaseController())->redirect('?page=profile');
        }
        break;
    case 'change_password':
        (new UserController())->showChangePasswordForm();
        break;
    case 'handle_change_password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new UserController())->handleChangePassword();
        } else {
            (new BaseController())->redirect('?page=change_password');
        }
        break;

    // --- Đơn hàng ---
    case 'checkout':
        (new OrderController())->showCheckoutForm();
        break;
    case 'handle_checkout':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new OrderController())->placeOrder();
        } else {
            (new BaseController())->redirect('?page=cart');
        }
        break;
    case 'order_success':
        (new OrderController())->showSuccessPage();
        break;
    case 'order_history':
        (new OrderController())->orderHistory();
        break;
    case 'order_detail':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id && $id > 0) {
            (new OrderController())->orderDetail($id);
        } else {
            (new BaseController())->showNotFoundPage('ID đơn hàng không hợp lệ.');
        }
        break;
    case 'cancel_order': // Hủy đơn hàng
        (new OrderController())->cancelOrder();
        break;

    // --- Đánh giá ---
    case 'review_add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new ReviewController())->addReview();
        } else {
            // Chuyển về trang trước đó nếu không phải POST
            (new BaseController())->redirect($_SERVER['HTTP_REFERER'] ?? '?page=home');
        }
        break;

    // --- Yêu thích ---
    case 'wishlist':
        (new WishlistController())->index();
        break;
    case 'wishlist_add':
        (new WishlistController())->add();
        break;
    case 'wishlist_remove':
        (new WishlistController())->remove();
        break;

    // --- Trang không tìm thấy (404) ---
    default:
        (new BaseController())->showNotFoundPage();
        break;
}
?>