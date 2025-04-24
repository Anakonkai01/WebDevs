<?php
// Web/public/index.php
ini_set('display_errors', 1); error_reporting(E_ALL); session_start();
define('BASE_PATH', dirname(__DIR__));


// Include controllers
require_once BASE_PATH . '/app/controllers/BaseController.php';
require_once BASE_PATH . '/app/controllers/HomeController.php';
require_once BASE_PATH . '/app/controllers/ProductController.php';
require_once BASE_PATH . '/app/controllers/CartController.php';
require_once BASE_PATH . '/app/controllers/UserController.php';
require_once BASE_PATH . '/app/controllers/OrderController.php'; // *** Đảm bảo đã require ***
require_once BASE_PATH . '/app/controllers/ReviewController.php'; // *** THÊM DÒNG NÀY ***
require_once BASE_PATH . '/app/controllers/WishlistController.php'; // *** THÊM DÒNG NÀY ***


// Lấy tham số page, mặc định là home
$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'home':
        (new HomeController())->index();
        break;

    case 'shop_grid':
        (new ProductController())->shopGrid();
        break;

    case 'product_detail':
        $productId = $_GET['id'] ?? null;
        if ($productId) {
            (new ProductController())->detail((int)$productId);
        } else {
            http_response_code(400);
            echo "<h2>400 - Thiếu ID sản phẩm</h2>";
        }
        break;

    case 'cart_add':
        $productId = $_GET['id'] ?? null;
        $quantity = $_GET['quantity'] ?? 1;
        if ($productId) {
            (new CartController())->add((int)$productId, (int)$quantity);
        } else {
            http_response_code(400);
            echo "<h2>400 - Thiếu ID sản phẩm để thêm vào giỏ</h2>";
        }
        break;

    case 'cart':
        (new CartController())->index();
        break;

    // *** THÊM CASE cart_update VÀO ĐÂY ***
    case 'cart_update':
        // Phương thức update thường xử lý dữ liệu POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new CartController())->update();
        } else {
            // Nếu truy cập bằng GET, có thể chuyển hướng về giỏ hàng hoặc báo lỗi
            header('Location: ?page=cart'); // Chuyển hướng về trang giỏ hàng
            exit;
        }
        break;

    // *** THÊM CASE cart_remove VÀO ĐÂY (cho nút xóa) ***
    case 'cart_remove':
        // Phương thức remove thường xử lý ID từ GET
        $productId = $_GET['id'] ?? null;
        if ($productId) {
            (new CartController())->remove((int)$productId);
        } else {
            // Thiếu ID, có thể báo lỗi hoặc chuyển hướng
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Thiếu ID sản phẩm để xóa.'];
            header('Location: ?page=cart');
            exit;
        }
        break;
    // *** THÊM CÁC CASE CHO USER AUTHENTICATION ***
    case 'login': // Hiển thị form đăng nhập
        (new UserController())->showLoginForm();
        break;

    case 'handle_login': // Xử lý submit form đăng nhập
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new UserController())->handleLogin();
        } else {
            header('Location: ?page=login'); // Chuyển hướng nếu truy cập GET
            exit;
        }
        break;

    case 'register': // Hiển thị form đăng ký
        (new UserController())->showRegisterForm();
        break;

    case 'handle_register': // Xử lý submit form đăng ký
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new UserController())->handleRegister();
        } else {
            header('Location: ?page=register'); // Chuyển hướng nếu truy cập GET
            exit;
        }
        break;

    case 'logout': // Xử lý đăng xuất
        (new UserController())->logout();
        break;


    // *** THÊM ROUTES QUẢN LÝ TÀI KHOẢN ***
    case 'profile': // Trang xem hồ sơ (và các link quản lý khác)
        (new UserController())->showProfile();
        break;

    case 'change_password': // Trang hiển thị form đổi mật khẩu
        (new UserController())->showChangePasswordForm();
        break;

    case 'handle_change_password': // Route xử lý việc đổi mật khẩu
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new UserController())->handleChangePassword();
        } else {
            // Nếu không phải POST thì chuyển hướng về form
            header('Location: ?page=change_password');
            exit;
        }
        break;

    // *** THÊM CÁC CASE CHO CHECKOUT ***
    case 'checkout': // Hiển thị form checkout
        (new OrderController())->showCheckoutForm();
        break;

    case 'handle_checkout': // Xử lý submit form checkout
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new OrderController())->placeOrder();
        } else {
            // Không cho phép truy cập GET vào trang xử lý
            header('Location: ?page=checkout');
            exit;
        }
        break;

    case 'order_success': // Hiển thị trang đặt hàng thành công
        (new OrderController())->showSuccessPage();
        break;


    // *** THÊM ROUTE LỊCH SỬ ĐƠN HÀNG ***
    case 'order_history':
        (new OrderController())->orderHistory();
        break;

    // *** THÊM ROUTE CHI TIẾT ĐƠN HÀNG ***
    case 'order_detail':
        $orderId = $_GET['id'] ?? null;
        if ($orderId && filter_var($orderId, FILTER_VALIDATE_INT)) { // Kiểm tra ID hợp lệ
            (new OrderController())->orderDetail((int)$orderId);
        } else {
            http_response_code(400);
            echo "<h2>400 - ID đơn hàng không hợp lệ hoặc bị thiếu</h2>";
        }
        break;

    // *** THÊM ROUTE XỬ LÝ REVIEW ***
    case 'review_add':
        // Chỉ xử lý phương thức POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new ReviewController())->addReview();
        } else {
            // Nếu truy cập bằng GET, chuyển hướng về trang chủ hoặc trang trước đó
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '?page=home'));
            exit;
        }
        break;


    // *** THÊM WISHLIST ROUTES ***
    case 'wishlist': // Xem danh sách yêu thích
        (new WishlistController())->index();
        break;
    case 'wishlist_add': // Thêm vào danh sách yêu thích
        (new WishlistController())->add();
        break;
    case 'wishlist_remove': // Xóa khỏi danh sách yêu thích
        (new WishlistController())->remove();
        break;
    // *** KẾT THÚC WISHLIST ROUTES ***



    // *** THÊM ROUTE LIÊN HỆ ***
    case 'contact':
        (new HomeController())->contact(); // Gọi phương thức contact trong HomeController
        break;





    // *** THÊM ROUTE HỦY ĐƠN HÀNG ***
    case 'cancel_order':
        // Phương thức này thường dùng GET với ID
        (new OrderController())->cancelOrder();
        break;




    // *** THÊM ROUTE ĐẶT LẠI ĐƠN HÀNG ***
    case 'reorder':
        // Phương thức này thường dùng GET với ID
        (new CartController())->reorder();
        break;



    default:
        http_response_code(404);
        echo "<h2>404 - Trang không tìm thấy</h2>";
        break;
}