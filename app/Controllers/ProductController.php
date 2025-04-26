<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Review;
use Exception; // Cần cho try-catch

class ProductController extends BaseController {

    // --- Constants (Giữ nguyên từ phiên bản trước) ---
    private const ITEMS_PER_PAGE = 9;
    private const DEFAULT_SORT = 'created_at_desc';
    private const ALLOWED_SORT_OPTIONS = ['created_at_desc', 'price_asc', 'price_desc', 'name_asc', 'name_desc', 'rating_desc'];
    private const ALLOWED_SPEC_FILTERS = ['ram', 'cpu', 'screen_size', 'storage', 'os', 'battery_capacity', 'screen_tech'];
    private const PRICE_RANGES_MAP = [
        'all'     => ['label' => 'Tất cả',         'min' => null, 'max' => null],
        '0-1'     => ['label' => 'Dưới 1 triệu',  'min' => 0,    'max' => 999999],
        '1-5'     => ['label' => '1 - 5 triệu',   'min' => 1000000, 'max' => 5000000],
        '5-10'    => ['label' => '5 - 10 triệu',  'min' => 5000001, 'max' => 10000000],
        '10-15'   => ['label' => '10 - 15 triệu', 'min' => 10000001,'max' => 15000000],
        '15-20'   => ['label' => '15 - 20 triệu', 'min' => 15000001,'max' => 20000000],
        '20-25'   => ['label' => '20 - 25 triệu', 'min' => 20000001,'max' => 25000000],
        '25-30'   => ['label' => '25 - 30 triệu', 'min' => 25000001,'max' => 30000000],
        '30-plus' => ['label' => 'Trên 30 triệu','min' => 30000001,'max' => null],
    ];
    private const SORT_OPTIONS_MAP = [
        'created_at_desc'=> 'Mặc định (Mới nhất)',
        'price_asc'      => 'Giá: Thấp đến Cao',
        'price_desc'     => 'Giá: Cao đến Thấp',
        'name_asc'       => 'Tên: A-Z',
        'name_desc'      => 'Tên: Z-A',
        'rating_desc'    => 'Đánh giá cao nhất'
    ];

    // --- Hàm shopGrid và các helper của nó giữ nguyên ---
    public function shopGrid() {
        // ... (Giữ nguyên code shopGrid)
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
                  && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        try {
            // 1. Lấy tham số lọc, sắp xếp, phân trang từ request
            $paginationParams = $this->processShopPagination(self::ITEMS_PER_PAGE);
            $filterParams = $this->processShopFilters(self::PRICE_RANGES_MAP, self::ALLOWED_SPEC_FILTERS);
            $sortOption = $this->processShopSorting(self::DEFAULT_SORT, self::ALLOWED_SORT_OPTIONS);

            // 2. Fetch dữ liệu từ Model
            $products = Product::getFilteredProducts(
                $filterParams['modelFilters'],
                $sortOption,
                $paginationParams['limit'],
                $paginationParams['offset']
            );
            $totalProducts = Product::countFilteredProducts($filterParams['modelFilters']);

            // 3. Tính toán phân trang
            $totalPages = ($paginationParams['limit'] > 0 && $totalProducts > 0)
                          ? (int)ceil($totalProducts / $paginationParams['limit']) : 1;
            $currentPage = max(1, min($paginationParams['currentPage'], $totalPages));
            $paginationParams['offset'] = ($currentPage - 1) * $paginationParams['limit'];

            if ($currentPage != $paginationParams['currentPage'] && $currentPage < $paginationParams['currentPage']) {
                 $products = Product::getFilteredProducts(
                    $filterParams['modelFilters'],
                    $sortOption,
                    $paginationParams['limit'],
                    $paginationParams['offset']
                );
            }

            $startItemNum = $totalProducts > 0 ? $paginationParams['offset'] + 1 : 0;
            $endItemNum = $totalProducts > 0 ? min($startItemNum + count($products) - 1, $totalProducts) : 0;

            // 4. Xử lý Response
            if ($isAjax) {
                $this->handleAjaxResponse($products, $totalProducts, $currentPage, $totalPages, $startItemNum, $endItemNum);
            } else {
                $this->handleFullPageLoad($products, $totalProducts, $currentPage, $totalPages, $startItemNum, $endItemNum, $filterParams, $sortOption);
            }

        } catch (Exception $e) {
            error_log("Error in shopGrid: " . $e->getMessage() . "\n" . $e->getTraceAsString());
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
    private function handleAjaxResponse(array $products, int $totalProducts, int $currentPage, int $totalPages, int $startItemNum, int $endItemNum) { /* ... */
       // Mặc định lỗi
        $response = ['success' => false, 'message' => 'Lỗi render dữ liệu AJAX.'];
        ob_start(); // Start output buffering FOR BOTH partials

        try {
            $globalData = $this->getGlobalViewData();
            $isLoggedIn = $globalData['isLoggedIn'] ?? false;
            $wishlistedIds = $globalData['wishlistedIds'] ?? [];

            // --- Render Product Grid HTML ---
            $viewDataGrid = [
                'products' => $products,
                'isLoggedIn' => $isLoggedIn,
                'wishlistedIds' => $wishlistedIds
            ];
            extract($viewDataGrid);
            include BASE_PATH . '/app/Views/partials/product_grid_items.php';
            $productHtml = ob_get_contents();
            ob_clean(); // Clear buffer for the next partial

            // --- Render Pagination HTML ---
            $viewDataPagination = [
                'currentPage' => $currentPage,
                'totalPages' => $totalPages
            ];
            extract($viewDataPagination);
            include BASE_PATH . '/app/Views/partials/pagination.php';
            $paginationHtml = ob_get_contents();

            // Tạo Count Text
             $countText = ($totalProducts > 0)
                 ? "Hiển thị {$startItemNum}–{$endItemNum} / {$totalProducts} sản phẩm"
                 : "Không tìm thấy sản phẩm nào.";

            // Chuẩn bị response thành công
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
    private function handleFullPageLoad(array $products, int $totalProducts, int $currentPage, int $totalPages, int $startItemNum, int $endItemNum, array $filterParams, string $sortOption) { /* ... */
         $availableBrands = Product::getDistinctBrands();
         $availableSpecs = [];
         foreach (self::ALLOWED_SPEC_FILTERS as $spec) {
             $distinctValues = Product::getDistinctValuesForSpec($spec);
             if (!empty($distinctValues)) {
                 $availableSpecs[$spec] = $distinctValues;
             }
         }

         $globalData = $this->getGlobalViewData();

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

         $this->render('shop_grid', $data);
    }
    private function processShopPagination(int $itemsPerPage): array { /* ... */
         $currentPage = filter_input(INPUT_GET, 'pg', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $currentPage = $currentPage ?: 1;
        $offset = ($currentPage - 1) * $itemsPerPage;
        return [ 'limit' => $itemsPerPage, 'offset' => $offset, 'currentPage' => $currentPage ];
    }
    private function processShopFilters(array $priceRangesMap, array $allowedSpecFilters): array { /* ... */
        $modelFilters = []; $currentFilters = [];
        $currentFilters['search'] = trim(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS) ?: '');
        $currentFilters['brand'] = filter_input(INPUT_GET, 'brand', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'All';
        $currentFilters['price_range'] = filter_input(INPUT_GET, 'price_range', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'all';
        foreach ($allowedSpecFilters as $spec) { $currentFilters[$spec] = filter_input(INPUT_GET, $spec, FILTER_SANITIZE_SPECIAL_CHARS) ?: 'all'; }

        if (!empty($currentFilters['search'])) $modelFilters['search'] = $currentFilters['search'];
        if ($currentFilters['brand'] !== 'All' && !empty($currentFilters['brand'])) $modelFilters['brand'] = $currentFilters['brand'];
        $currentPriceKey = $currentFilters['price_range'];
        if ($currentPriceKey !== 'all' && isset($priceRangesMap[$currentPriceKey])) {
            $range = $priceRangesMap[$currentPriceKey];
            if (isset($range['min']) && is_numeric($range['min'])) $modelFilters['min_price'] = $range['min'];
            if (isset($range['max']) && is_numeric($range['max'])) $modelFilters['max_price'] = $range['max'];
        }
        foreach ($allowedSpecFilters as $spec) { if ($currentFilters[$spec] !== 'all' && !empty($currentFilters[$spec])) $modelFilters[$spec] = $currentFilters[$spec]; }

        return [ 'modelFilters' => $modelFilters, 'currentFilters' => $currentFilters ];
    }
    private function processShopSorting(string $defaultSort, array $allowedSorts): string { /* ... */
        $currentSort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_SPECIAL_CHARS) ?: $defaultSort;
        return in_array($currentSort, $allowedSorts) ? $currentSort : $defaultSort;
    }
    // --- Kết thúc code shopGrid ---

    /**
     * Hiển thị trang chi tiết sản phẩm.
     *
     * @param int $id ID sản phẩm.
     * @return void
     */
    public function detail($id) {
        // 1. Validate ID
        $productId = filter_var($id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        if ($productId === false) {
            $this->showNotFoundPage('ID sản phẩm không hợp lệ.');
            return;
        }

        try {
            // 2. Lấy dữ liệu sản phẩm chính
            $product = Product::find($productId);
            if (!$product) {
                $this->showNotFoundPage('Không tìm thấy sản phẩm.');
                return;
            }

            // 3. Lấy đánh giá (bao gồm tên người dùng)
            $reviews = Review::getByProduct($productId); // Model đã được cập nhật để JOIN với users

            // 4. Lấy sản phẩm liên quan (logic đơn giản dựa trên brand)
            $relatedProducts = $this->getRelatedProducts($product);

            // 5. Lấy dữ liệu global (trạng thái đăng nhập, wishlist, giỏ hàng)
            // BaseController::render sẽ tự động gọi getGlobalViewData() và gộp dữ liệu
            // $globalData = $this->getGlobalViewData(); // Không cần gọi ở đây nữa

            // 6. Chuẩn bị dữ liệu cuối cùng cho view
            $data = [
                'product' => $product,
                'reviews' => $reviews, // Truyền reviews đã lấy
                'relatedProducts' => $relatedProducts,
                'pageTitle' => $product['name'] ?? 'Chi tiết sản phẩm'
                // Dữ liệu global như 'isLoggedIn', 'wishlistedIds', 'cartItemCount' sẽ được tự động thêm bởi render()
            ];

            // 7. Render view
            $this->render('product_detail', $data);

        } catch (Exception $e) {
            error_log("Error in ProductController::detail for ID {$id}: " . $e->getMessage());
            $this->showErrorPage('Đã xảy ra lỗi khi tải chi tiết sản phẩm.');
        }
    }

    /**
     * Lấy danh sách sản phẩm liên quan.
     * Ví dụ: Lấy tối đa 4 sản phẩm khác cùng thương hiệu.
     *
     * @param array $product Dữ liệu sản phẩm hiện tại.
     * @return array Danh sách sản phẩm liên quan.
     */
    private function getRelatedProducts(array $product): array {
        $relatedProducts = [];
        $productId = (int)($product['id'] ?? 0);
        $brand = $product['brand'] ?? null;

        if ($productId > 0 && !empty($brand)) {
            try {
                // Lấy tối đa 5 sản phẩm cùng brand (bao gồm cả sản phẩm hiện tại)
                $limit = 5;
                $allRelated = Product::getByBrand($brand, $limit);

                $count = 0;
                foreach ($allRelated as $relP) {
                    // Chỉ lấy 4 sản phẩm KHÁC sản phẩm hiện tại
                    if (isset($relP['id']) && (int)$relP['id'] !== $productId && $count < 4) {
                        $relatedProducts[] = $relP;
                        $count++;
                    }
                    if ($count >= 4) break; // Dừng nếu đã đủ 4 sản phẩm
                }
            } catch (Exception $e) {
                error_log("Error fetching related products for product ID {$productId}, Brand {$brand}: " . $e->getMessage());
                // Trả về mảng rỗng nếu có lỗi
                $relatedProducts = [];
            }
        }
        return $relatedProducts;
    }

} // End Class ProductController