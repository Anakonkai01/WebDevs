<?php
// Get data from BaseController
$cartItemCount = $cartItemCount ?? 0;
$wishlistItemCount = $wishlistItemCount ?? 0;
$isLoggedIn = $isLoggedIn ?? false;

// Get username if logged in
$username = $isLoggedIn ? htmlspecialchars($_SESSION['username'] ?? 'User') : '';

// Get current page
$currentPage = $_GET['page'] ?? 'home';

// Handle flash message
$flashMessage = $flashMessage ?? null;

// Function to check active page
function isActivePage($pageName, $currentPage) {
    return $pageName === $currentPage ? 'active fw-semibold' : '';
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'MyShop') ?></title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        main {
            flex-grow: 1;
        }

        .header-actions .badge {
            font-size: 0.65em;            
            padding: 0.2em 0.4em;
            transform: translate(50%, -50%);
        }
        .flash-message-container {
             max-width: 1140px; /* Giống container Bootstrap */
             margin-left: auto;
             margin-right: auto;
        }
         /* Ensure the dropdown menu has a high enough z-index */
        .dropdown-menu {
            z-index: 1050;
        }
        header.site-header {
           z-index: 1040;
           position: sticky;
           top: 0;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100" data-is-logged-in="<?= $isLoggedIn ? 'true' : 'false' ?>">

    <header class="site-header bg-white shadow-sm border-bottom">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container">
                <!-- Logo -->
                <a class="navbar-brand fs-4 fw-semibold" href="?page=home">MyShop</a>
                <!-- Button toggle -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link <?= isActivePage('home', $currentPage) ?>" href="?page=home">Trang chủ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= isActivePage('shop_grid', $currentPage) ?>" href="?page=shop_grid">Cửa hàng</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= isActivePage('contact', $currentPage) ?>" href="?page=contact">Liên hệ</a>
                        </li>
                    </ul>

                    <!-- Action -->
                    <ul class="navbar-nav ms-auto d-flex flex-row align-items-center header-actions">
                        <li class="nav-item me-2 me-lg-3 position-relative">
                            <a class="nav-link" href="?page=wishlist" title="Danh sách yêu thích">
                                <i class="fas fa-heart"></i>
                                <!-- Badge -->
                                <span class="badge bg-danger rounded-pill" id="header-wishlist-count" style="<?= $wishlistItemCount > 0 ? '' : 'display: none;' ?>">
                                    <?= $wishlistItemCount ?>
                                </span>
                            </a>
                        </li>

                        <li class="nav-item me-2 me-lg-3 position-relative">
                            <a class="nav-link" href="?page=cart" title="Giỏ hàng">
                                <i class="fas fa-shopping-cart"></i>
                                <!-- Badge -->
                                <span class="badge bg-danger rounded-pill" id="header-cart-count" style="<?= $cartItemCount > 0 ? '' : 'display: none;' ?>">
                                    <?= $cartItemCount ?>
                                </span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <?php if ($isLoggedIn): ?>
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userAccountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle me-1"></i>
                                    <?= $username ?>
                                </a>
                                <!-- user menu -->
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userAccountDropdown">
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="?page=logout" onclick="return confirm('Bạn chắc chắn muốn đăng xuất?');"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                                </ul>
                            <?php else: ?>
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userAccountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user me-1"></i>
                                    Tài khoản
                                </a>
                                <!-- no user menu -->
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

    <!-- Flash message -->
    <div class="flash-message-container mt-3">
        <?php if (isset($flashMessage) && is_array($flashMessage) && isset($flashMessage['type']) && isset($flashMessage['message'])): ?>
            <div class="alert alert-<?= htmlspecialchars($flashMessage['type']) ?> alert-dismissible fade show flash-message" role="alert">
                <?= nl2br(htmlspecialchars($flashMessage['message'])) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

<main class="container my-4">
<!-- Main content of the page will start here -->