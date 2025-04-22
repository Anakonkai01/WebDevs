<?php
require_once BASE_PATH . '/app/Models/Product.php';
require_once BASE_PATH . '/app/Controllers/BaseController.php';
require_once BASE_PATH . '/app/Models/Review.php';
require_once BASE_PATH . '/app/Models/Wishlist.php'; // *** THÊM DÒNG NÀY ***

class ProductController extends BaseController{

    // Hàm index cũ có thể giữ lại hoặc bỏ đi nếu không dùng
    public function index()
    {
        // $products = Product::all(); // Lấy tất cả có thể nặng nếu nhiều SP
        // $this->render('products_list_all', ['products' => $products]);
        // Thay vào đó, chuyển hướng đến trang shop grid mới
        $this->redirect('?page=shop_grid');
    }

    /**
     * Hiển thị trang chi tiết sản phẩm
     * @param int $id ID của sản phẩm
     */
    public function detail($id){
        // --- Xác thực ID ---
        // Đảm bảo ID là một số nguyên dương
        $productId = filter_var($id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

        if ($productId === false) {
            // ID không hợp lệ (không phải số nguyên dương)
            http_response_code(400); // Bad Request
            // Bạn có thể tạo một view lỗi chung errors/400.php
            // $this->render('errors/400', ['message' => 'ID sản phẩm không hợp lệ.']);
            echo "<h2>400 - ID sản phẩm không hợp lệ</h2>"; // Hoặc hiển thị thông báo đơn giản
            return;
        }

        // --- Lấy dữ liệu Sản phẩm ---
        $product = Product::find($productId);

        // --- Kiểm tra Sản phẩm tồn tại ---
        if (!$product) {
            // Không tìm thấy sản phẩm
            http_response_code(404); // Not Found
            // Bạn có thể tạo một view lỗi chung errors/404.php
            // $this->render('errors/404', ['message' => 'Không tìm thấy sản phẩm.']);
            echo "<h2>404 - Không tìm thấy sản phẩm</h2>"; // Hoặc hiển thị thông báo đơn giản
            return;
        }

        // --- Lấy dữ liệu Đánh giá ---
        // Giả sử Review model đã được require_once ở đầu file
        $reviews = Review::getByProduct($productId);


        // *** BỔ SUNG LẤY THÔNG TIN WISHLIST ***
        $isLoggedIn = isset($_SESSION['user_id']); // Kiểm tra đăng nhập
        $wishlistedIds = []; // Mảng chứa ID các SP đã thích
        if ($isLoggedIn) {
            // Nếu đăng nhập, lấy danh sách ID từ Wishlist Model
            $wishlistedIds = Wishlist::getWishlistedProductIds($_SESSION['user_id']);
        }
        // *** KẾT THÚC BỔ SUNG ***


        // --- Chuẩn bị dữ liệu cho View ---
        // Chuẩn bị dữ liệu cho View
        $data = [
            'product' => $product,
            'reviews' => $reviews,
            'isLoggedIn' => $isLoggedIn,         // <-- Truyền trạng thái đăng nhập
            'wishlistedIds' => $wishlistedIds     // <-- Truyền danh sách ID đã thích
        ];

        // --- Render View chi tiết sản phẩm ---
        // Đổi tên view thành 'product_detail' để rõ ràng hơn
        $this->render('product_detail', $data);
    }

    /**
     * Xử lý trang Shop Grid với lọc, sắp xếp, phân trang
     */
    public function shopGrid() {
        // --- Cấu hình ---
        $itemsPerPage = 9;

        // --- Định nghĩa các khoảng giá ---
        $priceRangesMap = [
            // Key => [label, min, max] (null nghĩa là không giới hạn)
            '0-1'   => ['label' => 'Dưới 1 triệu', 'min' => null, 'max' => 1000000],
            '1-5'   => ['label' => '1 - 5 triệu', 'min' => 1000000, 'max' => 5000000],
            '5-10'  => ['label' => '5 - 10 triệu', 'min' => 5000001, 'max' => 10000000],
            '10-15' => ['label' => '10 - 15 triệu','min' => 10000001,'max' => 15000000],
            '15-20' => ['label' => '15 - 20 triệu','min' => 15000001,'max' => 20000000],
            '20-25' => ['label' => '20 - 25 triệu','min' => 20000001,'max' => 25000000],
            '25-30' => ['label' => '25 - 30 triệu','min' => 25000001,'max' => 30000000],
            '30-plus' => ['label' => 'Trên 30 triệu','min' => 30000001,'max' => null],
        ];

        // --- Lấy tham số từ GET ---
        $currentPage = isset($_GET['pg']) ? max(1, (int)$_GET['pg']) : 1;
        $offset = ($currentPage - 1) * $itemsPerPage;

        // Lấy các filter hiện tại
        $currentPriceRangeKey = $_GET['price_range'] ?? 'all'; // 'all' là giá trị mặc định khi không lọc giá
        $currentFilters = [
            'search' => $_GET['search'] ?? '',
            'brand' => $_GET['brand'] ?? 'All',
            'price_range' => $currentPriceRangeKey, // Lưu key của khoảng giá đang chọn
            // Thêm color, size ở đây nếu DB có hỗ trợ
        ];

        // Mảng chứa các bộ lọc sạch để truyền vào Model
        $filters = [];
        if (!empty($currentFilters['search'])) {
            $filters['search'] = trim($currentFilters['search']);
        }
        if (!empty($currentFilters['brand']) && $currentFilters['brand'] !== 'All') {
            $filters['brand'] = $currentFilters['brand'];
        }

        // Xử lý khoảng giá được chọn
        if ($currentPriceRangeKey !== 'all' && isset($priceRangesMap[$currentPriceRangeKey])) {
            $range = $priceRangesMap[$currentPriceRangeKey];
            if ($range['min'] !== null) {
                $filters['min_price'] = $range['min'];
            }
            if ($range['max'] !== null) {
                $filters['max_price'] = $range['max'];
            }
        }
        // Thêm xử lý color, size nếu có


        // Lấy tùy chọn sắp xếp
        $sortOptions = ['created_at_desc', 'price_asc', 'price_desc', 'name_asc', 'name_desc', 'rating_desc'];
        $currentSort = $_GET['sort'] ?? 'created_at_desc';
        if (!in_array($currentSort, $sortOptions)) {
            $currentSort = 'created_at_desc';
        }


        // *** BỔ SUNG LẤY THÔNG TIN WISHLIST ***
        $isLoggedIn = isset($_SESSION['user_id']);
        $wishlistedIds = [];
        if ($isLoggedIn) {
            $wishlistedIds = Wishlist::getWishlistedProductIds($_SESSION['user_id']);
        }
        // *** KẾT THÚC BỔ SUNG ***


        // --- Gọi Model ---
        $availableBrands = Product::getDistinctBrands();
        // $priceRange = Product::getMinMaxPrice(); // Không cần lấy min/max tổng nữa
        $products = Product::getFilteredProducts($filters, $currentSort, $itemsPerPage, $offset);
        $totalProducts = Product::countFilteredProducts($filters);

        // --- Tính toán Phân trang ---
        $totalPages = ceil($totalProducts / $itemsPerPage);

        // --- Chuẩn bị dữ liệu cho View ---
        $data = [
            'products' => $products,
            'totalProducts' => $totalProducts,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'itemsPerPage' => $itemsPerPage,
            'availableBrands' => $availableBrands,
            // 'priceRange' => $priceRange, // Bỏ dòng này
            'currentFilters' => $currentFilters,
            'currentSort' => $currentSort,
            'sortOptionsMap' => [ // Đổi tên để rõ ràng hơn là map key=>label
                'created_at_desc'=> 'Mặc định (Mới nhất)',
                'price_asc'      => 'Giá: Thấp đến Cao',
                'price_desc'     => 'Giá: Cao đến Thấp',
                'name_asc'       => 'Tên: A-Z',
                'name_desc'      => 'Tên: Z-A',
                'rating_desc'    => 'Đánh giá cao nhất'
            ],
            'priceRangesMap' => $priceRangesMap, // Truyền map khoảng giá sang view
            'currentPriceRangeKey' => $currentPriceRangeKey, // Truyền key khoảng giá đang chọn
            // Thêm availableColors, availableSizes nếu có
            // *** Truyền biến wishlist sang view ***
            'isLoggedIn' => $isLoggedIn,
            'wishlistedIds' => $wishlistedIds
        ];


        // --- Render View ---
        $this->render('shop_grid', $data);
    }
}