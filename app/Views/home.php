<?php
// Web/app/Views/Home.php
$pageTitle = 'Trang chủ';
include_once __DIR__ . '/../layout/header.php'; // Header includes Bootstrap CSS/JS

// Get data from controller
// $search = $search ?? ''; // Không cần search ở home nữa
$brand = $brand ?? ''; // Selected brand (if any)
$brands = $brands ?? []; // List of all brands
$products = $products ?? []; // Main product list (featured/latest)
$latestProducts = $latestProducts ?? [];
$topRated = $topRated ?? [];
$mostReviewed = $mostReviewed ?? [];
$isLoggedIn = $isLoggedIn ?? false;
$wishlistedIds = $wishlistedIds ?? [];

// Helper function needed for sidebar links (keep as is)
function build_query_string_home(array $params): string {
    $currentParams = $_GET;
    foreach ($params as $key => $value) {
        if ($value === null || $value === '') { unset($currentParams[$key]); }
        else { $currentParams[$key] = $value; }
    }
    $currentParams['page'] = 'shop_grid'; // Chuyển đến shop_grid
    if (isset($currentParams['pg']) && (int)$currentParams['pg'] <= 1) { unset($currentParams['pg']); }
    return http_build_query($currentParams);
}

?>
    <style>
        /* Minimal custom styles */
        .hero-section { background-color: #e9ecef; }
        .filter-widget .list-group-item-action.active { z-index: 2; color: #fff; background-color: #0d6efd; border-color: #0d6efd;}
        .product-card .card-img-top { height: 200px; object-fit: contain; background-color: #fff; padding: 0.5rem; }
        .product-card .card-title { min-height: 3em; }
        .product-card .price { color: #dc3545; }
        .product-card .actions .btn-wishlist { color: #6c757d; border: none;}
        .product-card .actions .btn-wishlist.active { color: #dc3545; }
        .product-card .actions .btn-cart { color: #198754; border: none; }
        .product-card .actions .btn-wishlist.disabled,
        .product-card .actions .btn-cart.disabled { opacity: 0.5; }
        .product-list-widget img { width: 50px; height: 50px; object-fit: contain; }
        .product-list-widget .info .name { font-weight: 500; text-decoration: none; color: #212529;}
        .product-list-widget .info .name:hover { color: #0d6efd; }
        .product-list-widget .info .price { color: #dc3545; }
        .product-list-widget .info .reviews { color: #6c757d; font-size: 0.85em; }
    </style>

<?php // Hero Section ?>
    <div class="hero-section p-5 mb-4 rounded-3">
        <div class="container-fluid py-5 text-center">
            <h1 class="display-5 fw-bold">Chào mừng đến với MyShop!</h1>
            <p class="fs-4 text-muted">Tìm kiếm sản phẩm công nghệ yêu thích của bạn.</p>
            <a href="?page=shop_grid" class="btn btn-primary btn-lg mt-3">Khám phá Cửa hàng</a>
        </div>
    </div>

    <div class="row g-4">

        <?php // ----- Sidebar Column ----- ?>
        <aside class="col-lg-3">

            <?php // ----- Search Filter (Form tìm kiếm MỚI cho Home) ----- ?>
            <div class="card shadow-sm mb-4 filter-widget">
                <div class="card-header"><h5 class="mb-0">Tìm kiếm Sản phẩm</h5></div>
                <div class="card-body">
                    <?php // FORM NÀY SẼ SUBMIT ĐẾN ?page=shop_grid ?>
                    <form method="GET" action="?">
                        <?php // Input ẩn để đảm bảo submit đến trang shop_grid ?>
                        <input type="hidden" name="page" value="shop_grid">
                        <?php // Không cần giữ lại các bộ lọc khác ở đây ?>
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Nhập tên sản phẩm..." aria-label="Tìm sản phẩm">
                            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>
            <?php // ----- KẾT THÚC FORM TÌM KIẾM MỚI ----- ?>


            <?php // ----- Brand Filter (Giữ nguyên) ----- ?>
            <div class="card shadow-sm mb-4 filter-widget">
                <div class="card-header bg-light py-2">
                    <h5 class="mb-0 fs-6 fw-semibold"><i class="fas fa-tags me-1 text-primary"></i> Hãng sản xuất</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php // Link này chỉ cần đến shop_grid không lọc hãng ?>
                    <a href="?page=shop_grid"
                       class="list-group-item list-group-item-action py-2 <?= (empty($brand) || $brand == 'All') ? 'active' : '' ?>">
                        Tất cả Hãng
                    </a>
                    <?php foreach ($brands as $b): ?>
                        <a href="?<?= build_query_string_home(['brand' => $b, 'pg' => null]) ?>"
                           class="list-group-item list-group-item-action py-2 <?= ($brand == $b) ? 'active' : '' ?>">
                            <?= htmlspecialchars($b) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php // ----- Latest Products Widget (Giữ nguyên)----- ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h5 class="mb-0 fs-6 fw-semibold"><i class="fas fa-star me-2 text-warning"></i>Sản phẩm mới</h5></div>
                <ul class="list-group list-group-flush product-list-widget">
                    <?php if (empty($latestProducts)): ?>
                        <li class="list-group-item text-muted small">Chưa có sản phẩm mới.</li>
                    <?php else: ?>
                        <?php foreach ($latestProducts as $p): ?>
                            <li class="list-group-item d-flex align-items-center">
                                <a href="?page=product_detail&id=<?= (int)($p['id'] ?? 0) ?>">
                                    <img src="/webfinal/public/img/<?= htmlspecialchars($p['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($p['name'] ?? '') ?>" loading="lazy" class="border rounded me-2">
                                </a>
                                <div class="info flex-grow-1">
                                    <a href="?page=product_detail&id=<?= (int)($p['id'] ?? 0) ?>" class="name d-block text-truncate small"><?= htmlspecialchars($p['name'] ?? 'N/A') ?></a>
                                    <span class="price d-block fw-bold small"><?= number_format($p['price'] ?? 0,0,',','.') ?>₫</span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

        </aside>

        <?php // ----- Main Content Column (Giữ nguyên phần hiển thị sản phẩm nổi bật) ----- ?>
        <section class="col-lg-9">
            <h2 class="mb-3">Sản phẩm nổi bật</h2>
            <?php if (!empty($products)): ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
                    <?php foreach ($products as $p): ?>
                        <?php
                        $pId = (int)($p['id'] ?? 0);
                        $stock = (int)($p['stock'] ?? 0);
                        $isProductWishlisted = false;
                        if ($isLoggedIn && is_array($wishlistedIds) && !empty($wishlistedIds)) {
                            $isProductWishlisted = in_array($pId, $wishlistedIds);
                        }
                        ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm product-card">
                                <a href="?page=product_detail&id=<?= $pId ?>" class="text-center">
                                    <img src="/webfinal/public/img/<?= htmlspecialchars($p['image'] ?? 'default.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name'] ?? '') ?>" loading="lazy">
                                </a>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">
                                        <a href="?page=product_detail&id=<?= $pId ?>" class="text-dark text-decoration-none">
                                            <?= htmlspecialchars($p['name'] ?? 'N/A') ?>
                                        </a>
                                    </h5>
                                    <p class="card-text price fw-bold fs-5 mt-auto"><?= number_format($p['price'] ?? 0,0,',','.') ?>₫</p>
                                </div>
                                <div class="card-footer bg-transparent border-top-0 pb-3">
                                    <div class="actions d-flex justify-content-between align-items-center">
                                        <button type="button"
                                                class="btn btn-link btn-wishlist p-0 <?= $isProductWishlisted ? 'active' : '' ?> <?= !$isLoggedIn ? 'disabled' : '' ?>"
                                                data-product-id="<?= $pId ?>"
                                                data-is-wishlisted="<?= $isProductWishlisted ? '1' : '0' ?>"
                                                title="<?= !$isLoggedIn ? 'Đăng nhập để yêu thích' : ($isProductWishlisted ? 'Xóa khỏi Yêu thích' : 'Thêm vào Yêu thích') ?>"
                                        >
                                            <i class="fas fa-heart fs-4"></i>
                                        </button>
                                        <a href="?page=product_detail&id=<?= $pId ?>"
                                           class="btn btn-link btn-cart p-0 <?= $stock <= 0 ? 'disabled' : '' ?>"
                                           title="<?= $stock > 0 ? 'Xem chi tiết sản phẩm' : 'Hết hàng' ?>"
                                            <?php if($stock <= 0): ?> onclick="event.preventDefault(); alert('Sản phẩm này hiện đã hết hàng.');" <?php endif; ?>
                                        >
                                            <i class="fas fa-cart-plus fs-4"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-4">
                    <a href="?page=shop_grid" class="btn btn-outline-secondary">Xem tất cả sản phẩm <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            <?php else: ?>
                <div class="alert alert-warning" role="alert">
                    Không tìm thấy sản phẩm nào phù hợp.
                </div>
            <?php endif; ?>

        </section>

    </div>

<?php // JavaScript for Wishlist Toggle (Giữ nguyên) ?>
    <script>
        // --- Wishlist Toggle Function (Chỉ chứa logic AJAX) ---
        async function toggleWishlist(buttonElement, productId) {
            // ... (code toggleWishlist giữ nguyên như trước) ...
            console.log("AJAX toggleWishlist called for button:", buttonElement, "productId:", productId);

            const isWishlisted = buttonElement.dataset.isWishlisted === '1';
            const action = isWishlisted ? 'wishlist_remove' : 'wishlist_add';
            const icon = buttonElement.querySelector('i');

            buttonElement.disabled = true;
            if(icon) { icon.classList.remove('fa-heart'); icon.classList.add('fa-spinner', 'fa-spin'); }

            try {
                const response = await fetch(`?page=${action}&id=${productId}&ajax=1&redirect=no`, {
                    method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    const data = await response.json();
                    console.log("Wishlist Response:", data);
                    if (data.success) {
                        buttonElement.dataset.isWishlisted = isWishlisted ? '0' : '1';
                        buttonElement.classList.toggle('active');
                        buttonElement.title = isWishlisted ? 'Thêm vào Yêu thích' : 'Xóa khỏi Yêu thích';
                        if (typeof data.wishlistItemCount !== 'undefined') {
                            const wishlistCountElement = document.getElementById('header-wishlist-count');
                            if (wishlistCountElement) {
                                const newCount = parseInt(data.wishlistItemCount);
                                wishlistCountElement.textContent = newCount;
                                wishlistCountElement.style.display = newCount > 0 ? 'inline-block' : 'none';
                            }
                        }
                    } else {
                        if (data.login_required) { // Xử lý nếu Controller trả về yêu cầu đăng nhập
                            const currentUrl = encodeURIComponent(window.location.href || '?page=home');
                            window.location.href = `?page=login&redirect=${currentUrl}`;
                            return; // Dừng xử lý tiếp
                        }
                        alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                    }
                } else {
                    const textResponse = await response.text();
                    console.error("Non-JSON Wishlist Response:", textResponse);
                    throw new Error('Received non-JSON response from server during wishlist toggle.');
                }
            } catch (error) {
                console.error('Error toggling wishlist:', error);
                alert('Lỗi kết nối hoặc xử lý (Wishlist). Vui lòng thử lại.');
            } finally {
                buttonElement.disabled = false;
                if(icon) { icon.classList.remove('fa-spinner', 'fa-spin'); icon.classList.add('fa-heart'); }
            }
        }
    </script>

<?php
include_once __DIR__ . '/../layout/footer.php'; // Footer chứa event listener cho wishlist
?>