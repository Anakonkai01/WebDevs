<?php
// Web/app/Views/shop_grid.php
$pageTitle = 'Cửa hàng';
include_once __DIR__ . '/layout/header.php'; // Header includes Bootstrap CSS/JS & global data

// --- Get Data from Controller ---
// Use null coalescing operator for safety
$products = $products ?? [];
$totalProducts = $totalProducts ?? 0;
$itemsPerPage = $itemsPerPage ?? 9;
$availableBrands = $availableBrands ?? [];
$availableSpecs = $availableSpecs ?? [];
$currentFilters = $currentFilters ?? []; // This holds UI filter state (search, brand, price_range, specs)
$currentSort = $currentSort ?? 'created_at_desc';
$sortOptionsMap = $sortOptionsMap ?? ['created_at_desc' => 'Mặc định'];
$priceRangesMap = $priceRangesMap ?? [];
// Use $currentFilters['price_range'] directly, $currentPriceRangeKey is redundant
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$startItemNum = $startItemNum ?? 0;
$endItemNum = $endItemNum ?? 0;
$isLoggedIn = $isLoggedIn ?? false;
$wishlistedIds = $wishlistedIds ?? [];

// --- Helper Function for Spec Labels ---
function getSpecFilterLabel(string $specKey): string {
    $labels = [
        'ram' => 'RAM',
        'cpu' => 'CPU',
        'screen_size' => 'Kích thước màn hình',
        'storage' => 'Bộ nhớ trong',
        'os' => 'Hệ điều hành',
        'battery_capacity' => 'Dung lượng pin',
        'screen_tech' => 'Công nghệ màn hình'
    ];
    return $labels[$specKey] ?? ucfirst(str_replace('_', ' ', $specKey));
}
?>
<link rel="stylesheet" href="/webfinal/public/css/shop_grid.css"> <div class="row g-4"> <?php // Main row with gutters ?>

    <?php // ----- Sidebar Column ----- ?>
    <aside class="col-lg-3" id="shop-sidebar">
        <form id="filter-sort-form"> <?php // Wrap all filters/sort in one form for easier JS access ?>
            <h3 class="mb-3 fs-5 fw-semibold">Bộ lọc</h3>

            <?php // Search Filter ?>
            <div class="card shadow-sm mb-3 filter-widget">
                <div class="card-header bg-light py-2"><h5 class="mb-0 fs-6 fw-semibold">Tìm kiếm</h5></div>
                <div class="card-body py-2 px-3">
                    <div class="input-group input-group-sm">
                        <input type="search" class="form-control" name="search" id="search_input" placeholder="Tìm sản phẩm..." value="<?= htmlspecialchars($currentFilters['search'] ?? '') ?>" aria-label="Tìm sản phẩm">
                        <button class="btn btn-outline-secondary" type="submit" id="search-submit-button" aria-label="Tìm"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </div>

            <?php // Brand Filter ?>
            <div class="card shadow-sm mb-3 filter-widget">
                <div class="card-header bg-light py-2"><h5 class="mb-0 fs-6 fw-semibold">Hãng</h5></div>
                <div class="list-group list-group-flush filter-options" data-filter-key="brand">
                    <a href="#" data-value="All" class="list-group-item list-group-item-action py-2 filter-link <?= (!isset($currentFilters['brand']) || $currentFilters['brand'] == 'All') ? 'active' : '' ?>">Tất cả</a>
                    <?php if (!empty($availableBrands)): ?>
                        <?php foreach ($availableBrands as $b): ?>
                            <?php $brandValue = htmlspecialchars($b); ?>
                            <a href="#" data-value="<?= $brandValue ?>" class="list-group-item list-group-item-action py-2 filter-link <?= (isset($currentFilters['brand']) && $currentFilters['brand'] == $b) ? 'active' : '' ?>"><?= $brandValue ?></a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php // Price Range Filter ?>
            <div class="card shadow-sm mb-3 filter-widget">
                <div class="card-header bg-light py-2"><h5 class="mb-0 fs-6 fw-semibold">Khoảng giá</h5></div>
                <div class="list-group list-group-flush filter-options" data-filter-key="price_range">
                    <?php foreach ($priceRangesMap as $key => $rangeInfo): ?>
                         <?php $rangeValue = htmlspecialchars($key); ?>
                         <a href="#" data-value="<?= $rangeValue ?>" class="list-group-item list-group-item-action py-2 filter-link <?= (isset($currentFilters['price_range']) && $currentFilters['price_range'] == $key) ? 'active' : '' ?>"><?= htmlspecialchars($rangeInfo['label'] ?? '') ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php // ----- Specs Filters (Accordion) ----- ?>
            <?php if (!empty($availableSpecs)): ?>
            <div class="accordion shadow-sm mb-3 filter-widget" id="specsAccordion">
                <?php $specIndex = 0; ?>
                <?php foreach ($availableSpecs as $specKey => $options): ?>
                    <?php
                        // Skip if no options for this spec
                        if (empty($options)) continue;
                        $specIndex++;
                        $specKeySafe = htmlspecialchars($specKey);
                        $isCurrentlyFiltered = isset($currentFilters[$specKey]) && $currentFilters[$specKey] !== 'all';
                    ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= $specIndex ?>">
                            <button class="accordion-button <?= !$isCurrentlyFiltered ? 'collapsed' : '' ?> py-2 fs-6 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $specIndex ?>" aria-expanded="<?= $isCurrentlyFiltered ? 'true' : 'false' ?>" aria-controls="collapse<?= $specIndex ?>">
                                <?= getSpecFilterLabel($specKey) ?> <?= $isCurrentlyFiltered ? '<span class="badge bg-primary ms-2 rounded-pill small">Lọc</span>' : '' ?>
                            </button>
                        </h2>
                        <div id="collapse<?= $specIndex ?>" class="accordion-collapse collapse <?= $isCurrentlyFiltered ? 'show' : '' ?>" aria-labelledby="heading<?= $specIndex ?>" data-bs-parent="#specsAccordion">
                            <div class="list-group list-group-flush filter-options" data-filter-key="<?= $specKeySafe ?>">
                                 <a href="#" data-value="all" class="list-group-item list-group-item-action py-2 filter-link <?= (!isset($currentFilters[$specKey]) || $currentFilters[$specKey] === 'all') ? 'active' : '' ?>">
                                     Tất cả <?= getSpecFilterLabel($specKey) ?>
                                 </a>
                                 <?php foreach ($options as $option): ?>
                                      <?php $optionValue = htmlspecialchars($option); ?>
                                     <a href="#" data-value="<?= $optionValue ?>" class="list-group-item list-group-item-action py-2 filter-link <?= (isset($currentFilters[$specKey]) && $currentFilters[$specKey] == $option) ? 'active' : '' ?>">
                                         <?= $optionValue ?>
                                     </a>
                                 <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php // Sorting Filter ?>
            <div class="card shadow-sm mb-3 filter-widget">
                <div class="card-header bg-light py-2"><h5 class="mb-0 fs-6 fw-semibold">Sắp xếp theo</h5></div>
                <div class="card-body py-2 px-3">
                    <select name="sort" id="sort_select" class="form-select form-select-sm">
                         <?php foreach ($sortOptionsMap as $key => $value): ?>
                              <?php $sortKey = htmlspecialchars($key); ?>
                             <option value="<?= $sortKey ?>" <?= ($currentSort == $key) ? 'selected' : '' ?>>
                                 <?= htmlspecialchars($value) ?>
                             </option>
                         <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form> <?php // End filter/sort form ?>
    </aside> <?php // End Sidebar Column ?>

    <?php // ----- Main Content Column ----- ?>
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
            <span class="text-muted small" id="product-count-display">
                 <?php // Initial count text generated by PHP ?>
                 <?= ($totalProducts > 0) ? "Hiển thị {$startItemNum}–{$endItemNum} / {$totalProducts} sản phẩm" : "Không tìm thấy sản phẩm nào." ?>
            </span>
             <?php // Loading indicator - controlled by JS ?>
             <div id="loading-indicator" class="spinner-border spinner-border-sm text-primary" role="status" style="display: none;">
                <span class="visually-hidden">Đang tải...</span>
            </div>
        </div>

        <?php // Container cho Grid Sản phẩm - Updated by AJAX ?>
        <div id="product-grid-container" style="min-height: 200px;"> <?php // Add min-height to prevent collapse ?>
            <?php
            // Include partial view for initial page load
            $viewDataGrid = [
                'products' => $products,
                'isLoggedIn' => $isLoggedIn,
                'wishlistedIds' => $wishlistedIds
            ];
            // extract($viewDataGrid); // No need to extract again if already done by render()
            include BASE_PATH . '/app/Views/partials/product_grid_items.php';
            ?>
        </div>

        <?php // Container cho Pagination - Updated by AJAX ?>
        <div id="pagination-container" class="mt-4">
             <?php
             // Include partial view for initial page load
             $viewDataPagination = [
                 'currentPage' => $currentPage,
                 'totalPages' => $totalPages
             ];
            // extract($viewDataPagination); // No need to extract again
             include BASE_PATH . '/app/Views/partials/pagination.php';
             ?>
        </div>
    </div> <?php // End Main Content Column ?>
</div> <?php // End Main Row ?>

<?php
// Footer includes Bootstrap JS, the shared wishlist listener, and the conditional include for shop_grid_ajax.js
include_once __DIR__ . '/layout/footer.php';
?>