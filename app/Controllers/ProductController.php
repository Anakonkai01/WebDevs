<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Review; // Giữ lại nếu dùng sort 'rating_desc'
use App\Models\Wishlist; // Cần cho BaseController lấy wishlistedIds
use Exception;

class ProductController extends BaseController {

    // --- Hằng số cấu hình ---
    private const ITEMS_PER_PAGE = 9;
    private const DEFAULT_SORT = 'created_at_desc';
    private const ALLOWED_SORT_OPTIONS = [
        'created_at_desc', 'price_asc', 'price_desc',
        'name_asc', 'name_desc', 'rating_desc'
    ];
    // --- Danh sách các cột spec được phép lọc ---
    private const ALLOWED_SPEC_FILTERS = ['ram', 'cpu', 'screen_size', 'storage', 'os', 'battery_capacity', 'screen_tech'];
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

    public function __construct() {
        // Constructor của BaseController đã xử lý session
    }

    public function index() {
        $this->redirect('?page=shop_grid');
    }

    public function detail($id) {
       $productId = filter_var($id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
       if ($productId === false) { $this->showNotFoundPage('ID sản phẩm không hợp lệ.'); return; }
       $product = Product::find($productId);
       if (!$product) { $this->showNotFoundPage('Không tìm thấy sản phẩm.'); return; }
       $reviews = Review::getByProduct($productId);
       $relatedProducts = $this->getRelatedProducts($product);

       // Lấy dữ liệu global (bao gồm wishlist) từ BaseController
       $globalData = $this->getGlobalViewData(); // Gọi hàm kế thừa từ BaseController

       $data = array_merge($globalData, [
           'product' => $product,
           'reviews' => $reviews,
           'relatedProducts' => $relatedProducts,
           'pageTitle' => $product['name'] ?? 'Chi tiết sản phẩm'
       ]);
       $this->render('product_detail', $data);
   }


    /**
     * === HÀM shopGrid ĐÃ CẬP NHẬT CHO AJAX ===
     */
    public function shopGrid() {
        // --- Kiểm tra nếu là AJAX request ---
        // Cách 1: Kiểm tra header (phổ biến hơn)
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        // Cách 2: Dùng tham số GET (nếu muốn test dễ hơn qua URL)
        // $isAjax = isset($_GET['ajax']) && $_GET['ajax'] == 1;

        // 1. Lấy các tham số (luôn cần, dù AJAX hay không)
        $paginationParams = $this->processShopPagination(self::ITEMS_PER_PAGE);
        $filterParams = $this->processShopFilters(self::PRICE_RANGES_MAP, self::ALLOWED_SPEC_FILTERS);
        $sortOption = $this->processShopSorting(self::DEFAULT_SORT, self::ALLOWED_SORT_OPTIONS);

        // 2. Fetch Data từ Models (luôn cần)
        $products = Product::getFilteredProducts(
            $filterParams['modelFilters'],
            $sortOption,
            $paginationParams['limit'],
            $paginationParams['offset']
        );
        $totalProducts = (int)Product::countFilteredProducts($filterParams['modelFilters']);

        // 3. Tính toán phân trang (luôn cần)
        $totalPages = ($paginationParams['limit'] > 0 && $totalProducts > 0) ? ceil($totalProducts / $paginationParams['limit']) : 1;
        // Lấy trang hiện tại từ paginationParams để đảm bảo nó hợp lệ (>= 1)
        $currentPage = max(1, min($paginationParams['currentPage'], $totalPages));


        // Tính toán số item hiển thị
        $startItemNum = $totalProducts > 0 ? (($currentPage - 1) * $paginationParams['limit']) + 1 : 0;
        $endItemNum = $totalProducts > 0 ? min($startItemNum + count($products) - 1, $totalProducts) : 0;


        // --- Xử lý trả về dựa trên loại request ---
        if ($isAjax) {
             // Lấy dữ liệu chung cần cho partial views (ví dụ: wishlist)
             $globalData = $this->getGlobalViewData();
             $isLoggedIn = $globalData['isLoggedIn'] ?? false;
             $wishlistedIds = $globalData['wishlistedIds'] ?? [];

             // **Tạo HTML cho Product Grid và Pagination**
             ob_start();
              // Truyền biến vào partial view
             extract(['products' => $products, 'isLoggedIn' => $isLoggedIn, 'wishlistedIds' => $wishlistedIds]);
             include BASE_PATH . '/app/Views/partials/product_grid_items.php';
             $productHtml = ob_get_clean();

             ob_start();
             // Truyền biến vào partial view
             extract(['currentPage' => $currentPage, 'totalPages' => $totalPages]);
             include BASE_PATH . '/app/Views/partials/pagination.php';
             $paginationHtml = ob_get_clean();

            // **Tạo chuỗi hiển thị số lượng sản phẩm**
            $countText = ($totalProducts > 0)
                ? "Hiển thị {$startItemNum}–{$endItemNum} / {$totalProducts} sản phẩm"
                : "Không tìm thấy sản phẩm nào khớp với bộ lọc.";

             // **Trả về JSON**
             header('Content-Type: application/json; charset=utf-8');
             // ob_clean(); // Quan trọng: Xóa mọi output rác có thể có trước khi echo JSON
             echo json_encode([
                 'success' => true,
                 'productHtml' => $productHtml,
                 'paginationHtml' => $paginationHtml,
                 'countText' => $countText,
                 'totalProducts' => $totalProducts,
             ]);
             exit; // Dừng thực thi sau khi gửi JSON

        } else {
            // --- Render trang đầy đủ như bình thường ---
             $availableBrands = Product::getDistinctBrands();
             $availableSpecs = [];
             foreach (self::ALLOWED_SPEC_FILTERS as $spec) {
                 $availableSpecs[$spec] = Product::getDistinctValuesForSpec($spec);
             }

             // Lấy dữ liệu global (bao gồm wishlist) để truyền vào view đầy đủ
             $globalData = $this->getGlobalViewData();

             $data = array_merge($globalData, [ // Merge dữ liệu global vào data cho view
                 'products' => $products, // Dữ liệu sản phẩm ban đầu
                 'totalProducts' => $totalProducts,
                 'currentPage' => $currentPage,
                 'totalPages' => $totalPages,
                 'itemsPerPage' => $paginationParams['limit'],
                 'availableBrands' => $availableBrands,
                 'availableSpecs' => $availableSpecs,
                 'currentFilters' => $filterParams['currentFilters'],
                 'currentSort' => $sortOption,
                 'sortOptionsMap' => self::SORT_OPTIONS_MAP,
                 'priceRangesMap' => self::PRICE_RANGES_MAP,
                 'currentPriceRangeKey' => $filterParams['currentFilters']['price_range'],
                 // Các biến khác nếu cần cho view đầy đủ
             ]);
             $this->render('shop_grid', $data);
        }
    }

    // --- Các hàm helper processShopPagination, processShopFilters, processShopSorting, getRelatedProducts ---
    // Đảm bảo chúng giữ nguyên hoặc được cập nhật như trong các bước trước
    private function processShopPagination(int $itemsPerPage): array {
        $currentPage = isset($_GET['pg']) ? max(1, (int)$_GET['pg']) : 1;
        $offset = ($currentPage - 1) * $itemsPerPage;
        return [ 'limit' => $itemsPerPage, 'offset' => $offset, 'currentPage' => $currentPage ];
    }

    private function processShopFilters(array $priceRangesMap, array $allowedSpecFilters): array {
        $modelFilters = [];
        $currentFilters = [
            'search' => trim($_GET['search'] ?? ''),
            'brand' => $_GET['brand'] ?? 'All',
            'price_range' => $_GET['price_range'] ?? 'all',
        ];
        foreach ($allowedSpecFilters as $spec) {
            $currentFilters[$spec] = $_GET[$spec] ?? 'all';
        }

        if (!empty($currentFilters['search'])) { $modelFilters['search'] = $currentFilters['search']; }
        if (!empty($currentFilters['brand']) && $currentFilters['brand'] !== 'All') { $modelFilters['brand'] = $currentFilters['brand']; }
        $currentPriceKey = $currentFilters['price_range'];
        if ($currentPriceKey !== 'all' && isset($priceRangesMap[$currentPriceKey])) {
            $range = $priceRangesMap[$currentPriceKey];
            if ($range['min'] !== null) $modelFilters['min_price'] = $range['min'];
            if ($range['max'] !== null) $modelFilters['max_price'] = $range['max'];
        }

        foreach ($allowedSpecFilters as $spec) {
            if (!empty($currentFilters[$spec]) && $currentFilters[$spec] !== 'all') {
                $modelFilters[$spec] = $currentFilters[$spec];
            }
        }
        return [ 'modelFilters' => $modelFilters, 'currentFilters' => $currentFilters ];
    }

    private function processShopSorting(string $defaultSort, array $allowedSorts): string {
        $currentSort = $_GET['sort'] ?? $defaultSort;
        if (!in_array($currentSort, $allowedSorts)) { $currentSort = $defaultSort; }
        return $currentSort;
    }

    private function getRelatedProducts(array $product): array {
        $relatedProducts = [];
        if (!empty($product['brand'])) {
            try {
                $allRelated = Product::getByBrand($product['brand']);
                $count = 0;
                foreach ($allRelated as $relP) {
                    // Đảm bảo relP có ID và khác ID sản phẩm chính
                    if (isset($relP['id']) && (int)$relP['id'] !== (int)$product['id'] && $count < 4) {
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