<?php
// Web/app/Views/layout/header.php
if (session_status() == PHP_SESSION_NONE) { session_start(); } // Đảm bảo session bắt đầu

// --- Giả sử bạn có cách lấy số lượng SP trong giỏ hàng và wishlist ---
// Ví dụ đơn giản:
$cartItemCount = count($_SESSION['cart'] ?? []);
$wishlistItemCount = 0; // Cần logic lấy từ DB nếu đã login
if(isset($_SESSION['user_id'])) {
    // Giả sử bạn có thể gọi Wishlist model ở đây hoặc đã lấy sẵn từ controller nào đó
    // require_once BASE_PATH . '/app/Models/Wishlist.php'; // Không nên require model trong view
    // $wishlistItemCount = count(Wishlist::getWishlistedProductIds($_SESSION['user_id']));
    // Tạm thời để là 0 hoặc lấy từ một biến global/truyền vào nếu có
}
// --- Kết thúc phần giả sử ---

?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $pageTitle ?? 'Trang chủ' ?> - My Shop</title>
        <?php // Thêm link tới FontAwesome nếu bạn dùng icon ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <style>
            /* --- Reset & Basic Styles --- */
            body { font-family: sans-serif; margin: 0; padding: 0; background-color: #f4f6f9; color: #333; font-size: 15px; }
            .container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }
            a { color: #007bff; text-decoration: none; }
            a:hover { text-decoration: underline; }
            img { max-width: 100%; height: auto; }
            ul { list-style: none; padding: 0; margin: 0; }
            button { cursor: pointer; }

            /* --- Header Styles --- */
            .site-header { background-color: #ffffff; padding: 15px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 100; }
            .header-container { display: flex; justify-content: space-between; align-items: center; }
            .site-logo a { font-size: 1.8em; font-weight: bold; color: #333; text-decoration: none; }
            .main-navigation ul { display: flex; gap: 25px; }
            .main-navigation a { color: #333; font-weight: 500; font-size: 1.05em; padding: 5px 0; position: relative; text-decoration: none; }
            .main-navigation a::after { content: ''; position: absolute; width: 0; height: 2px; bottom: -2px; left: 0; background-color: #007bff; transition: width 0.3s ease; }
            .main-navigation a:hover::after, .main-navigation a.active::after { width: 100%; }
            .header-actions { display: flex; align-items: center; gap: 20px; }
            .header-actions a { color: #555; font-size: 1.2em; position: relative; }
            .header-actions a span.count {
                position: absolute; top: -8px; right: -10px; background-color: red; color: white;
                font-size: 0.7em; border-radius: 50%; width: 18px; height: 18px;
                display: flex; justify-content: center; align-items: center; font-weight: bold;
            }
            .user-menu span { margin-right: 10px; }
            .user-menu a { font-size: 1em; margin-left: 5px;}

            /* --- Flash Message Style --- */
            .flash-message { padding: 15px; margin: 20px auto; border-radius: 5px; border: 1px solid transparent; text-align: center; max-width: 1170px; }
            .flash-message.success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
            .flash-message.error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
            .flash-message.info { background-color: #d1ecf1; color: #0c5460; border-color: #bee5eb; }

            /* --- Main Content Basic Style --- */
            main { padding: 30px 0; }
            h1, h2, h3 { color: #343a40; }
            h1 { text-align: center; margin-bottom: 30px; }
            h2 { border-bottom: 1px solid #dee2e6; padding-bottom: 10px; margin-bottom: 20px; margin-top: 30px; font-size: 1.5em;}


            /* --- CSS cho Sticky Footer --- */
            html {
                height: 100%; /* Đảm bảo thẻ html chiếm toàn bộ chiều cao */
            }

            body {
                display: flex;           /* Sử dụng Flexbox */
                flex-direction: column;  /* Các thành phần con xếp chồng lên nhau */
                min-height: 100vh;       /* Chiều cao tối thiểu bằng chiều cao màn hình */
                /* Giữ lại các style cũ của body nếu cần */
                font-family: sans-serif;
                margin: 0;
                padding: 0;
                background-color: #f4f6f9;
                color: #333;
                font-size: 15px;
            }

            main.container { /* Chọn thẻ main có class container */
                flex-grow: 1; /* Cho phép main content "lớn lên" để đẩy footer xuống */
                /* Giữ lại các style cũ của main.container */
                max-width: 1200px;
                margin: 0 auto; /* Căn giữa container */
                width: 100%; /* Đảm bảo container chiếm không gian */
                padding: 30px 15px; /* Điều chỉnh padding của main */
            }

            /* --- Kết thúc CSS cho Sticky Footer --- */
        </style>
    </head>
<body>

    <header class="site-header">
        <div class="container header-container">
            <div class="site-logo">
                <a href="?page=home">MyShop</a>
            </div>
            <nav class="main-navigation">
                <ul>
                    <?php $currentPage = $_GET['page'] ?? 'home'; // Lấy trang hiện tại để active link ?>
                    <li><a href="?page=home" class="<?= ($currentPage == 'home') ? 'active' : '' ?>">Trang chủ</a></li>
                    <li><a href="?page=shop_grid" class="<?= ($currentPage == 'shop_grid') ? 'active' : '' ?>">Cửa hàng</a></li>
                    <?php // Thêm các link khác nếu cần: Giới thiệu, Tin tức... ?>
                    <li><a href="?page=contact" class="<?= ($currentPage == 'contact') ? 'active' : '' ?>">Liên hệ</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <?php // Nút tìm kiếm có thể để ở đây hoặc trong Hero section ?>
                <?php /* <a href="#search-modal" title="Tìm kiếm"><i class="fas fa-search"></i></a> */ ?>

                <?php // Wishlist ?>
                <a href="?page=wishlist" title="Danh sách yêu thích">
                    <i class="fas fa-heart"></i>
                    <?php if ($wishlistItemCount > 0): ?>
                        <span class="count"><?= $wishlistItemCount ?></span>
                    <?php endif; ?>
                </a>

                <?php // Cart ?>
                <a href="?page=cart" title="Giỏ hàng">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($cartItemCount > 0): ?>
                        <span class="count"><?= $cartItemCount ?></span>
                    <?php endif; ?>
                </a>

                <?php // User Menu ?>
                <div class="user-menu">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span><a href="?page=profile" title="Tài khoản">Chào, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>!</a></span>
                        <a href="?page=logout" title="Đăng xuất"><i class="fas fa-sign-out-alt"></i></a>
                    <?php else: ?>
                        <a href="?page=login" title="Đăng nhập"><i class="fas fa-user"></i></a>
                        <a href="?page=register" title="Đăng ký" style="margin-left: 10px;">Đăng ký</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

<main class="container">
<?php // Hiển thị flash message ngay dưới header ?>
<?php $flashMessage = $_SESSION['flash_message'] ?? null; if ($flashMessage): unset($_SESSION['flash_message']); ?>
    <div class="flash-message <?= htmlspecialchars($flashMessage['type']) ?>">
        <?= htmlspecialchars($flashMessage['message']) ?>
    </div>
<?php endif; ?>

<?php // Nội dung chính của trang sẽ được chèn vào đây ?>