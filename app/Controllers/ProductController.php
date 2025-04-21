<?php
require_once BASE_PATH . '/app/Models/Product.php';
require_once BASE_PATH . '/app/Controllers/BaseController.php';
class ProductController extends BaseController{
    public function index()
    {
        $product = (new Product())::all();
        $this->render('product', ['product' => $product]);
    }

    public function detail($id){
        $product = Product::find($id);

        if(!$product){
            echo "Product not found";
            return;
        }

        $this->render('product', ['product' => $product]);
    }
}