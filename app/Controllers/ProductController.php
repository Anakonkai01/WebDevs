<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Review;
use Exception;

class ProductController extends BaseController
{
    // Number of products per page
    private const ITEMS_PER_PAGE = 9;
    // Default sorting
    private const DEFAULT_SORT = 'created_at_desc';
    // Allowed sorting options
    private const ALLOWED_SORT_OPTIONS = ['created_at_desc', 'price_asc', 'price_desc', 'name_asc', 'name_desc', 'rating_desc'];
    // Allowed specification filters
    private const ALLOWED_SPEC_FILTERS = ['ram', 'cpu', 'screen_size', 'storage', 'os', 'battery_capacity', 'screen_tech'];
    // Map price ranges to labels and values
    private const PRICE_RANGES_MAP = [
        'all' => ['label' => 'Tất cả', 'min' => null, 'max' => null],
        '0-1' => ['label' => 'Dưới 1 triệu', 'min' => 0, 'max' => 999999],
        '1-5' => ['label' => '1 - 5 triệu', 'min' => 1000000, 'max' => 5000000],
        '5-10' => ['label' => '5 - 10 triệu', 'min' => 5000001, 'max' => 10000000],
        '10-15' => ['label' => '10 - 15 triệu', 'min' => 10000001, 'max' => 15000000],
        '15-20' => ['label' => '15 - 20 triệu', 'min' => 15000001, 'max' => 20000000],
        '20-25' => ['label' => '20 - 25 triệu', 'min' => 20000001, 'max' => 25000000],
        '25-30' => ['label' => '25 - 30 triệu', 'min' => 25000001, 'max' => 30000000],
        '30-plus' => ['label' => 'Trên 30 triệu', 'min' => 30000001, 'max' => null],
    ];
    // Map sort options to labels
      private const SORT_OPTIONS_MAP = [
        'created_at_desc' => 'Mặc định (Mới nhất)',
        'price_asc' => 'Giá: Thấp đến Cao',
        'price_desc' => 'Giá: Cao đến Thấp',
        'name_asc' => 'Tên: A-Z',
        'name_desc' => 'Tên: Z-A',
        'rating_desc' => 'Đánh giá cao nhất'
    ];

    // Display shop grid page
    public function shopGrid()
    {
        // Check if AJAX request
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        // Handle exceptions
        try {
            // 1. Process pagination, filters, and sorting parameters from the request
            $paginationParams = $this->processShopPagination(self::ITEMS_PER_PAGE);
            $filterParams = $this->processShopFilters(self::PRICE_RANGES_MAP, self::ALLOWED_SPEC_FILTERS);
            $sortOption = $this->processShopSorting(self::DEFAULT_SORT, self::ALLOWED_SORT_OPTIONS);

            // 2. Fetch data from the Product model based on the processed parameters
            $products = Product::getFilteredProducts(
                $filterParams['modelFilters'],
                $sortOption,
                $paginationParams['limit'],
                $paginationParams['offset']
            );
            $totalProducts = Product::countFilteredProducts($filterParams['modelFilters']);
            // 3. Calculate pagination details
            $totalPages = ($paginationParams['limit'] > 0 && $totalProducts > 0)
                ? (int)ceil($totalProducts / $paginationParams['limit']) : 1;
            $currentPage = max(1, min($paginationParams['currentPage'], $totalPages));
            $paginationParams['offset'] = ($currentPage - 1) * $paginationParams['limit'];

            // check page is valid
            if ($currentPage != $paginationParams['currentPage'] && $currentPage < $paginationParams['currentPage']) {
                $products = Product::getFilteredProducts($filterParams['modelFilters'],
                 $sortOption,
                 $paginationParams['limit'],
                    $paginationParams['offset']
                );
            }
            //Calculate the starting and ending item numbers for display
            $startItemNum = $totalProducts > 0 ? $paginationParams['offset'] + 1 : 0;
            $endItemNum = $totalProducts > 0 ? min($startItemNum + count($products) - 1, $totalProducts) : 0;

            // Handle the response
            if ($isAjax) {
                $this->handleAjaxResponse($products, $totalProducts, $currentPage, $totalPages, $startItemNum, $endItemNum);
            } else {
                $this->handleFullPageLoad($products, $totalProducts, $currentPage, $totalPages, $startItemNum, $endItemNum, $filterParams, $sortOption);
            }

        } catch (Exception $e) {
            error_log("Error in shopGrid: " . $e->getMessage() . "\n" . $e->getTraceAsString());
             // Handle AJAX error
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi server khi tải sản phẩm.']);
                exit;
            } else {
                $this->showErrorPage('Đã xảy ra lỗi khi tải trang cửa hàng.');
            }
        }
    }

    // Handle AJAX responses for the shop grid
    private function handleAjaxResponse(array $products, int $totalProducts, int $currentPage, int $totalPages, int $startItemNum, int $endItemNum)
    {
        $response = ['success' => false, 'message' => 'Lỗi render dữ liệu AJAX.'];
        ob_start();
        try {
            // Get global view data
            $globalData = $this->getGlobalViewData();

            //Get is login
            $isLoggedIn = $globalData['isLoggedIn'] ?? false;

            // Get wishlist Id
            $wishlistedIds = $globalData['wishlistedIds'] ?? [];

            // Render Product Grid HTML
            $viewDataGrid = [
                'products' => $products,
                'isLoggedIn' => $isLoggedIn,
                'wishlistedIds' => $wishlistedIds
            ];
            extract($viewDataGrid);
            include BASE_PATH . '/app/Views/partials/product_grid_items.php';

            // Get Product html
            $productHtml = ob_get_contents();

            // Render Pagination HTML
            $viewDataPagination = [
                'currentPage' => $currentPage,
                'totalPages' => $totalPages
            ];
            extract($viewDataPagination);
            include BASE_PATH . '/app/Views/partials/pagination.php';
            // Get Pagination html
            $paginationHtml = ob_get_contents();
            ob_clean();
             //count total products
             $countText = ($totalProducts > 0)
                ? "Hiển thị {$startItemNum}–{$endItemNum} / {$totalProducts} sản phẩm"
                : "Không tìm thấy sản phẩm nào.";

            // Prepare the successful response data
            $response = [
                'success' => true,
                'productHtml' => $productHtml,
                'paginationHtml' => $paginationHtml,
                'countText' => $countText,
                'totalProducts' => $totalProducts,
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
            ];

        } catch (Exception $e) {
            if (ob_get_level() > 0) ob_end_clean();
            error_log("Error rendering shop grid partials for AJAX: " . $e->getMessage());
            $response['message'] = 'Lỗi server khi tạo giao diện sản phẩm.';
            http_response_code(500);
        } finally {
            if (ob_get_level() > 0) ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        exit;
    }

    // Handle full page loads
    private function handleFullPageLoad(array $products, int $totalProducts, int $currentPage, int $totalPages, int $startItemNum, int $endItemNum, array $filterParams, string $sortOption)
    {
        //Get distinct brands

        $availableBrands = Product::getDistinctBrands();

        //Get distinct specification

        $availableSpecs = [];
        foreach (self::ALLOWED_SPEC_FILTERS as $spec) {
            $distinctValues = Product::getDistinctValuesForSpec($spec);
            if (!empty($distinctValues)) {
                $availableSpecs[$spec] = $distinctValues;
            }
        }
        // Get global view data
        $globalData = $this->getGlobalViewData();

        // data for view
        $data = array_merge($globalData, [
            'products' => $products,
            'totalProducts' => $totalProducts,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'itemsPerPage' => self::ITEMS_PER_PAGE,
            'startItemNum' => $startItemNum,
            'endItemNum' => $endItemNum,
            'availableBrands' => $availableBrands,
            'availableSpecs' => $availableSpecs,
            'currentFilters' => $filterParams['currentFilters'],
            'currentSort' => $sortOption,
            'sortOptionsMap' => self::SORT_OPTIONS_MAP,
            'priceRangesMap' => self::PRICE_RANGES_MAP,
            'pageTitle' => 'Cửa hàng'
        ]);

        // Render the shop_grid view with the prepared data
        $this->render('shop_grid', $data);
    }
    // Process pagination
    private function processShopPagination(int $itemsPerPage): array
    {
        // Filter and validate the current page
        $currentPage = filter_input(INPUT_GET, 'pg', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $currentPage = $currentPage ?: 1;
        // Calculate the offset
        $offset = ($currentPage - 1) * $itemsPerPage;
        return ['limit' => $itemsPerPage, 'offset' => $offset, 'currentPage' => $currentPage];
    }
    // process filter
    /**
     * Processes filter parameters from the request.
     */
    private function processShopFilters(array $priceRangesMap, array $allowedSpecFilters): array
    {
        $modelFilters = [];
        $currentFilters = [];
        // Get the search term and clean the character from the request
        $currentFilters['search'] = trim(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS) ?: '');
        // Get the brand and clean the character from the request
        $currentFilters['brand'] = filter_input(INPUT_GET, 'brand', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'All';
        // Get the price range and clean the character from the request
        $currentFilters['price_range'] = filter_input(INPUT_GET, 'price_range', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'all';
        //Get specification
        foreach ($allowedSpecFilters as $spec) {
            $currentFilters[$spec] = filter_input(INPUT_GET, $spec, FILTER_SANITIZE_SPECIAL_CHARS) ?: 'all';
        }
         // Set up the filter for model
        if (!empty($currentFilters['search'])) $modelFilters['search'] = $currentFilters['search'];
        if ($currentFilters['brand'] !== 'All' && !empty($currentFilters['brand'])) $modelFilters['brand'] = $currentFilters['brand'];
        //get price map
        $currentPriceKey = $currentFilters['price_range'];
        //set filter by price
        if ($currentPriceKey !== 'all' && isset($priceRangesMap[$currentPriceKey])) {
            $range = $priceRangesMap[$currentPriceKey];
            if (isset($range['min']) && is_numeric($range['min'])) $modelFilters['min_price'] = $range['min'];
            if (isset($range['max']) && is_numeric($range['max'])) $modelFilters['max_price'] = $range['max'];
        }
        //set filter by specification
        foreach ($allowedSpecFilters as $spec) {
            if ($currentFilters[$spec] !== 'all' && !empty($currentFilters[$spec])) $modelFilters[$spec] = $currentFilters[$spec]; // Add specification filter if not 'all'
        }
        return ['modelFilters' => $modelFilters, 'currentFilters' => $currentFilters];
    }

    /**
     * Processes sorting parameters from the request.
     *
     */
    private function processShopSorting(string $defaultSort, array $allowedSorts): string
    {
        // Get the sorting option from the request or use the default
        $currentSort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_SPECIAL_CHARS) ?: $defaultSort;
        // If the sort option is not in the allowed options, it will return default sort
        return in_array($currentSort, $allowedSorts) ? $currentSort : $defaultSort;
    }

    // product detail
    public function detail($id)
    {
        // Product Id

        // 1. Validate the product ID
        $productId = filter_var($id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        if ($productId === false) {
            $this->showNotFoundPage('ID sản phẩm không hợp lệ.');
            return;
        }

        try {
            // 2. Fetch the main product data
            $product = Product::find($productId);
            if (!$product) {
                $this->showNotFoundPage('Không tìm thấy sản phẩm.');
                return;
            }

            // 3. Fetch product reviews (including user names)
            $reviews = Review::getByProduct($productId);

            // 4. Fetch related products (based on the brand)
            $relatedProducts = $this->getRelatedProducts($product);

            // 5. Prepare the final data for the view
            $data = [
                'product' => $product,
                'reviews' => $reviews,
                'relatedProducts' => $relatedProducts,
                'pageTitle' => $product['name'] ?? 'Chi tiết sản phẩm'
            ];

            // 6. Render the view
            $this->render('product_detail', $data);
        } catch (Exception $e) {
            error_log("Error in ProductController::detail for ID {$id}: " . $e->getMessage());
            $this->showErrorPage('Đã xảy ra lỗi khi tải chi tiết sản phẩm.');
        }
    }

    /**
     * Get related products
     */
    private function getRelatedProducts(array $product): array {
        $relatedProducts = [];
        $productId = (int)($product['id'] ?? 0);
        $brand = $product['brand'] ?? null;

        // Ensure the product id and brand are valid
        if ($productId > 0 && !empty($brand)) {
            //Handle exception
            try {
                $limit = 5;
                $allRelated = Product::getByBrand($brand, $limit);
                $count = 0;
                foreach ($allRelated as $relP) {
                    if (isset($relP['id']) && (int)$relP['id'] !== $productId && $count < 4) {
                        $relatedProducts[] = $relP;
                        $count++;
                    }
                    if ($count >= 4) break;
                }
            } catch (Exception $e) {
                error_log("Error fetching related products for product ID {$productId}, Brand {$brand}: " . $e->getMessage());
                // Return an empty array if there is an error
                $relatedProducts = [];
            }
        }
        return $relatedProducts;
    }

}