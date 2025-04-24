<?php
// Web/app/layout/header.php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// Get cart count
$cartItemCount = count($_SESSION['cart'] ?? []);

// Get wishlist count (ensure $wishlistedIds is available if needed globally, otherwise fetch)
// Example: Fetching if not passed by controller (Not ideal in view, but for completeness)
$wishlistItemCount = 0;
if (isset($_SESSION['user_id'])) {
    // Prefer getting $wishlistedIds from controller data if passed
    // If not passed, fallback to direct model call (less optimal)
    if (!isset($wishlistedIds)) { // Check if controller provided it
        require_once BASE_PATH . '/app/Models/Wishlist.php'; // Need model if called directly
        $tempWishlistIds = Wishlist::getWishlistedProductIds((int)$_SESSION['user_id']);
        $wishlistItemCount = is_array($tempWishlistIds) ? count($tempWishlistIds) : 0;
    } elseif (is_array($wishlistedIds)) {
        $wishlistItemCount = count($wishlistedIds); // Use controller data if available
    }
}

// Determine current page for active link styling
$currentPage = $_GET['page'] ?? 'home';
?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($pageTitle ?? 'MyShop') ?></title>

        <link href="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        <style>
            body { display: flex; flex-direction: column; min-height: 100vh; background-color: #f8f9fa; }
            main { flex-grow: 1; }
            .site-footer { background-color: #343a40; color: #f8f9fa; padding: 40px 0; }
            .site-footer a { color: #adb5bd; } .site-footer a:hover { color: #fff; }
            /* Consistent icon sizing in header actions */
            .header-actions .nav-link i,
            .header-actions .dropdown-toggle i { font-size: 1.2rem; /* Adjust size as needed */ vertical-align: middle; }
            .header-actions .badge { /* Position badge relative to icon link */
                position: absolute;
                /* Sửa giá trị top ở đây, ví dụ: thành -2px để dịch xuống 3px so với -5px */
                top: 10px;
                right: -8px; /* Giữ nguyên hoặc điều chỉnh nếu cần */
                font-size: 0.65em;
                padding: 0.2em 0.4em;
            }

            .navbar-brand { font-weight: 600; }
            .navbar .nav-link { padding-left: 0.8rem; padding-right: 0.8rem; }
            .dropdown-menu { font-size: 0.95rem; }
            .flash-message { margin-top: 1rem; } /* Ensure flash message has margin */
        </style>
    </head>
<body class="d-flex flex-column min-vh-100">

    <header class="site-header sticky-top bg-white shadow-sm border-bottom"> <?php // Changed to bg-white, added border ?>
        <nav class="navbar navbar-expand-lg navbar-light"> <?php // Removed container here, will add inside ?>
            <div class="container"> <?php // Use a standard container for content alignment ?>
                <a class="navbar-brand fs-4" href="?page=home">MyShop</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage == 'home') ? 'active fw-semibold' : '' ?>" href="?page=home">Trang chủ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage == 'shop_grid') ? 'active fw-semibold' : '' ?>" href="?page=shop_grid">Cửa hàng</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage == 'contact') ? 'active fw-semibold' : '' ?>" href="?page=contact">Liên hệ</a>
                        </li>
                        <?php // Add other main navigation links here ?>
                    </ul>

                    <?php // Right-aligned items: Icons and User Menu ?>
                    <ul class="navbar-nav ms-auto d-flex flex-row align-items-center header-actions"> <?php // Use flex-row for horizontal layout on mobile when collapsed ?>
                        <?php // Wishlist ?>
                        <li class="nav-item me-2 me-lg-3"> <?php // Add margin ?>
                            <a class="nav-link position-relative" href="?page=wishlist" title="Danh sách yêu thích">
                                <i class="fas fa-heart"></i>
                                <?php // *** THÊM ID VÀO ĐÂY *** ?>
                                <span class="badge bg-danger rounded-pill translate-middle-y"
                                      id="header-wishlist-count" <?php // <<< THÊM ID NÀY ?>
                                      style="<?= $wishlistItemCount > 0 ? '' : 'display: none;' ?>">
                                    <?= $wishlistItemCount ?>
                                </span>
                            </a>
                        </li>

                        <?php // Cart ?>
                        <li class="nav-item me-2 me-lg-3">
                            <a class="nav-link position-relative" href="?page=cart" title="Giỏ hàng">
                                <i class="fas fa-shopping-cart"></i>
                                <?php // Thêm ID vào span này ?>
                                <span class="badge bg-danger rounded-pill translate-middle-y" id="header-cart-count" style="<?= $cartItemCount > 0 ? '' : 'display: none;' ?>">
                                <?= $cartItemCount ?>
                            </span>
                            </a>
                        </li>

                        <?php // Separator (Optional) ?>
                        <?php /*
                    <li class="nav-item d-none d-lg-block">
                        <span class="nav-link text-muted">|</span>
                    </li>
                    */ ?>

                        <?php // User Menu Dropdown ?>
                        <li class="nav-item dropdown">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php // Logged In: Show Username and Dropdown ?>
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userAccountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle me-2"></i> <?php // User circle icon ?>
                                    <?= htmlspecialchars($_SESSION['username']) ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userAccountDropdown">
                                    <li><a class="dropdown-item" href="?page=profile"><i class="fas fa-user-edit me-2 text-muted"></i>Hồ sơ</a></li>
                                    <li><a class="dropdown-item" href="?page=order_history"><i class="fas fa-history me-2 text-muted"></i>Lịch sử đơn hàng</a></li>
                                    <li><a class="dropdown-item" href="?page=wishlist"><i class="fas fa-heart me-2 text-muted"></i>Danh sách yêu thích</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="?page=logout"><i class="fas fa-sign-out-alt me-2 text-muted"></i>Đăng xuất</a></li>
                                </ul>
                            <?php else: ?>
                                <?php // Logged Out: Show Login/Register Links (Can be dropdown or direct links) ?>
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userAccountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user me-2"></i>
                                    Tài khoản
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userAccountDropdown">
                                    <li><a class="dropdown-item" href="?page=login"><i class="fas fa-sign-in-alt me-2 text-muted"></i>Đăng nhập</a></li>
                                    <li><a class="dropdown-item" href="?page=register"><i class="fas fa-user-plus me-2 text-muted"></i>Đăng ký</a></li>
                                </ul>
                                <?php // Alternative: Direct Links
                                /*
                                <li class="nav-item">
                                    <a class="nav-link" href="?page=login">Đăng nhập</a>
                                </li>
                                <li class="nav-item">
                                     <a class="nav-link" href="?page=register">Đăng ký</a>
                                </li>
                                */
                                ?>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div> <?php // End navbar-collapse ?>
            </div> <?php // End container ?>
        </nav>
    </header>

<main class="container my-4"> <?php // Added my-4 for margin top/bottom ?>
<?php // Display flash message
$flashMessage = $_SESSION['flash_message'] ?? null;
// Check if it's an array and has the necessary keys
if ($flashMessage && is_array($flashMessage) && isset($flashMessage['type']) && isset($flashMessage['message'])):
    unset($_SESSION['flash_message']); // Clear after displaying
    ?>
    <div class="alert alert-<?= htmlspecialchars($flashMessage['type']) ?> alert-dismissible fade show flash-message" role="alert">
        <?= htmlspecialchars($flashMessage['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php // Main page content starts here ?>