<?php
// Web/app/Views/shop_grid.php
$pageTitle = 'Cửa hàng';
include_once __DIR__ . '/../layout/header.php';

// --- Lấy Data từ Controller (bao gồm availableSpecs, currentFilters, etc.) ---
$products = $products ?? []; // Dữ liệu sản phẩm ban đầu
$totalProducts = $totalProducts ?? 0;
$itemsPerPage = $itemsPerPage ?? 9;
$availableBrands = $availableBrands ?? [];
$availableSpecs = $availableSpecs ?? [];
$currentFilters = $currentFilters ?? [];
// Đảm bảo currentFilters có giá trị mặc định cho specs
$defaultSpecFilters = ['ram' => 'all', 'cpu' => 'all', 'screen_size' => 'all', 'storage' => 'all', 'os' => 'all', 'battery_capacity' => 'all', 'screen_tech' => 'all'];
$currentFilters = array_merge($defaultSpecFilters, $currentFilters);

$currentSort = $currentSort ?? 'created_at_desc';
$sortOptionsMap = $sortOptionsMap ?? ['created_at_desc' => 'Mặc định'];
$priceRangesMap = $priceRangesMap ?? [];
$currentPriceRangeKey = $currentFilters['price_range'] ?? 'all';

// Dữ liệu cho partial views ban đầu
$isLoggedIn = $isLoggedIn ?? false; // Lấy từ BaseController qua $this->render
$wishlistedIds = $wishlistedIds ?? []; // Lấy từ BaseController qua $this->render

$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;

// Tính toán số item hiển thị ban đầu
$currentPage = (int)$currentPage;
$itemsPerPage = (int)$itemsPerPage;
$totalProducts = (int)$totalProducts;
$startItemNum = $totalProducts > 0 ? (($currentPage - 1) * $itemsPerPage) + 1 : 0;
$endItemNum = $totalProducts > 0 ? min($startItemNum + count($products) - 1, $totalProducts) : 0;

// --- Hàm trợ giúp để hiển thị tên bộ lọc thân thiện ---
function getSpecFilterLabel(string $specKey): string {
    $labels = [ 'ram' => 'RAM', 'cpu' => 'CPU', 'screen_size' => 'Kích thước màn hình', 'storage' => 'Bộ nhớ trong', 'os' => 'Hệ điều hành', 'battery_capacity' => 'Dung lượng pin', 'screen_tech' => 'Công nghệ màn hình'];
    return $labels[$specKey] ?? ucfirst(str_replace('_', ' ', $specKey));
}

// --- Hàm build query string cũ (có thể không cần nữa nếu JS xử lý hết) ---
// function build_query_string_sg(...) { ... } // Giữ lại nếu có link nào đó vẫn cần
?>
<link rel="stylesheet" href="/webfinal/public/css/shop_grid.css">

<div class="row g-4"> <?php // Main row ?>

    <?php // ----- Sidebar ----- ?>
    <aside class="col-lg-3" id="shop-sidebar"> <?php // Thêm ID cho sidebar ?>
        <h3 class="mb-3">Lọc & Sắp xếp</h3>

        <?php // Search Filter ?>
        <div class="card shadow-sm mb-4 filter-widget">
            <div class="card-header"><h5 class="mb-0">Tìm kiếm</h5></div>
            <div class="card-body">
                <?php // Form này sẽ được JS xử lý submit ?>
                <form id="search-form">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" id="search_input" placeholder="Tìm sản phẩm..." value="<?= htmlspecialchars($currentFilters['search']) ?>" aria-label="Tìm sản phẩm">
                        <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </form>
            </div>
        </div>

        <?php // Brand Filter ?>
         <div class="card shadow-sm mb-4 filter-widget">
            <div class="card-header bg-light py-2"><h5 class="mb-0 fs-6 fw-semibold"><i class="fas fa-tags me-1 text-primary"></i> Hãng</h5></div>
             <div class="list-group list-group-flush filter-options" data-filter-key="brand"> <?php // Thêm data-filter-key ?>
                <a href="#" data-value="All" class="list-group-item list-group-item-action py-2 filter-link <?= ($currentFilters['brand'] == 'All') ? 'active' : '' ?>">Tất cả</a>
                <?php foreach ($availableBrands as $b): ?>
                    <a href="#" data-value="<?= htmlspecialchars($b) ?>" class="list-group-item list-group-item-action py-2 filter-link <?= ($currentFilters['brand'] == $b) ? 'active' : '' ?>"><?= htmlspecialchars($b) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php // Price Range Filter ?>
         <div class="card shadow-sm mb-4 filter-widget">
            <div class="card-header"><h5 class="mb-0">Khoảng giá</h5></div>
            <div class="list-group list-group-flush filter-options" data-filter-key="price_range"> <?php // Thêm data-filter-key ?>
                <a href="#" data-value="all" class="list-group-item list-group-item-action py-2 filter-link <?= ($currentPriceRangeKey == 'all') ? 'active' : '' ?>">Tất cả</a>
                <?php foreach ($priceRangesMap as $key => $rangeInfo): ?>
                    <a href="#" data-value="<?= htmlspecialchars($key) ?>" class="list-group-item list-group-item-action py-2 filter-link <?= ($currentPriceRangeKey == $key) ? 'active' : '' ?>"><?= htmlspecialchars($rangeInfo['label'] ?? '') ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php // ----- Specs Filters (Accordion) ----- ?>
        <div class="accordion shadow-sm mb-4 filter-widget" id="specsAccordion">
            <?php $specIndex = 0; ?>
            <?php foreach ($availableSpecs as $specKey => $options): ?>
                <?php if (!empty($options)): ?>
                    <?php
                    $specIndex++;
                    $isCurrentlyFiltered = isset($currentFilters[$specKey]) && $currentFilters[$specKey] !== 'all';
                    ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= $specIndex ?>">
                            <button class="accordion-button <?= !$isCurrentlyFiltered ? 'collapsed' : '' ?> py-2 fs-6 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $specIndex ?>" aria-expanded="<?= $isCurrentlyFiltered ? 'true' : 'false' ?>" aria-controls="collapse<?= $specIndex ?>">
                                <?= getSpecFilterLabel($specKey) ?> <?= $isCurrentlyFiltered ? '<span class="badge bg-primary ms-2 rounded-pill small">Lọc</span>' : '' ?>
                            </button>
                        </h2>
                        <div id="collapse<?= $specIndex ?>" class="accordion-collapse collapse <?= $isCurrentlyFiltered ? 'show' : '' ?>" aria-labelledby="heading<?= $specIndex ?>" data-bs-parent="#specsAccordion">
                            <div class="list-group list-group-flush filter-options" data-filter-key="<?= $specKey ?>">
                                 <a href="#" data-value="all" class="list-group-item list-group-item-action py-2 filter-link <?= ($currentFilters[$specKey] === 'all') ? 'active' : '' ?>">
                                     Tất cả <?= getSpecFilterLabel($specKey) ?>
                                 </a>
                                 <?php foreach ($options as $option): ?>
                                     <a href="#" data-value="<?= htmlspecialchars($option) ?>" class="list-group-item list-group-item-action py-2 filter-link <?= ($currentFilters[$specKey] == $option) ? 'active' : '' ?>">
                                         <?= htmlspecialchars($option) ?>
                                     </a>
                                 <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php // ----- KẾT THÚC ACCORDION ----- ?>

        <?php // Sorting Filter ?>
        <div class="card shadow-sm mb-4 filter-widget">
            <div class="card-header"><h5 class="mb-0">Sắp xếp theo</h5></div>
            <div class="card-body">
                <select name="sort" id="sort_select" class="form-select form-select-sm"> <?php // Bỏ name nếu JS xử lý ?>
                     <?php foreach ($sortOptionsMap as $key => $value): ?>
                         <option value="<?= $key ?>" <?= ($currentSort == $key) ? 'selected' : '' ?>>
                             <?= htmlspecialchars($value) ?>
                         </option>
                     <?php endforeach; ?>
                </select>
            </div>
        </div>

    </aside> <?php // End Sidebar ?>

    <?php // ----- Main Content ----- ?>
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
            <span class="text-muted small" id="product-count-display">
                <?php if ($totalProducts > 0): ?>
                    Hiển thị <?= $startItemNum ?>–<?= $endItemNum ?> / <?= $totalProducts ?> sản phẩm
                <?php else: ?>
                    Không tìm thấy sản phẩm nào.
                <?php endif; ?>
            </span>
             <div id="loading-indicator" class="spinner-border spinner-border-sm text-primary" role="status" style="display: none;">
                <span class="visually-hidden">Đang tải...</span>
            </div>
        </div>

        <?php // Container cho Grid Sản phẩm (JS sẽ cập nhật nội dung ở đây) ?>
        <div id="product-grid-container">
            <?php
            // Include partial view cho lần tải đầu tiên
            extract(['products' => $products, 'isLoggedIn' => $isLoggedIn, 'wishlistedIds' => $wishlistedIds]); // Truyền biến
            include BASE_PATH . '/app/Views/partials/product_grid_items.php';
            ?>
        </div>

        <?php // Container cho Pagination (JS sẽ cập nhật nội dung ở đây) ?>
        <div id="pagination-container">
             <?php
             // Include partial view cho lần tải đầu tiên
             extract(['currentPage' => $currentPage, 'totalPages' => $totalPages]); // Truyền biến
             include BASE_PATH . '/app/Views/partials/pagination.php';
             ?>
        </div>

    </div> <?php // End Main Content Column ?>

</div> <?php // End Main Row ?>

<?php // Link tới file JS mới ?>
<script src="/webfinal/public/js/shop_grid_ajax.js"></script>

<?php
// Footer vẫn có thể chứa JS cho Wishlist nếu dùng event delegation
include_once __DIR__ . '/../layout/footer.php';
?>