<?php
// Web/app/Controllers/ProductController.php

namespace App\Controllers;

use App\Models\Product;
use App\Models\Review;
// use App\Models\Wishlist; // Bỏ use nếu không dùng Wishlist ở đâu khác ngoài header
use Exception; // <-- Cần cho try...catch trong getRelatedProducts

class ProductController extends BaseController {

    // Hằng số cấu hình (có thể đặt ở config file nếu muốn)
    private const ITEMS_PER_PAGE = 9;
    private const DEFAULT_SORT = 'created_at_desc';
    private const ALLOWED_SORT_OPTIONS = [
        'created_at_desc', 'price_asc', 'price_desc',
        'name_asc', 'name_desc', 'rating_desc'
    ];
    private const PRICE_RANGES_MAP = [
        '0-1'     => ['label' => 'Dưới 1 triệu',  'min' => null, 'max' => 1000000],
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


    /**
     * Constructor - Initialize session if needed.
     */
    public function __construct() {
        // Không cần gọi session_start() ở đây vì BaseController::getGlobalViewData đã làm
        // if (session_status() == PHP_SESSION_NONE) {
        //     session_start();
        // }
    }

    /**
     * Redirects the old index action to the shop grid page.
     */
    public function index() {
        $this->redirect('?page=shop_grid');
    }

    /**
     * Displays the product detail page.
     * @param int $id ID of the product.
     */
    public function detail($id) {
        // --- Validate ID ---
        $productId = filter_var($id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        if ($productId === false) {
            http_response_code(400);
            $this->render('errors/404', ['message' => 'ID sản phẩm không hợp lệ.', 'pageTitle' => 'Lỗi 400']);
            return;
        }

        // --- Get Product Data ---
        $product = Product::find($productId);

        // --- Check if Product Exists ---
        if (!$product) {
            http_response_code(404);
            $this->render('errors/404', ['message' => 'Không tìm thấy sản phẩm bạn yêu cầu.', 'pageTitle' => '404 - Không tìm thấy']);
            return;
        }

        // --- Get Reviews ---
        $reviews = Review::getByProduct($productId);

        // --- Get Related Products ---
        $relatedProducts = $this->getRelatedProducts($product);

        // --- Prepare Data for View (isLoggedIn, wishlistedIds, etc. được thêm tự động bởi BaseController) ---
        $data = [
            'product' => $product,
            'reviews' => $reviews,
            'relatedProducts' => $relatedProducts,
            'pageTitle' => $product['name'] ?? 'Chi tiết sản phẩm'
        ];

        $this->render('product_detail', $data);
    }

    // ======================================================
    // HÀM shopGrid ĐÃ ĐƯỢC REFACTOR
    // ======================================================
    public function shopGrid() {
        // 1. Lấy các tham số đã xử lý từ các hàm helper
        $paginationParams = $this->processShopPagination(self::ITEMS_PER_PAGE);
        $filterParams = $this->processShopFilters(self::PRICE_RANGES_MAP);
        $sortOption = $this->processShopSorting(self::DEFAULT_SORT, self::ALLOWED_SORT_OPTIONS);
        // Wishlist data sẽ được BaseController tự động thêm vào $finalData trong hàm render

        // 2. Fetch Data từ Models
        $availableBrands = Product::getDistinctBrands();
        $products = Product::getFilteredProducts(
            $filterParams['modelFilters'], // Chỉ các filter cho model
            $sortOption,
            $paginationParams['limit'],
            $paginationParams['offset']
        );
        $totalProducts = (int)Product::countFilteredProducts($filterParams['modelFilters']);

        // 3. Tính toán phân trang
        $totalPages = ($paginationParams['limit'] > 0 && $totalProducts > 0)
            ? ceil($totalProducts / $paginationParams['limit'])
            : 1;

        // 4. Chuẩn bị dữ liệu đầy đủ cho View
        // isLoggedIn, wishlistedIds, wishlistItemCount, cartItemCount sẽ được tự động thêm
        $data = [
            'products' => $products,
            'totalProducts' => $totalProducts,
            'currentPage' => $paginationParams['currentPage'],
            'totalPages' => $totalPages,
            'itemsPerPage' => $paginationParams['limit'], // Lấy từ paginationParams
            'availableBrands' => $availableBrands,
            'currentFilters' => $filterParams['currentFilters'], // Filters cho view
            'currentSort' => $sortOption,
            'sortOptionsMap' => self::SORT_OPTIONS_MAP, // Lấy từ hằng số
            'priceRangesMap' => self::PRICE_RANGES_MAP, // Lấy từ hằng số
            'currentPriceRangeKey' => $filterParams['currentFilters']['price_range'], // Lấy từ filterParams
            // 'isLoggedIn' và 'wishlistedIds' không cần truyền ở đây nữa
        ];

        // 5. Render View
        $this->render('shop_grid', $data);
    }

    // ======================================================
    // CÁC PHƯƠNG THỨC PRIVATE HELPER CHO shopGrid (Giữ nguyên trừ getWishlistDataForShop)
    // ======================================================

    /**
     * Xử lý và trả về các tham số phân trang.
     * @param int $itemsPerPage Số item mỗi trang.
     * @return array ['limit', 'offset', 'currentPage']
     */
    private function processShopPagination(int $itemsPerPage): array {
        $currentPage = isset($_GET['pg']) ? max(1, (int)$_GET['pg']) : 1;
        $offset = ($currentPage - 1) * $itemsPerPage;
        return [
            'limit' => $itemsPerPage,
            'offset' => $offset,
            'currentPage' => $currentPage
        ];
    }

    /**
     * Xử lý và trả về các tham số bộ lọc từ GET request.
     * @param array $priceRangesMap Map định nghĩa các khoảng giá.
     * @return array Chứa ['modelFilters' => [], 'currentFilters' => []]
     */
    private function processShopFilters(array $priceRangesMap): array {
        $modelFilters = []; // Filters để truyền vào Product Model
        $currentFilters = [ // Filters để truyền về View (hiển thị trạng thái hiện tại)
            'search' => trim($_GET['search'] ?? ''),
            'brand' => $_GET['brand'] ?? 'All',
            'price_range' => $_GET['price_range'] ?? 'all',
        ];

        if (!empty($currentFilters['search'])) { $modelFilters['search'] = $currentFilters['search']; }
        if (!empty($currentFilters['brand']) && $currentFilters['brand'] !== 'All') { $modelFilters['brand'] = $currentFilters['brand']; }
        $currentPriceKey = $currentFilters['price_range'];
        if ($currentPriceKey !== 'all' && isset($priceRangesMap[$currentPriceKey])) {
            $range = $priceRangesMap[$currentPriceKey];
            if ($range['min'] !== null) $modelFilters['min_price'] = $range['min'];
            if ($range['max'] !== null) $modelFilters['max_price'] = $range['max'];
        }

        return [ 'modelFilters' => $modelFilters, 'currentFilters' => $currentFilters ];
    }

    /**
     * Xử lý và trả về tùy chọn sắp xếp hợp lệ.
     * @param string $defaultSort Giá trị mặc định.
     * @param array $allowedSorts Mảng các giá trị sắp xếp hợp lệ.
     * @return string Tùy chọn sắp xếp hợp lệ.
     */
    private function processShopSorting(string $defaultSort, array $allowedSorts): string {
        $currentSort = $_GET['sort'] ?? $defaultSort;
        if (!in_array($currentSort, $allowedSorts)) { $currentSort = $defaultSort; }
        return $currentSort;
    }

    /* ---- BỎ HÀM getWishlistDataForShop() ---- */

    /**
     * Lấy danh sách sản phẩm liên quan (ví dụ: cùng brand). Helper cho hàm detail().
     * @param array $product Sản phẩm chính.
     * @return array Danh sách sản phẩm liên quan.
     */
    private function getRelatedProducts(array $product): array {
        $relatedProducts = [];
        if (!empty($product['brand'])) {
            try {
                $allRelated = Product::getByBrand($product['brand']);
                $count = 0;
                foreach ($allRelated as $relP) {
                    if ((int)($relP['id'] ?? 0) !== (int)$product['id'] && $count < 4) {
                        $relatedProducts[] = $relP;
                        $count++;
                    }
                    if ($count >= 4) break;
                }
            } catch (Exception $e) {
                error_log("Error fetching related products: " . $e->getMessage());
                $relatedProducts = [];
            }
        }
        return $relatedProducts;
    }

} // End Class ProductController