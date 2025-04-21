<?php
require_once BASE_PATH . '/app/controllers/BaseController.php';
require_once BASE_PATH . '/app/models/Product.php';

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
            'brands'         => $brands // <--- TRUYỀN SANG VIEW
        ]);
    }
}
