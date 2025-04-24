<?php
// Web/app/Views/Home.php
$pageTitle = 'Trang chủ';
include_once __DIR__ . '/../layout/header.php'; // Header includes Bootstrap CSS/JS

// Get data from controller
$search = $search ?? '';
$brand = $brand ?? ''; // Selected brand (if any) - Note: Home page might not use this directly
$brands = $brands ?? []; // List of all brands
$products = $products ?? []; // Main product list (featured/latest)
$latestProducts = $latestProducts ?? [];
$topRated = $topRated ?? [];
$mostReviewed = $mostReviewed ?? [];
$isLoggedIn = $isLoggedIn ?? false; // Make sure this is passed from HomeController
$wishlistedIds = $wishlistedIds ?? []; // Make sure this is passed from HomeController

// Helper function needed for sidebar links (can be defined here or included)
function build_query_string_home(array $params): string {
    $currentParams = $_GET;
    foreach ($params as $key => $value) {
        if ($value === null || $value === '') { unset($currentParams[$key]); }
        else { $currentParams[$key] = $value; }
    }
    // Links from home sidebar should generally go to shop_grid
    $currentParams['page'] = 'shop_grid';
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
        .product-card .actions .btn-cart.disabled {
            opacity: 0.5; pointer-events: none;
        }
        /* Sidebar product list */
        .product-list-widget img { width: 50px; height: 50px; object-fit: contain; }
        .product-list-widget .info .name { font-weight: 500; text-decoration: none; color: #212529;}
        .product-list-widget .info .name:hover { color: #0d6efd; }
        .product-list-widget .info .price { color: #dc3545; }
        .product-list-widget .info .reviews { color: #6c757d; font-size: 0.85em; }

    </style>

<?php // Hero Section ?>
    <div class="hero-section p-5 mb-4 rounded-3">
        <div class="container-fluid py-5 text-center"> <?php // Centered text ?>
            <h1 class="display-5 fw-bold">Chào mừng đến với MyShop!</h1>
            <p class="fs-4 text-muted">Tìm kiếm sản phẩm công nghệ yêu thích của bạn.</p> <?php // Adjusted text ?>
            <a href="?page=shop_grid" class="btn btn-primary btn-lg mt-3">Khám phá Cửa hàng</a> <?php // Added button ?>
        </div>
    </div>

    <div class="row g-4"> <?php // Bootstrap row with gutters ?>

        <?php // ----- Sidebar Column ----- ?>
        <aside class="col-lg-3">

            <?php // ----- Brand Filter (Using improved style) ----- ?>
            <div class="card shadow-sm mb-4 filter-widget">
                <div class="card-header bg-light py-2">
                    <h5 class="mb-0 fs-6 fw-semibold"><i class="fas fa-tags me-1 text-primary"></i> Hãng sản xuất</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="?page=shop_grid" <?php // Link to shop_grid (no filter) ?>
                       class="list-group-item list-group-item-action py-2 <?= (empty($brand)) ? 'active' : '' ?>"> <?php // Note: $brand might not be set on home, adjust active state if needed ?>
                        Tất cả Hãng
                    </a>
                    <?php foreach ($brands as $b): ?>
                        <a href="?<?= build_query_string_home(['brand' => $b, 'pg' => null]) ?>" <?php // Link to shop_grid WITH brand filter, use correct helper ?>
                           class="list-group-item list-group-item-action py-2 <?= ($brand == $b) ? 'active' : '' ?>">
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
                        <li class="list-group-item text-muted small">Chưa có sản phẩm mới.</li>
                    <?php else: ?>
                        <?php foreach ($latestProducts as $p): ?>
                            <li class="list-group-item d-flex align-items-center">
                                <a href="?page=product_detail&id=<?= (int)($p['id'] ?? 0) ?>">
                                    <img src="/public/img/<?= htmlspecialchars($p['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($p['name'] ?? '') ?>" loading="lazy" class="border rounded me-2">
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
            <?php // --- You can add Top Rated/Most Reviewed widgets here similarly --- ?>

        </aside>

        <?php // ----- Main Content Column ----- ?>
        <section class="col-lg-9">

            <?php // ----- Main Product Grid (Featured/Latest) ----- ?>
            <h2 class="mb-3">Sản phẩm nổi bật</h2>
            <?php if (!empty($products)): ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4"> <?php // Responsive grid ?>
                    <?php foreach ($products as $p): ?>
                        <?php
                        $pId = (int)($p['id'] ?? 0);
                        $stock = (int)($p['stock'] ?? 0);
                        // Wishlist check (same as shop_grid)
                        $isProductWishlisted = false;
                        if ($isLoggedIn && is_array($wishlistedIds) && !empty($wishlistedIds)) {
                            $isProductWishlisted = in_array($pId, $wishlistedIds);
                        }
                        // DEBUG: error_log("Home - PID: $pId, LoggedIn: $isLoggedIn, IsWishlisted: $isProductWishlisted, WishlistIDs: " . print_r($wishlistedIds, true));
                        ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm product-card">
                                <?php // Link only wraps image ?>
                                <a href="?page=product_detail&id=<?= $pId ?>" class="text-center">
                                    <img src="/public/img/<?= htmlspecialchars($p['image'] ?? 'default.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name'] ?? '') ?>" loading="lazy">
                                </a>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">
                                        <?php // Link only wraps title ?>
                                        <a href="?page=product_detail&id=<?= $pId ?>" class="text-dark text-decoration-none">
                                            <?= htmlspecialchars($p['name'] ?? 'N/A') ?>
                                        </a>
                                    </h5>
                                    <p class="card-text price fw-bold fs-5 mt-auto"><?= number_format($p['price'] ?? 0,0,',','.') ?>₫</p>
                                </div>
                                <div class="card-footer bg-transparent border-top-0 pb-3">
                                    <?php // Actions OUTSIDE the main links ?>
                                    <div class="actions d-flex justify-content-between align-items-center">
                                        <?php // Wishlist Button ?>
                                        <button type="button" <?php // Add type="button" ?>
                                                class="btn btn-link btn-wishlist p-0 <?= $isProductWishlisted ? 'active' : '' ?> <?= !$isLoggedIn ? 'disabled' : '' ?>"
                                                onclick="toggleWishlist(this, <?= $pId ?>)"
                                                data-product-id="<?= $pId ?>"
                                                data-is-wishlisted="<?= $isProductWishlisted ? '1' : '0' ?>"
                                                title="<?= !$isLoggedIn ? 'Đăng nhập để yêu thích' : ($isProductWishlisted ? 'Xóa khỏi Yêu thích' : 'Thêm vào Yêu thích') ?>"
                                        >
                                            <i class="fas fa-heart fs-4"></i>
                                        </button>
                                        <?php // Cart Link ?>
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

        </section> <?php // End Main Content Column ?>

    </div> <?php // End row ?>

<?php // JavaScript for Wishlist Toggle (Same as shop_grid.php) ?>
    <script>
        // --- Wishlist Toggle Function (Ensure this is identical across relevant views) ---
        async function toggleWishlist(buttonElement, productId) {
            // Determine the correct redirect URL for login attempts on this specific page
            const loginRedirectUrl = encodeURIComponent(window.location.href || '?page=home'); // Default to home if current URL fails

            // Kiểm tra nút có bị disable không (trường hợp chưa đăng nhập)
            if (buttonElement.classList.contains('disabled')) {
                alert('Vui lòng đăng nhập để sử dụng chức năng này.');
                window.location.href = `?page=login&redirect=${loginRedirectUrl}`; // Use dynamic redirect
                return;
            }

            const isWishlisted = buttonElement.dataset.isWishlisted === '1';
            const action = isWishlisted ? 'wishlist_remove' : 'wishlist_add';
            const icon = buttonElement.querySelector('i');

            buttonElement.disabled = true; // Disable nút tạm thời
            icon.classList.remove('fa-heart'); // Bỏ icon trái tim
            icon.classList.add('fa-spinner', 'fa-spin'); // Thêm icon xoay

            try {
                // Gửi yêu cầu AJAX
                const response = await fetch(`?page=${action}&id=${productId}&ajax=1&redirect=no`, {
                    method: 'GET', // Hoặc POST nếu controller nhận POST cho ajax
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                // Kiểm tra content type trước khi parse JSON
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    const data = await response.json(); // Parse JSON
                    console.log("Wishlist Response:", data); // Log để debug

                    if (data.success) {
                        // Cập nhật trạng thái nút
                        buttonElement.dataset.isWishlisted = isWishlisted ? '0' : '1';
                        buttonElement.classList.toggle('active');
                        buttonElement.title = isWishlisted ? 'Thêm vào Yêu thích' : 'Xóa khỏi Yêu thích';

                        // *** CẬP NHẬT HEADER COUNT ***
                        if (typeof data.wishlistItemCount !== 'undefined') {
                            const wishlistCountElement = document.getElementById('header-wishlist-count');
                            if (wishlistCountElement) {
                                const newCount = parseInt(data.wishlistItemCount);
                                wishlistCountElement.textContent = newCount;
                                // Hiện/ẩn badge dựa trên số lượng mới
                                wishlistCountElement.style.display = newCount > 0 ? 'inline-block' : 'none';
                            }
                        }
                        // *** KẾT THÚC CẬP NHẬT HEADER COUNT ***

                    } else {
                        // Xử lý login_required nếu controller trả về
                        if (data.login_required) {
                            alert(data.message || 'Vui lòng đăng nhập để sử dụng chức năng này.');
                            window.location.href = `?page=login&redirect=${loginRedirectUrl}`; // Chuyển hướng đăng nhập
                        } else {
                            // Các lỗi khác từ server
                            alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                        }
                    }
                } else {
                    // Xử lý trường hợp không phải JSON (ví dụ: lỗi server, trang HTML lỗi)
                    const textResponse = await response.text();
                    console.error("Non-JSON Wishlist Response:", textResponse);
                    throw new Error('Received non-JSON response from server during wishlist toggle.');
                }
            } catch (error) {
                console.error('Error toggling wishlist:', error);
                alert('Lỗi kết nối hoặc xử lý (Wishlist). Vui lòng thử lại.');
            } finally {
                // Khôi phục trạng thái nút
                buttonElement.disabled = false;
                icon.classList.remove('fa-spinner', 'fa-spin'); // Bỏ icon xoay
                icon.classList.add('fa-heart'); // Thêm lại icon trái tim
            }
        }

        // (Nếu home.php có chức năng Add to Cart AJAX thì cần hàm đó ở đây nữa)
    </script>

<?php
include_once __DIR__ . '/../layout/footer.php';
?>

