<?php

namespace App\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
class CartController extends BaseController
{
    public function __construct()
    {
        // Check if a session is active
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize cart if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Adds a product to the cart.
     */
    public function add(int $productId = null, int $quantity = null) {
        $isAjax = isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1;
        $productId = (int)($_REQUEST['id'] ?? 0);
        $quantity = (int)($_REQUEST['quantity'] ?? 1);
        $response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.', 'cartItemCount' => count($_SESSION['cart'] ?? [])];
        if ($quantity <= 0) { $response['message'] = 'Invalid quantity.'; }
        elseif ($productId <= 0) { $response['message'] = 'Invalid product ID.'; }
        else {
            $product = Product::find($productId);
            if (!$product) { $response['message'] = 'Product does not exist!'; }
            else {
                $currentQuantityInCart = $_SESSION['cart'][$productId]['quantity'] ?? 0;
                $requestedTotalQuantity = $currentQuantityInCart + $quantity;
                $stock = (int)$product['stock'];
                if ($stock < $requestedTotalQuantity) {
                    $canAdd = max(0, $stock - $currentQuantityInCart);
                    if ($stock <= 0) {
                        $response['message'] = 'Product "' . htmlspecialchars($product['name']) . '" is out of stock.';
                    }
                    elseif ($canAdd > 0) {
                        $_SESSION['cart'][$productId]['quantity'] = $currentQuantityInCart + $canAdd;
                        $response['success'] = true;
                        $response['message'] = 'Only '.$stock.' products left "' . htmlspecialchars($product['name']) . '". Added maximum of '.$canAdd.' to cart.';
                    } else {
                        $response['message'] = 'Cart already has maximum of '.$currentQuantityInCart.' product "' . htmlspecialchars($product['name']) . '", cannot add more.';
                    }
                } else {
                    if (isset($_SESSION['cart'][$productId])) {
                        $_SESSION['cart'][$productId]['quantity'] = $requestedTotalQuantity;
                        $response['success'] = true;
                        $response['message'] = 'Updated quantity "' . htmlspecialchars($product['name']) . '" in cart.';
                    } else {
                        $_SESSION['cart'][$productId] = [ 'id' => $productId, 'name' => $product['name'], 'price' => (float)$product['price'], 'image' => $product['image'], 'quantity' => $quantity ];
                        $response['success'] = true;
                        $response['message'] = 'Added "' . htmlspecialchars($product['name']) . '" to cart!';
                    }

                    // Update product info
                    $_SESSION['cart'][$productId]['price'] = (float)$product['price'];
                    $_SESSION['cart'][$productId]['image'] = $product['image'];
                    $_SESSION['cart'][$productId]['name'] = $product['name'];
                }
            }
        }
        //update total number
        $response['cartItemCount'] = count($_SESSION['cart'] ?? []);

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            ob_clean(); echo json_encode($response, JSON_UNESCAPED_UNICODE); exit;
        } else {
            $_SESSION['flash_message'] = ['type' => $response['success'] ? 'success' : 'error', 'message' => $response['message']];
            $this->redirect('?page=cart'); exit;
        }
    }

    /**
     * Displays the cart page.
     */
    public function index()
    {
        $cartItems = $_SESSION['cart'] ?? [];
        $totalPrice = $this->calculateCartTotal();
        $flashMessage = $_SESSION['flash_message'] ?? null;
        if ($flashMessage) {
            unset($_SESSION['flash_message']);
        }
        $this->render('cart', [
            'cartItems' => $cartItems,
            'totalPrice' => $totalPrice,
            'flashMessage' => $flashMessage,
            'pageTitle' => 'Cart'
        ]);
    }
    /**
     * Updates the quantities of items in the cart.
     */
    public function update() {
        $isAjax = isset($_POST['ajax']) && $_POST['ajax'] == 1;
        if ($isAjax && isset($_POST['itemId']) && isset($_POST['quantity'])) {
            header('Content-Type: application/json; charset=utf-8');
            ob_clean();

            $productId = (int)$_POST['itemId'];
            $quantity = (int)$_POST['quantity'];

            //default response
            $response = [
                'success' => false, 'message' => 'Unknown error.',
                'itemId' => $productId, 'newQuantity' => null, 'itemSubtotal' => 0,
                'totalPrice' => $this->calculateCartTotal(),
                'removed' => false
            ];
            try {
                //check item
                if (!isset($_SESSION['cart'][$productId])) {
                    $response['message'] = 'Sản phẩm không có trong giỏ.';
                } elseif ($quantity <= 0) {
                    // Xử lý xóa item nếu số lượng <= 0
                    $this->handleAjaxItemRemoval($productId, $response);
                } else {
                    // Xử lý cập nhật số lượng
                    $this->handleAjaxItemUpdate($productId, $quantity, $response);
                }
            } catch (\Exception $e) {
                error_log("Cart Update Exception: " . $e->getMessage());
                $response = ['success' => false, 'message' => 'Error server update cart.', 'itemId' => $productId];
            }
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        // --- Xử lý NON-AJAX (Form submit với mảng quantities) ---
        elseif (!$isAjax && isset($_POST['quantities']) && is_array($_POST['quantities'])) {
            $updated = false;
            $errorMessages = [];
            $quantities = $_POST['quantities'];

            foreach ($quantities as $productId => $quantity) {
                $productId = (int)$productId;
                $quantity = (int)$quantity; //convert
                if (!isset($_SESSION['cart'][$productId])) continue;
                if ($quantity <= 0) {
                    unset($_SESSION['cart'][$productId]);
                    $updated = true;
                    continue;
                }
                $product = Product::find($productId);
                if ($product && $product['stock'] < $quantity) {
                    $errorMessages[] = 'Not enough stock for "' . htmlspecialchars($_SESSION['cart'][$productId]['name']) . '". Maximum: ' . $product['stock'] . '. Quantity not updated.';
                } else if ($product) {
                    if ($_SESSION['cart'][$productId]['quantity'] != $quantity) {
                        $_SESSION['cart'][$productId]['quantity'] = $quantity;
                        $_SESSION['cart'][$productId]['price'] = (float)$product['price'];
                        $_SESSION['cart'][$productId]['image'] = $product['image'];
                        $updated = true;
                    }
                } else {
                    unset($_SESSION['cart'][$productId]);
                    $errorMessages[] = 'Product "' . htmlspecialchars($_SESSION['cart'][$productId]['name']) . '" no longer exists and has been removed from cart.';
                    $updated = true;
                }
            }
            if (!empty($errorMessages)) {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => implode('<br>', $errorMessages)];
            } elseif ($updated) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cart updated.'];
            }
        }

        // Redirect to cart
        $this->redirect('?page=cart');
        exit;
    }

    /**
     * Removes a product from the cart.
     */
    public function remove()
    {
        $isAjax = isset($_GET['ajax']) && $_GET['ajax'] == 1;
        $response = ['success' => false, 'message' => 'Thiếu ID sản phẩm.', 'totalPrice' => 0, 'itemCount' => count($_SESSION['cart'] ?? [])];
        $productId = $_GET['id'] ?? null;
        if ($productId) {
            $productId = (int)$productId;
            if (isset($_SESSION['cart'][$productId])) {
                $productName = $_SESSION['cart'][$productId]['name'];
                try {
                    unset($_SESSION['cart'][$productId]);
                    $totalPrice = $this->calculateCartTotal();
                    $itemCount = count($_SESSION['cart']);
                    $response = [
                        'success' => true,
                        'message' => 'Đã xóa "' . htmlspecialchars($productName) . '" khỏi giỏ hàng.',
                        'totalPrice' => $totalPrice,
                        'itemCount' => $itemCount
                    ]; //update response
                    if (!$isAjax) { $_SESSION['flash_message'] = ['type' => 'success', 'message' => $response['message']]; }

                    error_log("Cart Remove Exception: " . $e->getMessage());
                    $response = ['success' => false, 'message' => 'Lỗi server khi xóa sản phẩm.', 'totalPrice' => $response['totalPrice'], 'itemCount' => $response['itemCount']];
                    if (!$isAjax) { $_SESSION['flash_message'] = ['type' => 'error', 'message' => $response['message']]; }
                }
            } else {
                $response['message'] = 'Sản phẩm không có trong giỏ hàng.';
                if (!$isAjax) { $_SESSION['flash_message'] = ['type' => 'error', 'message' => $response['message']]; }
            }
        } else {
            if (!$isAjax) { $_SESSION['flash_message'] = ['type' => 'error', 'message' => $response['message']]; }
        }

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            ob_clean(); echo json_encode($response, JSON_UNESCAPED_UNICODE); exit;
        } else {
            $this->redirect('?page=cart'); exit;
        }
    }

    /*
     * Reorders items from a previous order, adding them to the current cart.
     */
    public function reorder()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('?page=login');
            return;
        }
        $userId = $_SESSION['user_id'];
        $orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$orderId) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID đơn hàng không hợp lệ.'];
            $this->redirect('?page=order_history');
            return;
        }
        $order = Order::find($orderId);
        if (!$order || $order['user_id'] != $userId) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Không tìm thấy hoặc không có quyền đặt lại đơn hàng này.'];
            $this->redirect('?page=order_history');
            return;
        }

        $orderItems = OrderItem::getItemsByOrder($orderId);
        if (empty($orderItems)) { $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'Đơn hàng này không có sản phẩm nào để đặt lại.']; $this->redirect('?page=order_history'); return; }

        $addedItems = []; $failedItems = []; $cart = $_SESSION['cart'] ?? [];
        foreach ($orderItems as $item) {
            $productId = $item['product_id']; $quantityToAdd = (int)$item['quantity']; $product = Product::find($productId);
            if (!$product) { $failedItems[] = "Sản phẩm ID {$productId} không còn tồn tại."; continue; }
            $productName = $product['name']; $currentStock = (int)$product['stock']; $quantityInCart = isset($cart[$productId]) ? (int)$cart[$productId]['quantity'] : 0;
            $totalQuantityNeeded = $quantityInCart + $quantityToAdd;
            if ($currentStock <= 0) { $failedItems[] = "\"{$productName}\" (Hết hàng)"; continue; }
            if ($currentStock < $totalQuantityNeeded) {
                $canAdd = max(0, $currentStock - $quantityInCart);
                if ($canAdd > 0) {
                    $cart[$productId] = [ 'id' => $productId, 'name' => $productName, 'price' => (float)$product['price'], 'image' => $product['image'], 'quantity' => $quantityInCart + $canAdd ];
                    $addedItems[] = "\"{$productName}\" (Đã thêm {$canAdd}, không đủ {$quantityToAdd})";
                } else { $failedItems[] = "\"{$productName}\" (Không thể thêm, đã có {$quantityInCart}/{$currentStock} trong giỏ)"; }
            } else {
                if (isset($cart[$productId])) { $cart[$productId]['quantity'] += $quantityToAdd; }
                else { $cart[$productId] = [ 'id' => $productId, 'name' => $productName, 'price' => (float)$product['price'], 'image' => $product['image'], 'quantity' => $quantityToAdd ]; }
                $addedItems[] = "\"{$productName}\" (SL: {$quantityToAdd})";
            }
        }
        $_SESSION['cart'] = $cart;
        $successMsg = !empty($addedItems) ? 'Đã thêm vào giỏ: ' . implode(', ', $addedItems) . '.' : '';
        $errorMsg = !empty($failedItems) ? ' Không thể thêm: ' . implode(', ', $failedItems) . '.' : '';
        $_SESSION['flash_message'] = ['type' => 'info', 'message' => trim($successMsg . $errorMsg)];
        $this->redirect('?page=cart');
    }
    
    /**
     * Calculates the total price of the cart.
     */
    private function calculateCartTotal(): float
    {
        $totalPrice = 0;
        foreach ($_SESSION['cart'] ?? [] as $item){
            $totalPrice += (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 0);
        }
        return $totalPrice;
    }


    private function handleAjaxItemRemoval(int $productId, array &$response): void
    {
        if (isset($_SESSION['cart'][$productId])) {
            // Get the product name before removing it
            $productName = $_SESSION['cart'][$productId]['name'];
            unset($_SESSION['cart'][$productId]);
            // Update the response
            $response['success'] = true;
            $response['message'] = 'Đã xóa sản phẩm "'.htmlspecialchars($productName).'" khỏi giỏ.';
            $response['removed'] = true;
            $response['newQuantity'] = 0;
            $response['itemSubtotal'] = 0;
            $response['totalPrice'] = $this->calculateCartTotal(); // Tính lại tổng tiền
        } else {
            $response['message'] = 'Sản phẩm không có trong giỏ để xóa.';
            
        }
    }


    private function handleAjaxItemUpdate(int $productId, int $quantity, array &$response): void
    {
        $product = Product::find($productId);
        if (!$product) {
            $this->handleAjaxItemRemoval($productId, $response);
            $response['message'] = 'Product not exists.';
            return;
        }
        $currentStock = (int)$product['stock'];
        $adjustedQuantity = $quantity;
        if ($currentStock < $quantity) {
            $adjustedQuantity = max(0, $currentStock);
            if ($adjustedQuantity > 0) {
                $response['success'] = true;
                $response['message'] = 'Not enough! Only ' . $currentStock . ' left.';
                $response['newQuantity'] = $adjustedQuantity;
            } else {
                $this->handleAjaxItemRemoval($productId, $response);
                $response['message'] = 'Product out of stock.';
                return;
            }
        } else {
            $response['success'] = true;
            $response['message'] = 'Updated quantity.';
            $response['newQuantity'] = $adjustedQuantity;
        }
        
        //update session
        $_SESSION['cart'][$productId]['quantity'] = $adjustedQuantity;
        $_SESSION['cart'][$productId]['price'] = (float)$product['price'];
        $_SESSION['cart'][$productId]['image'] = $product['image'];
        $_SESSION['cart'][$productId]['name'] = $product['name'];

        // Tính toán lại subtotal và total
        $itemPrice = (float)$product['price'];
        $response['itemSubtotal'] = $itemPrice * $adjustedQuantity;
        $response['totalPrice'] = $this->calculateCartTotal();
    }

}