<?php
// Web/app/Controllers/CartController.php

namespace App\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;


class CartController extends BaseController {

    /**
     * Constructor: Ensures session is started and cart exists.
     */
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Thêm sản phẩm vào giỏ hàng (hỗ trợ AJAX từ product_detail) - Giữ nguyên
     */
    public function add(int $productId = null, int $quantity = null) {
        $isAjax = isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1;
        $productId = (int)($_REQUEST['id'] ?? 0);
        $quantity = (int)($_REQUEST['quantity'] ?? 1);
        $response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.', 'cartItemCount' => count($_SESSION['cart'] ?? [])];

        if ($quantity <= 0) { $response['message'] = 'Số lượng không hợp lệ.'; }
        elseif ($productId <= 0) { $response['message'] = 'ID sản phẩm không hợp lệ.'; }
        else {
            $product = Product::find($productId);
            if (!$product) { $response['message'] = 'Sản phẩm không tồn tại!'; }
            else {
                $currentQuantityInCart = $_SESSION['cart'][$productId]['quantity'] ?? 0;
                $requestedTotalQuantity = $currentQuantityInCart + $quantity;
                $stock = (int)$product['stock'];

                if ($stock < $requestedTotalQuantity) {
                    $canAdd = max(0, $stock - $currentQuantityInCart);
                    if ($stock <= 0) { $response['message'] = 'Sản phẩm "' . htmlspecialchars($product['name']) . '" đã hết hàng.'; }
                    elseif ($canAdd > 0) {
                        $_SESSION['cart'][$productId]['quantity'] = $currentQuantityInCart + $canAdd;
                        $response['success'] = true;
                        $response['message'] = 'Chỉ còn '.$stock.' sản phẩm "' . htmlspecialchars($product['name']) . '". Đã thêm tối đa '.$canAdd.' vào giỏ.';
                    } else { $response['message'] = 'Giỏ hàng đã có tối đa '.$currentQuantityInCart.' sản phẩm "' . htmlspecialchars($product['name']) . '", không thể thêm.'; }
                } else {
                    if (isset($_SESSION['cart'][$productId])) {
                        $_SESSION['cart'][$productId]['quantity'] = $requestedTotalQuantity;
                        $response['success'] = true;
                        $response['message'] = 'Đã cập nhật số lượng "' . htmlspecialchars($product['name']) . '" trong giỏ.';
                    } else {
                        $_SESSION['cart'][$productId] = [ 'id' => $productId, 'name' => $product['name'], 'price' => (float)$product['price'], 'image' => $product['image'], 'quantity' => $quantity ];
                        $response['success'] = true;
                        $response['message'] = 'Đã thêm "' . htmlspecialchars($product['name']) . '" vào giỏ hàng!';
                    }
                    // Luôn cập nhật lại thông tin sản phẩm (giá, ảnh) phòng trường hợp admin thay đổi
                    $_SESSION['cart'][$productId]['price'] = (float)$product['price'];
                    $_SESSION['cart'][$productId]['image'] = $product['image'];
                    $_SESSION['cart'][$productId]['name'] = $product['name']; // Cập nhật tên nếu cần
                }
            }
        }

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
    public function index() {
        $cartItems = $_SESSION['cart'] ?? [];
        $totalPrice = $this->calculateCartTotal(); // Dùng hàm helper
        $flashMessage = $_SESSION['flash_message'] ?? null;
        if ($flashMessage) { unset($_SESSION['flash_message']); }
        $this->render('cart', [
            'cartItems' => $cartItems,
            'totalPrice' => $totalPrice, // Tổng tiền của TOÀN BỘ giỏ hàng
            'flashMessage' => $flashMessage,
            'pageTitle' => 'Giỏ hàng'
        ]);
    }


    public function update() {
        $isAjax = isset($_POST['ajax']) && $_POST['ajax'] == 1;

        // --- Xử lý AJAX (Cập nhật từng item) ---
        if ($isAjax && isset($_POST['itemId']) && isset($_POST['quantity'])) {
            header('Content-Type: application/json; charset=utf-8');
            ob_clean();

            $productId = (int)$_POST['itemId'];
            $quantity = (int)$_POST['quantity'];

            // Cấu trúc response mặc định
            $response = [
                'success' => false, 'message' => 'Lỗi không xác định.',
                'itemId' => $productId, 'newQuantity' => null, 'itemSubtotal' => 0,
                'totalPrice' => $this->calculateCartTotal(), // Lấy tổng tiền hiện tại
                'removed' => false
            ];

            try {
                // Kiểm tra item tồn tại trong giỏ
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
                $response = ['success' => false, 'message' => 'Lỗi server khi cập nhật giỏ hàng.', 'itemId' => $productId];
                // http_response_code(500); // Có thể set 500 cho lỗi server
            }

            // Trả về JSON và dừng script
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }
        // --- Kết thúc xử lý AJAX ---


        // --- Xử lý NON-AJAX (Form submit với mảng quantities - Giữ nguyên logic cũ) ---
        elseif (!$isAjax && isset($_POST['quantities']) && is_array($_POST['quantities'])) {
            $updated = false;
            $errorMessages = [];
            $quantities = $_POST['quantities'];

            foreach ($quantities as $productId => $quantity) {
                $productId = (int)$productId;
                $quantity = (int)$quantity;
                if (!isset($_SESSION['cart'][$productId])) continue; // Bỏ qua nếu item không có trong giỏ

                if ($quantity <= 0) {
                    unset($_SESSION['cart'][$productId]);
                    $updated = true;
                    continue;
                }

                // Kiểm tra tồn kho
                $product = Product::find($productId);
                if ($product && $product['stock'] < $quantity) {
                    $errorMessages[] = 'Không đủ hàng cho "' . htmlspecialchars($_SESSION['cart'][$productId]['name']) . '". Tối đa: ' . $product['stock'] . '. Số lượng chưa được cập nhật.';
                    // Giữ nguyên số lượng cũ hoặc đặt tối đa? => Hiện tại là giữ nguyên
                } else if ($product) {
                    // Chỉ cập nhật nếu số lượng thay đổi
                    if ($_SESSION['cart'][$productId]['quantity'] != $quantity) {
                        $_SESSION['cart'][$productId]['quantity'] = $quantity;
                        // Cập nhật lại thông tin sản phẩm phòng trường hợp thay đổi
                        $_SESSION['cart'][$productId]['price'] = (float)$product['price'];
                        $_SESSION['cart'][$productId]['image'] = $product['image'];
                        $_SESSION['cart'][$productId]['name'] = $product['name'];
                        $updated = true;
                    }
                } else {
                    // Sản phẩm không tồn tại trong DB -> Xóa khỏi giỏ
                    unset($_SESSION['cart'][$productId]);
                    $errorMessages[] = 'Sản phẩm "' . htmlspecialchars($_SESSION['cart'][$productId]['name']) . '" không còn tồn tại và đã bị xóa khỏi giỏ.';
                    $updated = true; // Coi như có cập nhật
                }
            }

            // Đặt flash message
            if (!empty($errorMessages)) {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => implode('<br>', $errorMessages)];
            } elseif ($updated) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Giỏ hàng đã được cập nhật.'];
            }
        }
        // --- Kết thúc xử lý NON-AJAX ---


        // Nếu không phải AJAX hợp lệ hoặc là non-AJAX -> Chuyển hướng về giỏ hàng
        $this->redirect('?page=cart');
        exit;
    }


    /**
     * Removes a product from the cart (supports AJAX). 
     */
    public function remove() {
        $isAjax = isset($_GET['ajax']) && $_GET['ajax'] == 1;
        $response = ['success' => false, 'message' => 'Thiếu ID sản phẩm.', 'totalPrice' => 0, 'itemCount' => count($_SESSION['cart'] ?? [])];
        $productId = $_GET['id'] ?? null;

        if ($productId) {
            $productId = (int)$productId;
            if (isset($_SESSION['cart'][$productId])) {
                $productName = $_SESSION['cart'][$productId]['name'];
                try {
                    unset($_SESSION['cart'][$productId]);
                    $totalPrice = $this->calculateCartTotal(); // Dùng helper
                    $itemCount = count($_SESSION['cart']);
                    $response = [
                        'success' => true,
                        'message' => 'Đã xóa "' . htmlspecialchars($productName) . '" khỏi giỏ hàng.',
                        'totalPrice' => $totalPrice,
                        'itemCount' => $itemCount
                    ];
                    if (!$isAjax) { $_SESSION['flash_message'] = ['type' => 'success', 'message' => $response['message']]; }

                } catch (\Exception $e) {
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

    /**
     * Adds items from a previous order back to the current cart. 
     */
    public function reorder() {
        if (!isset($_SESSION['user_id'])) { $this->redirect('?page=login'); return; }
        $userId = $_SESSION['user_id'];
        $orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$orderId) { $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID đơn hàng không hợp lệ.']; $this->redirect('?page=order_history'); return; }
        $order = Order::find($orderId);
        if (!$order || $order['user_id'] != $userId) { $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Không tìm thấy hoặc không có quyền đặt lại đơn hàng này.']; $this->redirect('?page=order_history'); return; }
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
     * Tính tổng giá trị của toàn bộ giỏ hàng hiện tại trong session.
     * @return float Tổng giá trị.
     */
    private function calculateCartTotal(): float {
        $totalPrice = 0;
        foreach ($_SESSION['cart'] ?? [] as $item) {
            $totalPrice += (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 0);
        }
        return $totalPrice;
    }

    /**
     * Xử lý việc xóa item khỏi giỏ hàng cho AJAX request.
     * Cập nhật trực tiếp vào $response.
     * @param int $productId ID sản phẩm cần xóa.
     * @param array &$response Mảng response sẽ được trả về cho client.
     * @return void
     */
    private function handleAjaxItemRemoval(int $productId, array &$response): void {
        if (isset($_SESSION['cart'][$productId])) {
            $productName = $_SESSION['cart'][$productId]['name']; // Lấy tên trước khi xóa
            unset($_SESSION['cart'][$productId]);
            $response['success'] = true;
            $response['message'] = 'Đã xóa sản phẩm "'.htmlspecialchars($productName).'" khỏi giỏ.';
            $response['removed'] = true;
            $response['newQuantity'] = 0;
            $response['itemSubtotal'] = 0;
            $response['totalPrice'] = $this->calculateCartTotal(); // Tính lại tổng tiền
        } else {
            // Trường hợp này ít xảy ra nếu đã check tồn tại trước đó
            $response['message'] = 'Sản phẩm không có trong giỏ để xóa.';
        }
    }

    /**
     * Xử lý việc cập nhật số lượng item cho AJAX request.
     * Bao gồm kiểm tra sản phẩm, kiểm tra tồn kho, cập nhật session và tính toán giá.
     * Cập nhật trực tiếp vào $response.
     * @param int $productId ID sản phẩm.
     * @param int $quantity Số lượng mới.
     * @param array &$response Mảng response sẽ được trả về cho client.
     * @return void
     */
    private function handleAjaxItemUpdate(int $productId, int $quantity, array &$response): void {
        $product = Product::find($productId);

        if (!$product) {
            // Sản phẩm không tồn tại trong DB -> Xóa khỏi giỏ
            $this->handleAjaxItemRemoval($productId, $response); // Gọi hàm xóa
            $response['message'] = 'Sản phẩm không tồn tại và đã được xóa khỏi giỏ.'; // Ghi đè message xóa
            return;
        }

        // Kiểm tra tồn kho
        $currentStock = (int)$product['stock'];
        $adjustedQuantity = $quantity; // Số lượng sẽ được cập nhật vào session

        if ($currentStock < $quantity) {
            // Không đủ hàng
            $adjustedQuantity = max(0, $currentStock); // Số lượng tối đa có thể set
            if ($adjustedQuantity > 0) {
                $response['success'] = true; // Vẫn coi là thành công (đã điều chỉnh)
                $response['message'] = 'Không đủ hàng! Chỉ còn ' . $currentStock . '. Số lượng đã được điều chỉnh.';
                $response['newQuantity'] = $adjustedQuantity;
            } else {
                // Hết sạch hàng -> Xóa khỏi giỏ
                $this->handleAjaxItemRemoval($productId, $response);
                $response['message'] = 'Sản phẩm đã hết hàng và được xóa khỏi giỏ.';
                return; // Dừng ở đây sau khi xóa
            }
        } else {
            // Đủ hàng
            $response['success'] = true;
            $response['message'] = 'Đã cập nhật số lượng.';
            $response['newQuantity'] = $adjustedQuantity;
        }

        // Cập nhật session cart với số lượng đã điều chỉnh (adjustedQuantity)
        $_SESSION['cart'][$productId]['quantity'] = $adjustedQuantity;
        // Cập nhật thông tin sản phẩm phòng trường hợp thay đổi
        $_SESSION['cart'][$productId]['price'] = (float)$product['price'];
        $_SESSION['cart'][$productId]['image'] = $product['image'];
        $_SESSION['cart'][$productId]['name'] = $product['name'];

        // Tính toán lại subtotal và total
        $itemPrice = (float)$product['price'];
        $response['itemSubtotal'] = $itemPrice * $adjustedQuantity;
        $response['totalPrice'] = $this->calculateCartTotal();
    }

} 