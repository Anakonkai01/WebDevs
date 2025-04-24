<?php
// Web/app/Controllers/ProductController.php

// Ensure necessary models and base controller are loaded
require_once BASE_PATH . '/app/Models/Product.php';
require_once BASE_PATH . '/app/Controllers/BaseController.php';
require_once BASE_PATH . '/app/Models/Review.php';
require_once BASE_PATH . '/app/Models/Wishlist.php'; // Make sure this line exists

class ProductController extends BaseController {

    /**
     * Constructor - Initialize session if needed.
     * (Optional: Can be removed if session is always started in index.php)
     */
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
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
            $this->render('errors/400', ['message' => 'ID sản phẩm không hợp lệ.']); // Assuming you have an error view
            // Or echo "<h2>400 - ID sản phẩm không hợp lệ</h2>";
            return;
        }

        // --- Get Product Data ---
        $product = Product::find($productId);

        // --- Check if Product Exists ---
        if (!$product) {
            http_response_code(404);
            $this->render('errors/404', ['message' => 'Không tìm thấy sản phẩm.']); // Assuming you have an error view
            // Or echo "<h2>404 - Không tìm thấy sản phẩm</h2>";
            return;
        }

        // --- Get Reviews ---
        $reviews = Review::getByProduct($productId);

        // --- Get Wishlist Status ---
        $isLoggedIn = isset($_SESSION['user_id']);
        $wishlistedIds = [];
        if ($isLoggedIn) {
            $wishlistedIds = Wishlist::getWishlistedProductIds((int)$_SESSION['user_id']); // Ensure user ID is int
            if (!is_array($wishlistedIds)) { // Add safety check
                error_log("Warning: Wishlist::getWishlistedProductIds did not return an array for user ID: " . $_SESSION['user_id']);
                $wishlistedIds = [];
            }
        }

        // --- Get Related Products (Example: Same Brand) ---
        $relatedProducts = [];
        if ($product && !empty($product['brand'])) {
            try {
                // Assuming Product::getByBrand fetches products by brand name
                $allRelated = Product::getByBrand($product['brand']);
                $count = 0;
                foreach ($allRelated as $relP) {
                    // Ensure related product ID is different and limit to 4
                    if ((int)$relP['id'] !== $productId && $count < 4) {
                        $relatedProducts[] = $relP;
                        $count++;
                    }
                    if ($count >= 4) break; // Stop once limit is reached
                }
            } catch (Exception $e) {
                error_log("Error fetching related products: " . $e->getMessage());
                $relatedProducts = []; // Reset on error
            }
        }

        // --- Prepare Data for View ---
        $data = [
            'product' => $product,
            'reviews' => $reviews,
            'isLoggedIn' => $isLoggedIn,         // Pass login status
            'wishlistedIds' => $wishlistedIds,     // Pass wishlist IDs array
            'relatedProducts' => $relatedProducts, // Pass related products
            'pageTitle' => $product['name'] ?? 'Chi tiết sản phẩm' // Set page title
        ];

        // --- Render Product Detail View ---
        $this->render('product_detail', $data);
    }

    /**
     * Displays the Shop Grid page with filtering, sorting, and pagination.
     */
    public function shopGrid() {
        // --- Configuration ---
        $itemsPerPage = 9; // Products per page

        // --- Define Price Ranges ---
        $priceRangesMap = [
            '0-1'     => ['label' => 'Dưới 1 triệu',  'min' => null, 'max' => 1000000],
            '1-5'     => ['label' => '1 - 5 triệu',   'min' => 1000000, 'max' => 5000000],
            '5-10'    => ['label' => '5 - 10 triệu',  'min' => 5000001, 'max' => 10000000],
            '10-15'   => ['label' => '10 - 15 triệu', 'min' => 10000001,'max' => 15000000],
            '15-20'   => ['label' => '15 - 20 triệu', 'min' => 15000001,'max' => 20000000],
            '20-25'   => ['label' => '20 - 25 triệu', 'min' => 20000001,'max' => 25000000],
            '25-30'   => ['label' => '25 - 30 triệu', 'min' => 25000001,'max' => 30000000],
            '30-plus' => ['label' => 'Trên 30 triệu','min' => 30000001,'max' => null],
        ];

        // --- Get Parameters from GET Request ---
        $currentPage = isset($_GET['pg']) ? max(1, (int)$_GET['pg']) : 1;
        $offset = ($currentPage - 1) * $itemsPerPage;

        // Get current filters
        $currentSearch = trim($_GET['search'] ?? '');
        $currentBrand = $_GET['brand'] ?? 'All';
        $currentPriceRangeKey = $_GET['price_range'] ?? 'all';

        // Prepare filters array for the model query
        $filters = [];
        if (!empty($currentSearch)) {
            $filters['search'] = $currentSearch;
        }
        if (!empty($currentBrand) && $currentBrand !== 'All') {
            $filters['brand'] = $currentBrand;
        }
        if ($currentPriceRangeKey !== 'all' && isset($priceRangesMap[$currentPriceRangeKey])) {
            $range = $priceRangesMap[$currentPriceRangeKey];
            if ($range['min'] !== null) $filters['min_price'] = $range['min'];
            if ($range['max'] !== null) $filters['max_price'] = $range['max'];
        }
        // Add other filters (color, size) here if needed

        // Get sorting option
        $sortOptions = ['created_at_desc', 'price_asc', 'price_desc', 'name_asc', 'name_desc', 'rating_desc'];
        $currentSort = $_GET['sort'] ?? 'created_at_desc';
        if (!in_array($currentSort, $sortOptions)) {
            $currentSort = 'created_at_desc'; // Default sort
        }

        // --- Get Wishlist Status for products on this page ---
        $isLoggedIn = isset($_SESSION['user_id']);
        $wishlistedIds = []; // Default to empty
        if ($isLoggedIn) {
            $wishlistedIds = Wishlist::getWishlistedProductIds((int)$_SESSION['user_id']); // Ensure user ID is int
            if (!is_array($wishlistedIds)) { // Safety check
                error_log("Warning: Wishlist::getWishlistedProductIds did not return an array for user ID: " . $_SESSION['user_id']);
                $wishlistedIds = [];
            }
        }

        // --- Fetch Data from Models ---
        $availableBrands = Product::getDistinctBrands(); // Get unique brand names
        $products = Product::getFilteredProducts($filters, $currentSort, $itemsPerPage, $offset); // Get products for the current page
        $totalProducts = (int)Product::countFilteredProducts($filters); // Get total count matching filters, ensure integer

        // --- Calculate Pagination ---
        $totalPages = ($itemsPerPage > 0 && $totalProducts > 0) ? ceil($totalProducts / $itemsPerPage) : 1; // Ensure totalPages >= 1

        // --- Prepare Data Array for the View ---
        $data = [
            'products' => $products,
            'totalProducts' => $totalProducts, // Integer
            'currentPage' => $currentPage, // Integer
            'totalPages' => $totalPages,   // Integer
            'itemsPerPage' => $itemsPerPage, // Integer
            'availableBrands' => $availableBrands,
            'currentFilters' => [ // Pass current filter values back to view
                'search' => $currentSearch,
                'brand' => $currentBrand,
                'price_range' => $currentPriceRangeKey,
            ],
            'currentSort' => $currentSort,
            'sortOptionsMap' => [ // Map for sorting dropdown labels
                'created_at_desc'=> 'Mặc định (Mới nhất)',
                'price_asc'      => 'Giá: Thấp đến Cao',
                'price_desc'     => 'Giá: Cao đến Thấp',
                'name_asc'       => 'Tên: A-Z',
                'name_desc'      => 'Tên: Z-A',
                'rating_desc'    => 'Đánh giá cao nhất'
            ],
            'priceRangesMap' => $priceRangesMap, // Pass price range definitions
            'currentPriceRangeKey' => $currentPriceRangeKey, // Pass current selected price key
            // Pass wishlist data
            'isLoggedIn' => $isLoggedIn,
            'wishlistedIds' => $wishlistedIds // Pass the array (even if empty)
        ];

        // --- Render the Shop Grid View ---
        $this->render('shop_grid', $data);
    }
}