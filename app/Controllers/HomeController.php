<?php
namespace App\Controllers; 

use App\Models\Product;  

class HomeController extends BaseController
{
    public function index()
    {

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