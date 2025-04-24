<?php
// Web/app/Views/shop_grid.php

// --- START: Include Header ---
$pageTitle = 'Cửa hàng'; // Set a specific title for this page
include_once __DIR__ . '/../layout/header.php';
// --- END: Include Header ---


// Helper function to build query string (keep as is)
function build_query_string(array $params): string {
    // ... (giữ nguyên hàm này) ...
    $currentParams = $_GET;
    foreach ($params as $key => $value) {
        if ($value === null || $value === '' ||
            ($key === 'price_range' && $value === 'all') ||
            ($key === 'brand' && $value === 'All') ||
            ($key === 'sort' && $value === 'created_at_desc')) {
            unset($currentParams[$key]);
        } else {
            $currentParams[$key] = $value;
        }
    }
    $currentParams['page'] = 'shop_grid';
    if (isset($currentParams['pg']) && ($currentParams['pg'] == 1 || $currentParams['pg'] === null || $currentParams['pg'] === '')) {
        unset($currentParams['pg']);
    }
    unset($currentParams['min_price']);
    unset($currentParams['max_price']);
    return http_build_query($currentParams);
}

// Lấy các biến đã được truyền từ Controller (keep as is)
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

// *** ADDED: Wishlist data needed by header/footer or product grid actions ***
$isLoggedIn = $isLoggedIn ?? isset($_SESSION['user_id']);
$wishlistedIds = $wishlistedIds ?? [];
// *** END ADDED ***

?>
    <style>
        /* --- Style section (keep as is or modify as needed) --- */
        .shop-content { display: flex; gap: 20px; flex-wrap: wrap; /* Allow wrapping on smaller screens */ }
        .sidebar { width: 100%; /* Full width on small screens */ margin-bottom: 20px; flex-shrink: 0; }
        .main-content { flex: 1; min-width: 0; width: 100%; /* Full width initially */}

        /* Apply sidebar width only on larger screens */
        @media (min-width: 768px) {
            .sidebar { width: 250px; margin-bottom: 0; }
            .main-content { width: auto; } /* Allow it to take remaining space */
        }

        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; } /* Adjusted minmax */
        .product-item { border: 1px solid #dee2e6; border-radius: 5px; overflow: hidden; background-color: #fff; transition: box-shadow 0.3s ease; text-align: center; padding-bottom: 15px;}
        .product-item:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .product-item img { width: 100%; height: 180px; object-fit: contain; background-color: #f8f9fa; margin-bottom: 10px;} /* Adjusted height */
        .product-item .product-info { padding: 0 15px; }
        .product-item h5 { font-size: 1em; margin: 10px 0 5px 0; height: 2.8em; overflow: hidden; /* Fixed height & hide overflow */ display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .product-item h5 a { color: #343a40; text-decoration: none; }
        .product-item .price { font-weight: bold; color: #dc3545; margin-bottom: 10px; }
        .product-item .brand-rating { font-size: 0.9em; color: #6c757d; margin-bottom: 10px; }
        .product-item .actions { margin-top: 10px; display: flex; justify-content: center; gap: 15px; align-items: center;} /* Actions like wishlist/cart */
        .product-item .actions a { font-size: 1.3em; text-decoration: none; }
        .product-item .actions a.wishlist-btn { color: #adb5bd; }
        .product-item .actions a.wishlist-btn.active { color: red; }
        .product-item .actions a.cart-btn { color: #28a745; }
        .product-item .actions span.cart-btn { color: #6c757d; font-size: 1.3em; cursor: not-allowed;}


        .pagination { margin-top: 30px; text-align: center; }
        .pagination a, .pagination strong, .pagination span { margin: 0 3px; text-decoration: none; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; display: inline-block; /* Needed for padding */ min-width: 40px; /* Ensure consistent width */ text-align: center; box-sizing: border-box;}
        .pagination strong { font-weight: bold; background-color: #007bff; color: white; border-color: #007bff;}
        .pagination a:hover { background-color: #f0f0f0; }
        .pagination span { color: #ccc; background-color: #f8f9fa; } /* Style for disabled links/ellipsis */

        .filter-group { margin-bottom: 25px; }
        .filter-group h4 { margin-bottom: 10px; font-size: 1.2em; border-bottom: 1px solid #eee; padding-bottom: 8px;}
        .filter-group ul { list-style: none; padding: 0; margin: 0; }
        .filter-group li { margin-bottom: 8px; }
        .filter-group li a { text-decoration: none; color: #007bff; display: block; padding: 3px 0; }
        .filter-group li a:hover { text-decoration: underline; }
        .filter-group li a.active { font-weight: bold; color: #dc3545; } /* Highlight active filter */
        .sidebar label, .sidebar input[type="text"], .sidebar select, .sidebar button { display: block; margin-bottom: 8px; width: 100%; box-sizing: border-box; padding: 8px; font-size: 0.95em; border-radius: 4px; border: 1px solid #ced4da; }
        .sidebar select { width: 100%; }
        .sidebar button { width: auto; cursor: pointer; background-color: #007bff; color: white; border: none; padding: 8px 15px; }
        .sidebar button:hover { background-color: #0056b3; }
        .sidebar form { margin: 0; }
        .results-info { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; font-size: 0.95em; color: #6c757d;}
        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0; } /* For screen readers */
    </style>

    <div class="shop-content"> <?php // Added a wrapper div ?>

        <aside class="sidebar"> <?php // Changed div class to aside ?>
            <h3>Lọc & Sắp xếp</h3>

            <div class="filter-group">
                <h4>Tìm kiếm</h4>
                <form method="GET" action="">
                    <input type="hidden" name="page" value="shop_grid">
                    <?php // Keep other filters when searching ?>
                    <input type="hidden" name="brand" value="<?= htmlspecialchars($currentFilters['brand']) ?>">
                    <input type="hidden" name="price_range" value="<?= htmlspecialchars($currentPriceRangeKey) ?>">
                    <input type="hidden" name="sort" value="<?= htmlspecialchars($currentSort) ?>">
                    <label for="search_input" class="sr-only">Tìm sản phẩm</label>
                    <input type="text" id="search_input" name="search" placeholder="Tìm sản phẩm..." value="<?= htmlspecialchars($currentFilters['search']) ?>">
                    <button type="submit">Tìm</button>
                </form>
            </div>

            <div class="filter-group">
                <h4>Hãng sản xuất</h4>
                <ul>
                    <li>
                        <a href="?<?= build_query_string(['brand' => 'All', 'pg' => null]) ?>"
                           class="<?= ($currentFilters['brand'] == 'All' || empty($currentFilters['brand'])) ? 'active' : '' ?>">
                            Tất cả các hãng
                        </a>
                    </li>
                    <?php foreach ($availableBrands as $b): ?>
                        <li>
                            <a href="?<?= build_query_string(['brand' => $b, 'pg' => null]) ?>"
                               class="<?= ($currentFilters['brand'] == $b) ? 'active' : '' ?>">
                                <?= htmlspecialchars($b) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="filter-group">
                <h4>Khoảng giá</h4>
                <ul>
                    <li>
                        <a href="?<?= build_query_string(['price_range' => 'all', 'pg' => null]) ?>"
                           class="<?= ($currentPriceRangeKey == 'all') ? 'active' : '' ?>">
                            Tất cả mức giá
                        </a>
                    </li>
                    <?php foreach ($priceRangesMap as $key => $rangeInfo): ?>
                        <li>
                            <a href="?<?= build_query_string(['price_range' => $key, 'pg' => null]) ?>"
                               class="<?= ($currentPriceRangeKey == $key) ? 'active' : '' ?>">
                                <?= htmlspecialchars($rangeInfo['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="filter-group">
                <h4>Sắp xếp theo</h4>
                <form method="GET" action="" id="sortForm">
                    <input type="hidden" name="page" value="shop_grid">
                    <?php // Keep other filters when sorting ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($currentFilters['search']) ?>">
                    <input type="hidden" name="brand" value="<?= htmlspecialchars($currentFilters['brand']) ?>">
                    <input type="hidden" name="price_range" value="<?= htmlspecialchars($currentPriceRangeKey) ?>">
                    <?php // Reset to page 1 when changing sort ?>
                    <input type="hidden" name="pg" value="1"> <?php // Go to page 1 on sort change ?>

                    <label for="sort_select" class="sr-only">Sắp xếp theo</label> <?php // Screen reader label ?>
                    <select name="sort" id="sort_select" onchange="document.getElementById('sortForm').submit()">
                        <?php foreach ($sortOptionsMap as $key => $value): ?>
                            <option value="<?= $key ?>" <?= ($currentSort == $key) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($value) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <noscript><button type="submit">Sắp xếp</button></noscript> <?php // Fallback for no JS ?>
                </form>
            </div>

        </aside>

        <div class="main-content">
            <?php // Removed <h1> as it's likely in the header or handled differently now ?>

            <div class="results-info">
                Hiển thị <?= count($products) ?> trong số <strong><?= $totalProducts ?></strong> sản phẩm tìm thấy.
            </div>

            <?php if (!empty($products)): ?>
                <div class="products-grid">
                    <?php foreach ($products as $p): ?>
                        <?php
                        $pId = $p['id'];
                        $isProductWishlisted = $isLoggedIn && is_array($wishlistedIds) && in_array($pId, $wishlistedIds); // Ensure wishlistedIds is array
                        ?>
                        <div class="product-item">
                            <a href="?page=product_detail&id=<?= $pId ?>">
                                <img src="/public/img/<?= htmlspecialchars($p['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                            </a>
                            <div class="product-info">
                                <h5><a href="?page=product_detail&id=<?= $pId ?>"><?= htmlspecialchars($p['name']) ?></a></h5>
                                <div class="price"><?= number_format($p['price'], 0, ',', '.') ?>₫</div>
                                <div class="brand-rating">
                                    <?= htmlspecialchars($p['brand']) ?> | <?= htmlspecialchars(number_format($p['rating'] ?? 0, 1)) ?> ★
                                </div>
                                <div class="actions">
                                    <?php // Wishlist Action ?>
                                    <div>
                                        <?php if ($isLoggedIn): ?>
                                            <?php if ($isProductWishlisted): ?>
                                                <a href="?page=wishlist_remove&id=<?= $pId ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" title="Xóa khỏi Yêu thích" class="wishlist-btn active">❤️</a>
                                            <?php else: ?>
                                                <a href="?page=wishlist_add&id=<?= $pId ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" title="Thêm vào Yêu thích" class="wishlist-btn">♡</a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php $redirectUrlGrid = urlencode('?page=shop_grid' . (isset($_SERVER['QUERY_STRING']) ? '&'.$_SERVER['QUERY_STRING'] : '' )); ?>
                                            <a href="?page=login&redirect=<?= $redirectUrlGrid ?>" title="Đăng nhập để yêu thích" class="wishlist-btn">♡</a>
                                        <?php endif; ?>
                                    </div>
                                    <?php // Cart Action ?>
                                    <div>
                                        <?php if ($p['stock'] > 0): ?>
                                            <a href="?page=cart_add&id=<?= $pId ?>&quantity=1" title="Thêm vào giỏ" class="cart-btn">🛒</a>
                                        <?php else: ?>
                                            <span title="Hết hàng" class="cart-btn" style="cursor: not-allowed; opacity: 0.5;">🛒</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="padding: 20px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; text-align: center;">
                    Không tìm thấy sản phẩm nào phù hợp. Vui lòng thử điều chỉnh bộ lọc.
                </div>
            <?php endif; ?>

            <div class="pagination">
                <?php if ($totalPages > 1): ?>
                    <?php
                    // --- Consistently use integer variables for calculations ---
                    $currentPageInt = (int)$currentPage;
                    $totalPagesInt = (int)$totalPages;
                    ?>

                    <?php // Previous Page Link ?>
                    <?php if ($currentPageInt > 1): ?>
                        <a href="?<?= build_query_string(['pg' => $currentPageInt - 1]) ?>" aria-label="Trang trước">&laquo; Trước</a>
                    <?php else: ?>
                        <span aria-disabled="true">&laquo; Trước</span>
                    <?php endif; ?>

                    <?php // Page Number Links - Using fixed calculation logic ?>
                    <?php
                    $maxPagesToShow = 5; // Max number of direct page links to show
                    $maxPagesToShowInt = (int)$maxPagesToShow;

                    // Calculate the start and end page numbers to display
                    $halfMax = (int)floor($maxPagesToShowInt / 2); // Ensure halfMax is integer
                    $startPage = max(1, $currentPageInt - $halfMax);
                    $endPage = min($totalPagesInt, $currentPageInt + $halfMax);

                    // Adjust if we are near the beginning or end to maintain maxPagesToShow
                    if ($endPage - $startPage + 1 < $maxPagesToShowInt) {
                        if ($currentPageInt <= $halfMax) { // near the beginning
                            $endPage = min($totalPagesInt, $startPage + $maxPagesToShowInt - 1);
                        } elseif ($currentPageInt >= $totalPagesInt - $halfMax) { // near the end
                            $startPage = max(1, $endPage - $maxPagesToShowInt + 1);
                        }
                    }
                    // Final check for edge case where totalPages < maxPagesToShow
                    if ($totalPagesInt <= $maxPagesToShowInt) {
                        $startPage = 1;
                        $endPage = $totalPagesInt;
                    }

                    // Show first page link and ellipsis if needed
                    if ($startPage > 1) {
                        echo '<a href="?' . build_query_string(['pg' => 1]) . '">1</a>';
                        if ($startPage > 2) {
                            echo '<span>...</span>'; // Ellipsis
                        }
                    }

                    // Show page numbers in the calculated range
                    for ($i = $startPage; $i <= $endPage; $i++) {
                        if ($i == $currentPageInt) {
                            echo '<strong>' . $i . '</strong>';
                        } else {
                            echo '<a href="?' . build_query_string(['pg' => $i]) . '">' . $i . '</a>';
                        }
                    }

                    // Show last page link and ellipsis if needed
                    if ($endPage < $totalPagesInt) {
                        if ($endPage < $totalPagesInt - 1) {
                            echo '<span>...</span>'; // Ellipsis
                        }
                        echo '<a href="?' . build_query_string(['pg' => $totalPagesInt]) . '">' . $totalPagesInt . '</a>';
                    }
                    ?>

                    <?php // Next Page Link ?>
                    <?php if ($currentPageInt < $totalPagesInt): ?>
                        <a href="?<?= build_query_string(['pg' => $currentPageInt + 1]) ?>" aria-label="Trang sau">Sau &raquo;</a>
                    <?php else: ?>
                        <span aria-disabled="true">Sau &raquo;</span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

        </div>
    </div> <?php // End shop-content ?>

<?php
// --- START: Include Footer ---
include_once __DIR__ . '/../layout/footer.php';
// --- END: Include Footer ---
?>