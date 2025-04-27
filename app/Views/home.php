<?php
// Web/app/Views/Home.php
global $isLoggedIn, $wishlistedIds;
$pageTitle = 'Trang chủ';
// Header bao gồm Bootstrap CSS/JS và các biến toàn cục như $isLoggedIn, $wishlistedIds
include_once __DIR__ . '/../layout/header.php'; // Đã bao gồm header được cập nhật

// Lấy dữ liệu từ controller
$brand = $brand ?? '';
$brands = $brands ?? [];
$products = $products ?? [];
$latestProducts = $latestProducts ?? [];

// Helper function để tạo query string cho link filter (chuyển đến shop_grid)
function build_query_string_home(array $params): string {
    $currentParams = $_GET;
    $currentParams['page'] = 'shop_grid';
    foreach ($params as $key => $value) {
        if ($value === null || $value === '') { unset($currentParams[$key]); }
        else { $currentParams[$key] = $value; }
    }
    unset($currentParams['pg']);
    return http_build_query($currentParams);
}
?>
<link rel="stylesheet" href="/webfinal/public/css/home.css">

<?php // ----- Hero Section ----- ?>
    <div class="hero-section text-center text-white mb-4"> <?php // Đảm bảo các class này tồn tại ?>
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h1 class="display-4 fw-bold mb-3">Khám Phá Sản Phẩm Công Nghệ Mới Nhất</h1>
            <p class="lead mb-4 mx-auto" style="max-width: 700px;"> <?php // Tăng max-width nếu cần ?>
                Tìm kiếm những thiết bị điện tử, phụ kiện và sản phẩm công nghệ hàng đầu tại MyShop.
            </p>
            <a href="?page=shop_grid" class="btn btn-primary btn-lg mt-3 px-4 py-2 fw-medium">
                <i class="fas fa-shopping-bag me-2"></i> Mua Sắm Ngay
            </a>
        </div>
    </div>
<?php // ----- End Hero Section ----- ?>

<div class="row g-4">

    <?php // ----- Sidebar Column ----- ?>
    <aside class="col-lg-3">
        <?php // ----- Search Form (Submit to shop_grid) ----- ?>
        <div class="card shadow-sm mb-4 filter-widget">
            <div class="card-header"><h5 class="mb-0">Tìm kiếm Sản phẩm</h5></div>
            <div class="card-body">
                <form method="GET" action="?">
                    <input type="hidden" name="page" value="shop_grid">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Nhập tên sản phẩm..." aria-label="Tìm sản phẩm">
                        <button class="btn btn-outline-secondary" type="submit" aria-label="Tìm kiếm"><i class="fas fa-search"></i></button>
                    </div>
                </form>
            </div>
        </div>
        <?php // ----- Brand Filter ----- ?>
        <div class="card shadow-sm mb-4 filter-widget">
            <div class="card-header bg-light py-2">
                <h5 class="mb-0 fs-6 fw-semibold"><i class="fas fa-tags me-1 text-primary"></i> Hãng sản xuất</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="?page=shop_grid" class="list-group-item list-group-item-action py-2 px-3 <?= (empty($brand) || $brand == 'All') ? 'active' : '' ?>">
                    Tất cả Hãng
                </a>
                <?php foreach ($brands as $b): ?>
                    <a href="?<?= build_query_string_home(['brand' => $b]) ?>"
                       class="list-group-item list-group-item-action py-2 px-3 <?= ($brand == $b) ? 'active' : '' ?>">
                        <?= htmlspecialchars($b) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php // ----- Latest Products Widget ----- ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header"><h5 class="mb-0 fs-6 fw-semibold"><i class="fas fa-star me-2 text-warning"></i>Sản phẩm mới</h5></div>
            <ul class="list-group list-group-flush product-list-widget">
                <?php if (empty($latestProducts)): ?>
                    <li class="list-group-item text-muted small p-3">Chưa có sản phẩm mới.</li>
                <?php else: ?>
                    <?php foreach ($latestProducts as $p): ?>
                        <?php $pId = (int)($p['id'] ?? 0); ?>
                        <li class="list-group-item d-flex align-items-center p-2">
                            <a href="?page=product_detail&id=<?= $pId ?>" class="flex-shrink-0">
                                <img src="/webfinal/public/img/<?= htmlspecialchars($p['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($p['name'] ?? '') ?>" loading="lazy" class="border rounded me-2" style="width: 60px; height: 60px; object-fit: contain;">
                            </a>
                            <div class="info flex-grow-1 overflow-hidden">
                                <a href="?page=product_detail&id=<?= $pId ?>" class="name d-block text-truncate small fw-medium text-decoration-none text-dark"><?= htmlspecialchars($p['name'] ?? 'N/A') ?></a>
                                <span class="price d-block fw-bold small text-danger"><?= number_format($p['price'] ?? 0,0,',','.') ?>₫</span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </aside> <?php // End Sidebar ?>

    <?php // ----- Main Content Column ----- ?>
    <section class="col-lg-9">
        <h2 class="mb-3">Sản phẩm nổi bật</h2>
        <?php if (!empty($products)): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
                <?php foreach ($products as $p): ?>
                    <?php
                    $pId = (int)($p['id'] ?? 0);
                    $stock = (int)($p['stock'] ?? 0);
                    $isProductWishlisted = $isLoggedIn && is_array($wishlistedIds) && in_array($pId, $wishlistedIds);
                    ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm product-card">
                            <a href="?page=product_detail&id=<?= $pId ?>" class="text-center d-block p-2">
                                <img src="/webfinal/public/img/<?= htmlspecialchars($p['image'] ?? 'default.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name'] ?? '') ?>" loading="lazy" style="max-height: 200px; object-fit: contain;">
                            </a>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fs-6 mb-2" style="min-height: 3em;">
                                    <a href="?page=product_detail&id=<?= $pId ?>" class="text-dark text-decoration-none product-name">
                                        <?= htmlspecialchars($p['name'] ?? 'N/A') ?>
                                    </a>
                                </h5>
                                <p class="card-text text-muted small mb-2 flex-grow-1">
                                   <?= htmlspecialchars($p['brand'] ?? 'N/A') ?> | <?= number_format($p['rating'] ?? 0, 1) ?> ★
                                </p>
                                <p class="card-text price fw-bold fs-5 mb-0 mt-auto text-danger"><?= number_format($p['price'] ?? 0,0,',','.') ?>₫</p>
                            </div>
                            <div class="card-footer bg-transparent border-top-0 pb-3 pt-2">
                                <div class="actions d-flex justify-content-between align-items-center">
                                    <button type="button"
                                            class="btn btn-link btn-wishlist p-0 <?= !$isLoggedIn ? 'disabled' : ($isProductWishlisted ? 'active text-danger' : 'text-secondary') ?>"
                                            data-product-id="<?= $pId ?>"
                                            data-is-wishlisted="<?= $isProductWishlisted ? '1' : '0' ?>"
                                            title="<?= !$isLoggedIn ? 'Đăng nhập để yêu thích' : ($isProductWishlisted ? 'Xóa khỏi Yêu thích' : 'Thêm vào Yêu thích') ?>"
                                            <?= !$isLoggedIn ? 'disabled' : '' ?>
                                            aria-label="Yêu thích">
                                        <i class="fas fa-heart fs-4"></i>
                                    </button>
                                    <a href="?page=product_detail&id=<?= $pId ?>"
                                       class="btn btn-link btn-cart p-0 <?= $stock <= 0 ? 'disabled text-muted' : 'text-success' ?>"
                                       title="<?= $stock > 0 ? 'Xem chi tiết sản phẩm' : 'Hết hàng' ?>"
                                       aria-label="Xem chi tiết"
                                        <?php if($stock <= 0): ?> aria-disabled="true" <?php endif; ?>>
                                        <i class="fas fa-cart-plus fs-4"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="?page=shop_grid" class="btn btn-outline-primary">Xem tất cả sản phẩm <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        <?php else: ?>
            <div class="alert alert-warning" role="alert">
                Hiện chưa có sản phẩm nào.
            </div>
        <?php endif; ?>
    </section> <?php // End Main Content ?>
</div> <?php // End Row ?>

<?php
// Đã xóa include home.js - dùng footer.php
include_once __DIR__ . '/../layout/footer.php';
?>