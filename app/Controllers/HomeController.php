<?php

namespace App\Controllers;

use App\Models\Product;

class HomeController extends BaseController
{
    
    public function index()
    {
        // Lấy sản phẩm nổi bật (ví dụ: 12 sản phẩm mới nhất)
        $featuredProducts = Product::getLatest(12);

        // Lấy 6 sản phẩm mới nhất cho sidebar
        $latestProducts = Product::getLatest(6);

        // Lấy 5 sản phẩm có rating cao nhất
        $topRatedProducts = Product::getTopRated(5);

        // Lấy 5 sản phẩm có nhiều review nhất
        $mostReviewed = Product::getMostReviewed(5);

        // Lấy các thương hiệu riêng biệt cho sidebar
        $brands = Product::getDistinctBrands();

        // Truyền dữ liệu vào view home
        $this->render('home', [
            'products'       => $featuredProducts,
            'latestProducts' => $latestProducts,
            'topRated'       => $topRatedProducts,
            'mostReviewed'   => $mostReviewed,
            'brands'         => $brands,]);
    }
    public function contact()
    {
        $this->render('contact', ['pageTitle' => 'Liên hệ']);
    }
}