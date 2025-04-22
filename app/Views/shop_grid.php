<?php
// Web/app/Views/shop_grid.php

// Helper function để tạo link phân trang/filter/sort giữ lại các tham số hiện tại
function build_query_string(array $params): string {
    $currentParams = $_GET; // Lấy tất cả tham số GET hiện tại

    // Ghi đè hoặc thêm tham số mới từ $params vào $currentParams
    foreach ($params as $key => $value) {
        // Xóa tham số khỏi $currentParams nếu giá trị mới là null/rỗng
        // Hoặc nếu là các giá trị mặc định ('all', 'All', 'created_at_desc')
        if ($value === null || $value === '' ||
            ($key === 'price_range' && $value === 'all') ||
            ($key === 'brand' && $value === 'All') ||
            ($key === 'sort' && $value === 'created_at_desc')) {
            unset($currentParams[$key]);
        } else {
            $currentParams[$key] = $value;
        }
    }

    // Luôn giữ 'page=shop_grid' làm định danh trang
    $currentParams['page'] = 'shop_grid';

    // Xóa tham số phân trang 'pg' nếu giá trị là 1 (trang đầu tiên)
    if (isset($currentParams['pg']) && ($currentParams['pg'] == 1 || $currentParams['pg'] === null || $currentParams['pg'] === '')) {
        unset($currentParams['pg']);
    }

    // Xóa các tham số giá cũ (min_price, max_price) nếu chúng vô tình còn sót lại
    unset($currentParams['min_price']);
    unset($currentParams['max_price']);

    // Tạo chuỗi query string từ mảng tham số đã xử lý
    return http_build_query($currentParams);
}

// Lấy các biến đã được truyền từ Controller
// Đặt giá trị mặc định để tránh lỗi nếu biến không tồn tại
$products = $products ?? [];
$totalProducts = $totalProducts ?? 0;
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$itemsPerPage = $itemsPerPage ?? 9;
$availableBrands = $availableBrands ?? [];
$currentFilters = $currentFilters ?? ['search' => '', 'brand' => 'All', 'price_range' => 'all'];
$currentSort = $currentSort ?? 'created_at_desc';
$sortOptionsMap = $sortOptionsMap ?? ['created_at_desc' => 'Mặc định'];
$priceRangesMap = $priceRangesMap ?? []; // Map chứa thông tin khoảng giá
$currentPriceRangeKey = $currentPriceRangeKey ?? 'all'; // Key khoảng giá đang chọn

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shop Grid Test</title>
    <style>
        body { font-family: sans-serif; display: flex; padding: 10px; }
        .sidebar { width: 220px; padding-right: 20px; border-right: 1px solid #ccc; margin-right: 20px; flex-shrink: 0; }
        .main-content { flex: 1; min-width: 0; } /* Added min-width */
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; margin-bottom: 20px; } /* Responsive grid */
        .product-item { border: 1px solid #eee; padding: 10px; text-align: center; }
        .product-item img { max-width: 100%; height: 120px; object-fit: contain; margin-bottom: 10px; } /* Adjusted image height */
        .product-item h5 { font-size: 0.9em; margin: 5px 0; min-height: 2.7em; } /* Fixed height for name */
        .product-item p { font-size: 0.9em; margin: 3px 0; }
        .product-item small { font-size: 0.8em; color: #666; }
        .pagination { margin-top: 20px; text-align: center; }
        .pagination a, .pagination strong { margin: 0 5px; text-decoration: none; padding: 5px 10px; border: 1px solid #ddd; }
        .pagination strong { font-weight: bold; background-color: #eee; }
        .pagination a:hover { background-color: #f0f0f0; }
        .filter-group { margin-bottom: 20px; }
        .filter-group h4 { margin-bottom: 8px; font-size: 1.1em; border-bottom: 1px solid #eee; padding-bottom: 5px;}
        .filter-group ul { list-style: none; padding: 0; margin: 0; }
        .filter-group li { margin-bottom: 5px; }
        .filter-group li a { text-decoration: none; color: #007bff; }
        .filter-group li a:hover { text-decoration: underline; }
        .filter-group li a.active { font-weight: bold; color: #dc3545; } /* Highlight active filter */
        label, input[type="text"], input[type="number"], select, button { display: block; margin-bottom: 8px; width: 100%; box-sizing: border-box; padding: 8px; font-size: 0.9em; }
        select { width: 100%; }
        button { width: auto; cursor: pointer; background-color: #007bff; color: white; border: none; border-radius: 3px; padding: 8px 15px; }
        button:hover { background-color: #0056b3; }
        form { margin: 0; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>Filters & Sort</h3>

    <div class="filter-group">
        <h4>Search</h4>
        <form method="GET" action="">
            <input type="hidden" name="page" value="shop_grid">
            <input type="hidden" name="brand" value="<?= htmlspecialchars($currentFilters['brand']) ?>">
            <input type="hidden" name="price_range" value="<?= htmlspecialchars($currentPriceRangeKey) ?>">
            <input type="hidden" name="sort" value="<?= htmlspecialchars($currentSort) ?>">
            <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($currentFilters['search']) ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="filter-group">
        <h4>Brands</h4>
        <ul>
            <li>
                <a href="?<?= build_query_string(['brand' => 'All', 'pg' => null]) ?>"
                   class="<?= ($currentFilters['brand'] == 'All' || empty($currentFilters['brand'])) ? 'active' : '' ?>">
                    All Brands
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
        <h4>Price Range</h4>
        <ul>
            <li>
                <a href="?<?= build_query_string(['price_range' => 'all', 'pg' => null]) ?>"
                   class="<?= ($currentPriceRangeKey == 'all') ? 'active' : '' ?>">
                    All Prices
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
        <h4>Sort By</h4>
        <form method="GET" action="" id="sortForm">
            <input type="hidden" name="page" value="shop_grid">
            <input type="hidden" name="search" value="<?= htmlspecialchars($currentFilters['search']) ?>">
            <input type="hidden" name="brand" value="<?= htmlspecialchars($currentFilters['brand']) ?>">
            <input type="hidden" name="price_range" value="<?= htmlspecialchars($currentPriceRangeKey) ?>">
            <input type="hidden" name="pg" value="<?= $currentPage ?>">

            <label for="sort_select" class="sr-only">Sort products by</label> <select name="sort" id="sort_select" onchange="document.getElementById('sortForm').submit()">
                <?php foreach ($sortOptionsMap as $key => $value): ?>
                    <option value="<?= $key ?>" <?= ($currentSort == $key) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($value) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <noscript><button type="submit">Sort</button></noscript> </form>
    </div>

</div>

<div class="main-content">
    <h1>Shop Grid</h1>

    <p>Showing <?= count($products) ?> of <strong><?= $totalProducts ?></strong> products found.</p>

    <h2>Products</h2>
    <?php if (!empty($products)): ?>
        <div class="products-grid">
            <?php foreach ($products as $p): ?>
                <div class="product-item">
                    <a href="?page=product_detail&id=<?= $p['id'] ?>"> <img src="/public/img/<?= htmlspecialchars($p['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy"> </a>
                    <h5><a href="?page=product_detail&id=<?= $p['id'] ?>" style="text-decoration:none; color: inherit;"><?= htmlspecialchars($p['name']) ?></a></h5>
                    <p><strong><?= number_format($p['price'], 0, ',', '.') ?>₫</strong></p>
                    <p><small>Brand: <?= htmlspecialchars($p['brand']) ?></small></p>
                    <p><small>Rating: <?= htmlspecialchars(number_format($p['rating'] ?? 0, 1)) ?> ★</small></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No products found matching your criteria. Try adjusting the filters.</p>
    <?php endif; ?>

    <div class="pagination">
        <?php if ($totalPages > 1): ?>
            <?php // Previous Page Link ?>
            <?php if ($currentPage > 1): ?>
                <a href="?<?= build_query_string(['pg' => $currentPage - 1]) ?>" aria-label="Previous Page">&laquo; Prev</a>
            <?php else: ?>
                <span style="color: #ccc; padding: 5px 10px; border: 1px solid #ddd;">&laquo; Prev</span> <?php endif; ?>

            <?php // Page Number Links (simplified view for many pages) ?>
            <?php
            $maxPagesToShow = 5; // Max number of direct page links to show
            $startPage = max(1, $currentPage - floor($maxPagesToShow / 2));
            $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);
            // Adjust startPage if endPage reaches the limit first
            $startPage = max(1, $endPage - $maxPagesToShow + 1);

            if ($startPage > 1) {
                echo '<a href="?' . build_query_string(['pg' => 1]) . '">1</a>';
                if ($startPage > 2) {
                    echo '<span>...</span>';
                }
            }

            for ($i = $startPage; $i <= $endPage; $i++) {
                if ($i == $currentPage) {
                    echo '<strong>' . $i . '</strong>';
                } else {
                    echo '<a href="?' . build_query_string(['pg' => $i]) . '">' . $i . '</a>';
                }
            }

            if ($endPage < $totalPages) {
                if ($endPage < $totalPages - 1) {
                    echo '<span>...</span>';
                }
                echo '<a href="?' . build_query_string(['pg' => $totalPages]) . '">' . $totalPages . '</a>';
            }
            ?>

            <?php // Next Page Link ?>
            <?php if ($currentPage < $totalPages): ?>
                <a href="?<?= build_query_string(['pg' => $currentPage + 1]) ?>" aria-label="Next Page">Next &raquo;</a>
            <?php else: ?>
                <span style="color: #ccc; padding: 5px 10px; border: 1px solid #ddd;">Next &raquo;</span> <?php endif; ?>
        <?php endif; ?>
    </div>

</div>

</body>
</html>