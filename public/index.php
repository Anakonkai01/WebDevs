<?php
// Bật hiện lỗi
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Khởi động session
session_start();

// Định nghĩa Base path
define('BASE_PATH', dirname(__DIR__));

// Include các controller
require_once BASE_PATH . '/app/controllers/HomeController.php';
// Nếu bạn có thêm controller khác, ví dụ Product, Cart, Review, thì cũng require ở đây

// Lấy tham số page, mặc định là home
$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'home':
        // Khi truy cập ?page=home hoặc không truyền page
        (new HomeController())->index();
        break;

    // Bạn có thể thêm các route khác ở đây:
    // case 'product':
    //    (new ProductController())->detail($_GET['id'] ?? null);
    //    break;
    //
    // case 'cart':
    //    (new CartController())->show();
    //    break;

    default:
        // Nếu người dùng truy cập page lạ
        echo "<h2>404 - Trang không tìm thấy</h2>";
        break;
}
