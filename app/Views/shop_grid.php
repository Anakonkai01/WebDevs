<?php
// Web/app/Views/shop_grid.php
$pageTitle = 'Cửa hàng';
include_once __DIR__ . '/../layout/header.php'; // Header includes Bootstrap

// Helper function
function build_query_string_sg(array $params): string {
    $currentParams = $_GET;
    foreach ($params as $key => $value) {
        // Remove parameter if it's null, empty, or default
        if ($value === null || $value === '' ||
            ($key === 'price_range' && $value === 'all') ||
            ($key === 'brand' && $value === 'All') ||
            ($key === 'sort' && $value === 'created_at_desc')) {
            unset($currentParams[$key]);
        } else {
            $currentParams[$key] = $value;
        }
    }
    $currentParams['page'] = 'shop_grid'; // Ensure page parameter is correct
    // Remove page number if it's 1 or less
    if (isset($currentParams['pg']) && (int)$currentParams['pg'] <= 1) {
        unset($currentParams['pg']);
    }
    // Remove internal price filters from query string
    unset($currentParams['min_price']); unset($currentParams['max_price']);
    return http_build_query($currentParams);
}

// --- Get Data from Controller ---
$products = $products ?? [];
$totalProducts = $totalProducts ?? 0;
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$itemsPerPage = $itemsPerPage ?? 9;
$availableBrands = $availableBrands ?? [];
$currentFilters = $currentFilters ?? ['search' => '', 'brand' => 'All', 'price_range' => 'all'];
$currentSort = $currentSort ?? 'created_at_desc';
$sortOptionsMap = $sortOptionsMap ?? ['created_at_desc' => 'Mặc định'];
$priceRangesMap = $priceRangesMap ?? [];
$currentPriceRangeKey = $currentPriceRangeKey ?? 'all';
$isLoggedIn = $isLoggedIn ?? false;
$wishlistedIds = $wishlistedIds ?? [];

// --- Ensure Numeric Types for Calculations ---
$currentPage = (int)$currentPage;
$itemsPerPage = (int)$itemsPerPage;
$totalProducts = (int)$totalProducts;

// --- Calculation for Item Count Display ---
$startItemNum = $totalProducts > 0 ? (($currentPage - 1) * $itemsPerPage) + 1 : 0;
$endItemNum = $totalProducts > 0 ? min($startItemNum + count($products) - 1, $totalProducts) : 0;

?>
    <style>
        /* Minimal custom CSS */
        .filter-widget .list-group-item-action.active {
            z-index: 2; color: #fff; background-color: #0d6efd; border-color: #0d6efd;
        }
        .product-card .card-img-top { height: 200px; object-fit: contain; background-color: #fff; padding: 0.5rem; }
        .product-card .card-title { min-height: 3em; /* Ensure title area has consistent height */ }
        .product-card .price { color: #dc3545; }
        .product-card .actions .btn-wishlist { color: #6c757d; border: none; }
        .product-card .actions .btn-wishlist.active { color: #dc3545; }
        .product-card .actions .btn-cart { color: #198754; border: none; }
        .product-card .actions .btn-wishlist.disabled, /* Giữ style disabled */
        .product-card .actions .btn-cart.disabled {
            opacity: 0.5;
            /* KHÔNG CẦN pointer-events: none; nữa nếu dùng Event Delegation */
        }
        .pagination .page-link { min-width: 40px; text-align: center;}
        .pagination .page-item.disabled .page-link { pointer-events: none; color: #6c757d; background-color: #e9ecef; border-color: #dee2e6;}
        .pagination .page-item.active .page-link { z-index: 3; color: #fff; background-color: #0d6efd; border-color: #0d6efd;}
    </style>

    <div class="row g-4"> <?php // Main row ?>

        <?php // ----- Sidebar ----- ?>
        <aside class="col-lg-3">
            <h3 class="mb-3">Lọc & Sắp xếp</h3>

            <?php // Search Filter - RESTORED ?>
            <div class="card shadow-sm mb-4 filter-widget">
                <div class="card-header"><h5 class="mb-0">Tìm kiếm</h5></div>
                <div class="card-body">
                    <form method="GET" action="">
                        <input type="hidden" name="page" value="shop_grid">
                        <?php // Giữ lại các bộ lọc khác khi tìm kiếm ?>
                        <input type="hidden" name="brand" value="<?= htmlspecialchars($currentFilters['brand'] ?? 'All') ?>">
                        <input type="hidden" name="price_range" value="<?= htmlspecialchars($currentPriceRangeKey) ?>">
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($currentSort) ?>">
                        <div class="input-group">
                            <input type="text" class="form-control" id="search_input" name="search" placeholder="Tìm sản phẩm..." value="<?= htmlspecialchars($currentFilters['search'] ?? '') ?>" aria-label="Tìm sản phẩm">
                            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>

            <?php // Brand Filter - RESTORED ?>
            <div class="card shadow-sm mb-4 filter-widget">
                <div class="card-header bg-light py-2">
                    <h5 class="mb-0 fs-6 fw-semibold"><i class="fas fa-tags me-1 text-primary"></i> Hãng sản xuất</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="?<?= build_query_string_sg(['brand' => 'All', 'pg' => null]) ?>"
                       class="list-group-item list-group-item-action py-2 <?= ($currentFilters['brand'] == 'All' || empty($currentFilters['brand'])) ? 'active' : '' ?>">
                        Tất cả các hãng
                    </a>
                    <?php foreach ($availableBrands as $b): ?>
                        <a href="?<?= build_query_string_sg(['brand' => $b, 'pg' => null]) ?>"
                           class="list-group-item list-group-item-action py-2 <?= ($currentFilters['brand'] == $b) ? 'active' : '' ?>">
                            <?= htmlspecialchars($b) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php // Price Range Filter - RESTORED ?>
            <div class="card shadow-sm mb-4 filter-widget">
                <div class="card-header"><h5 class="mb-0">Khoảng giá</h5></div>
                <ul class="list-group list-group-flush"> <?php // Can also use div here ?>
                    <a href="?<?= build_query_string_sg(['price_range' => 'all', 'pg' => null]) ?>"
                       class="list-group-item list-group-item-action py-2 <?= ($currentPriceRangeKey == 'all') ? 'active' : '' ?>">
                        Tất cả mức giá
                    </a>
                    <?php foreach ($priceRangesMap as $key => $rangeInfo): ?>
                        <a href="?<?= build_query_string_sg(['price_range' => $key, 'pg' => null]) ?>"
                           class="list-group-item list-group-item-action py-2 <?= ($currentPriceRangeKey == $key) ? 'active' : '' ?>">
                            <?= htmlspecialchars($rangeInfo['label'] ?? '') ?>
                        </a>
                    <?php endforeach; ?>
                </ul>
            </div>

            <?php // Sorting - RESTORED ?>
            <div class="card shadow-sm mb-4 filter-widget">
                <div class="card-header"><h5 class="mb-0">Sắp xếp theo</h5></div>
                <div class="card-body">
                    <form method="GET" action="" id="sortForm">
                        <input type="hidden" name="page" value="shop_grid">
                        <input type="hidden" name="search" value="<?= htmlspecialchars($currentFilters['search'] ?? '') ?>">
                        <input type="hidden" name="brand" value="<?= htmlspecialchars($currentFilters['brand'] ?? 'All') ?>">
                        <input type="hidden" name="price_range" value="<?= htmlspecialchars($currentPriceRangeKey) ?>">
                        <input type="hidden" name="pg" value="1"> <?php // Go to page 1 on sort change ?>

                        <select name="sort" id="sort_select" class="form-select" onchange="document.getElementById('sortForm').submit()" aria-label="Sắp xếp sản phẩm">
                            <?php foreach ($sortOptionsMap as $key => $value): ?>
                                <option value="<?= $key ?>" <?= ($currentSort == $key) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($value) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <noscript><button type="submit" class="btn btn-sm btn-secondary mt-2">Sắp xếp</button></noscript>
                    </form>
                </div>
            </div>

        </aside> <?php // End Sidebar ?>

        <?php // ----- Main Content ----- ?>
        <div class="col-lg-9">

            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
            <span class="text-muted">
                <?php if ($totalProducts > 0): ?>
                    Hiển thị <?= $startItemNum ?>–<?= $endItemNum ?> trên tổng số <?= $totalProducts ?> sản phẩm
                <?php else: ?>
                    Không tìm thấy sản phẩm nào
                <?php endif; ?>
            </span>
            </div>

            <?php if (!empty($products)): ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4"> <?php // Responsive Grid ?>
                    <?php foreach ($products as $p): ?>
                        <?php
                        $pId = (int)($p['id'] ?? 0);
                        $stock = (int)($p['stock'] ?? 0);
                        // *** WISHLIST CHECK ***
                        $isProductWishlisted = false; // Default
                        if ($isLoggedIn && is_array($wishlistedIds) && !empty($wishlistedIds)) {
                            $isProductWishlisted = in_array($pId, $wishlistedIds);
                        }
                        ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm product-card">
                                <?php // --- Product Link (Only covers image) --- ?>
                                <a href="?page=product_detail&id=<?= $pId ?>" class="text-center">
                                    <img src="/public/img/<?= htmlspecialchars($p['image'] ?? 'default.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name'] ?? '') ?>" loading="lazy">
                                </a>
                                <?php // --- END Product Link --- ?>

                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">
                                        <?php // --- Product Title Link --- ?>
                                        <a href="?page=product_detail&id=<?= $pId ?>" class="text-dark text-decoration-none">
                                            <?= htmlspecialchars($p['name'] ?? 'N/A') ?>
                                        </a>
                                        <?php // --- END Product Title Link --- ?>
                                    </h5>
                                    <p class="card-text text-muted small mb-2"><?= htmlspecialchars($p['brand'] ?? 'N/A') ?> | <?= htmlspecialchars(number_format($p['rating'] ?? 0, 1)) ?> ★</p>
                                    <p class="card-text price fw-bold fs-5 mt-auto"><?= number_format($p['price'] ?? 0, 0, ',', '.') ?>₫</p>
                                </div>
                                <div class="card-footer bg-transparent border-top-0 pb-3">
                                    <?php // --- Actions are OUTSIDE the main links --- ?>
                                    <div class="actions d-flex justify-content-between align-items-center">

                                        <?php // Wishlist Button - BỎ ONCLICK ?>
                                        <button type="button"
                                                class="btn btn-link btn-wishlist p-0 <?= $isProductWishlisted ? 'active' : '' ?> <?= !$isLoggedIn ? 'disabled' : '' ?>"
                                                data-product-id="<?= $pId ?>"
                                                data-is-wishlisted="<?= $isProductWishlisted ? '1' : '0' ?>"
                                                title="<?= !$isLoggedIn ? 'Đăng nhập để yêu thích' : ($isProductWishlisted ? 'Xóa khỏi Yêu thích' : 'Thêm vào Yêu thích') ?>"
                                        >
                                            <i class="fas fa-heart fs-4"></i> <?php // Correct Heart Icon ?>
                                        </button>

                                        <?php // Cart Link (formerly button, points to product detail) ?>
                                        <a href="?page=product_detail&id=<?= $pId ?>"
                                           class="btn btn-link btn-cart p-0 <?= $stock <= 0 ? 'disabled' : '' ?>" <?php // Keep 'disabled' class for styling ?>
                                           title="<?= $stock > 0 ? 'Xem chi tiết sản phẩm' : 'Hết hàng' ?>"
                                            <?php if($stock <= 0): ?> onclick="event.preventDefault(); alert('Sản phẩm này hiện đã hết hàng.');" <?php endif; ?> <?php // Prevent click if disabled ?>
                                        >
                                            <i class="fas fa-cart-plus fs-4"></i> <?php // Cart Icon ?>
                                        </a>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php // --- Pagination --- ?>
                <nav aria-label="Product navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($totalPages > 1):
                            $currentPageInt = (int)$currentPage; $totalPagesInt = (int)$totalPages;
                            $prevDisabled = ($currentPageInt <= 1) ? 'disabled' : '';
                            $nextDisabled = ($currentPageInt >= $totalPagesInt) ? 'disabled' : '';
                            ?>
                            <li class="page-item <?= $prevDisabled ?>"> <a class="page-link" href="?<?= build_query_string_sg(['pg' => $currentPageInt - 1]) ?>">«</a> </li>
                            <?php
                            $maxPagesToShow = 5; $halfMax = floor($maxPagesToShow / 2);
                            $startPage = max(1, $currentPageInt - $halfMax); $endPage = min($totalPagesInt, $startPage + $maxPagesToShow - 1);
                            if ($endPage - $startPage + 1 < $maxPagesToShow) { $startPage = max(1, $endPage - $maxPagesToShow + 1); }
                            if ($startPage > 1) { echo '<li class="page-item"><a class="page-link" href="?'.build_query_string_sg(['pg' => 1]).'">1</a></li>'; if ($startPage > 2) { echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; } }
                            for ($i = $startPage; $i <= $endPage; $i++) { echo '<li class="page-item '.($i == $currentPageInt ? 'active' : '').'"><a class="page-link" href="?'.build_query_string_sg(['pg' => $i]).'">'.$i.'</a></li>'; }
                            if ($endPage < $totalPagesInt) { if ($endPage < $totalPagesInt - 1) { echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; } echo '<li class="page-item"><a class="page-link" href="?'.build_query_string_sg(['pg' => $totalPagesInt]).'">'.$totalPagesInt.'</a></li>'; }
                            ?>
                            <li class="page-item <?= $nextDisabled ?>"> <a class="page-link" href="?<?= build_query_string_sg(['pg' => $currentPageInt + 1]) ?>">»</a> </li>
                        <?php endif; ?>
                    </ul>
                </nav>

            <?php endif; ?>

        </div> <?php // End Main Content Column ?>
    </div> <?php // End Main Row ?>

<?php // JavaScript for Wishlist Toggle (ĐÃ SỬA - Chỉ chứa logic AJAX) ?>
    <script>
        // --- Wishlist Toggle Function (Chỉ chứa logic AJAX) ---
        async function toggleWishlist(buttonElement, productId) {
            // Hàm này giờ chỉ chạy khi người dùng ĐÃ ĐĂNG NHẬP (được gọi bởi event listener)
            console.log("AJAX toggleWishlist called for button:", buttonElement, "productId:", productId);

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
                        // Các lỗi khác từ server (không phải login_required)
                        alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                    }
                } else {
                    // Xử lý trường hợp không phải JSON
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
        // No addToCart JS needed here now
    </script>

<?php
include_once __DIR__ . '/../layout/footer.php'; // Footer sẽ chứa event listener mới
?>