<?php
// Web/app/layout/header.php
// Dữ liệu này được truyền từ BaseController->render()
$cartItemCount = $cartItemCount ?? 0;
$wishlistItemCount = $wishlistItemCount ?? 0;
$isLoggedIn = $isLoggedIn ?? false;
$currentPage = $_GET['page'] ?? 'home';
$flashMessage = $flashMessage ?? null; // Lấy flash message từ BaseController
?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($pageTitle ?? 'MyShop') ?></title>

        <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link rel="stylesheet" href="/webfinal/public/css/contact.css">

        <style>
            /* CSS Styles giữ nguyên */
            body { display: flex; flex-direction: column; min-height: 100vh; background-color: #f8f9fa; }
            main { flex-grow: 1; }
            .site-footer { background-color: #343a40; color: #f8f9fa; padding: 40px 0; }
            .site-footer a { color: #adb5bd; } .site-footer a:hover { color: #fff; }
            .header-actions .nav-link i,
            .header-actions .dropdown-toggle i { font-size: 1.2rem; vertical-align: middle; }
            .header-actions .badge {
                position: absolute;
                top: 10px;
                right: -8px;
                font-size: 0.65em;
                padding: 0.2em 0.4em;
            }
            .navbar-brand { font-weight: 600; }
            .navbar .nav-link { padding-left: 0.8rem; padding-right: 0.8rem; }
            .dropdown-menu { font-size: 0.95rem; }
            .flash-message { margin-top: 1rem; margin-bottom: 1rem; /* Điều chỉnh margin */ }
        </style>
    </head>
<body class="d-flex flex-column min-vh-100" data-is-logged-in="<?= $isLoggedIn ? 'true' : 'false' ?>">

    <header class="site-header sticky-top bg-white shadow-sm border-bottom">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container">
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

                    <ul class="navbar-nav ms-auto d-flex flex-row align-items-center header-actions">
                        <li class="nav-item me-2 me-lg-3">
                            <a class="nav-link position-relative" href="?page=wishlist" title="Danh sách yêu thích">
                                <i class="fas fa-heart"></i>
                                <span class="badge bg-danger rounded-pill translate-middle-y"
                                      id="header-wishlist-count"
                                      style="<?= $wishlistItemCount > 0 ? '' : 'display: none;' ?>">
                                <?= $wishlistItemCount ?>
                            </span>
                            </a>
                        </li>

                        <li class="nav-item me-2 me-lg-3">
                            <a class="nav-link position-relative" href="?page=cart" title="Giỏ hàng">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="badge bg-danger rounded-pill translate-middle-y" id="header-cart-count" style="<?= $cartItemCount > 0 ? '' : 'display: none;' ?>">
                                <?= $cartItemCount ?>
                            </span>
                            </a>
                        </li>

                        <li class="nav-item dropdown">
                            <?php if ($isLoggedIn): ?>
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userAccountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle me-2"></i>
                                    <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userAccountDropdown">
                                    <li><a class="dropdown-item" href="?page=profile"><i class="fas fa-user-edit me-2 text-muted"></i>Hồ sơ</a></li>
                                    <li><a class="dropdown-item" href="?page=order_history"><i class="fas fa-history me-2 text-muted"></i>Lịch sử đơn hàng</a></li>
                                    <li><a class="dropdown-item" href="?page=wishlist"><i class="fas fa-heart me-2 text-muted"></i>Danh sách yêu thích</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="?page=logout"><i class="fas fa-sign-out-alt me-2 text-muted"></i>Đăng xuất</a></li>
                                </ul>
                            <?php else: ?>
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userAccountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user me-2"></i>
                                    Tài khoản
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userAccountDropdown">
                                    <li><a class="dropdown-item" href="?page=login"><i class="fas fa-sign-in-alt me-2 text-muted"></i>Đăng nhập</a></li>
                                    <li><a class="dropdown-item" href="?page=register"><i class="fas fa-user-plus me-2 text-muted"></i>Đăng ký</a></li>
                                </ul>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

<main class="container my-4">
<?php // Hiển thị Flash message (được truyền từ BaseController->render) ?>
<?php if (isset($flashMessage) && is_array($flashMessage) && isset($flashMessage['type']) && isset($flashMessage['message'])): ?>
    <div class="alert alert-<?= htmlspecialchars($flashMessage['type']) ?> alert-dismissible fade show flash-message" role="alert">
        <?= nl2br(htmlspecialchars($flashMessage['message'])) // Dùng nl2br nếu muốn hỗ trợ xuống dòng trong message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php // Main page content starts here ?>