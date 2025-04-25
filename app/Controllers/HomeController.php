<?php
namespace App\Controllers; // <--- Namespace

use App\Models\Product;   // <-- Use Model Product

class HomeController extends BaseController
{
    public function index()
    {
        // Bỏ phần xử lý search và brand ở đây, chỉ lấy sản phẩm nổi bật/mới nhất
        // $search = $_GET['search'] ?? ''; // XÓA DÒNG NÀY
        // $brand  = $_GET['brand'] ?? ''; // XÓA HOẶC GIỮ LẠI NẾU CẦN CHO LOGIC KHÁC

        // if (!empty($search)) { // XÓA KHỐI LỆNH NÀY
        //     $products = Product::searchByName($search);
        // } elseif (!empty($brand)) { // XÓA KHỐI LỆNH NÀY
        //     $products = Product::getByBrand($brand);
        // } else {
        //     $products = Product::getLatest(12); // Giữ lại hoặc thay đổi logic lấy sản phẩm nổi bật
        // }

        // Nên có một logic rõ ràng để lấy sản phẩm nổi bật cho trang chủ
        // Ví dụ: Lấy 12 sản phẩm mới nhất hoặc sản phẩm có rating cao nhất
        $featuredProducts = Product::getLatest(12); // Ví dụ: lấy 12 sản phẩm mới nhất làm nổi bật

        // Dữ liệu isLoggedIn và wishlistedIds sẽ được BaseController tự động thêm vào

        $latestProducts   = Product::getLatest(6); // Vẫn lấy SP mới cho widget sidebar
        $topRatedProducts = Product::getTopRated(5);
        $mostReviewed     = Product::getMostReviewed(5);
        $brands           = Product::getDistinctBrands(); // Vẫn lấy danh sách hãng cho sidebar

        $this->render('home', [
            'products'       => $featuredProducts, // Truyền sản phẩm nổi bật vào view
            'latestProducts' => $latestProducts,
            'topRated'       => $topRatedProducts,
            'mostReviewed'   => $mostReviewed,
            // 'search'         => $search, // BỎ search
            // 'brand'          => $brand, // BỎ brand nếu không dùng
            'brands'         => $brands, // Vẫn cần brands cho sidebar
            // isLoggedIn, wishlistedIds, wishlistItemCount, cartItemCount sẽ được tự động thêm
        ]);
    }

    public function contact() {
        $this->render('contact', ['pageTitle' => 'Liên hệ']);
    }
}