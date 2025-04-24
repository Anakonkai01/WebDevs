<?php
require_once BASE_PATH . '/app/controllers/BaseController.php';
require_once BASE_PATH . '/app/models/Product.php';
require_once BASE_PATH . '/app/Models/Wishlist.php'; // Cần nếu index() có dùng

class HomeController extends BaseController
{
    /**
     * Hiển thị trang chủ với các chức năng:
     * - Tìm kiếm
     * - Lọc theo hãng
     * - Sản phẩm mới nhất
     * - Sản phẩm đánh giá cao
     * - Sản phẩm nhiều review nhất
     */
    public function index()
    {
        $search = $_GET['search'] ?? '';
        $brand  = $_GET['brand'] ?? '';

        // Lấy danh sách sản phẩm chính
        if (!empty($search)) {
            $products = Product::searchByName($search);
        } elseif (!empty($brand)) {
            $products = Product::getByBrand($brand);
        } else {
            $products = Product::getLatest(12);
        }


        // *** ADD THIS ***
        $isLoggedIn = isset($_SESSION['user_id']); // Check login status
        $wishlistedIds = []; // Initialize empty array
        if ($isLoggedIn) {
            // Make sure Wishlist model is included if not autoloaded
            require_once BASE_PATH . '/app/Models/Wishlist.php';
            $wishlistedIds = Wishlist::getWishlistedProductIds($_SESSION['user_id']);
        }
// *** END ADD ***

        // Các danh sách phụ cho homepage
        $latestProducts   = Product::getLatest(6);
        $topRatedProducts = Product::getTopRated(5);
        $mostReviewed     = Product::getMostReviewed(5);
        $brands           = Product::getDistinctBrands(); // <--- LẤY DANH SÁCH HÃNG

        // Render view home.php và truyền dữ liệu
        $this->render('home', [
            'products'       => $products,
            'latestProducts' => $latestProducts,
            'topRated'       => $topRatedProducts,
            'mostReviewed'   => $mostReviewed,
            'search'         => $search,
            'brand'          => $brand,
            'brands'         => $brands, // <--- TRUYỀN SANG VIEW
            'isLoggedIn' => $isLoggedIn,     // <-- Pass login status
            'wishlistedIds' => $wishlistedIds // <-- Pass wishlist IDs
        ]);
    }


    /**
     * Hiển thị trang liên hệ tĩnh
     */
    public function contact() {
        // Đối với trang tĩnh, thường không cần lấy dữ liệu từ Model
        // Chỉ cần render view và có thể truyền tiêu đề trang
        $this->render('contact', ['pageTitle' => 'Liên hệ']);
    }
}
