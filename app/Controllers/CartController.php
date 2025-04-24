<?php
// Web/app/Controllers/CartController.php

require_once BASE_PATH . '/app/Controllers/BaseController.php';
require_once BASE_PATH . '/app/Models/Product.php'; // Cần Product model
require_once BASE_PATH . '/app/Models/Order.php';   // Cho reorder
require_once BASE_PATH . '/app/Models/OrderItem.php'; // Cho reorder

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
     * Thêm sản phẩm vào giỏ hàng (hỗ trợ AJAX từ product_detail)
     *
     * @param int|null $productId (Không dùng trực tiếp, lấy từ POST/GET)
     * @param int|null $quantity (Không dùng trực tiếp, lấy từ POST/GET)
     */
    public function add(int $productId = null, int $quantity = null) { // Tham số có thể không cần thiết nếu luôn lấy từ POST/GET

        $isAjax = isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1; // Chấp nhận ajax từ GET hoặc POST
        // Lấy ID và số lượng từ POST (cho AJAX) hoặc GET (cho link thường)
        $productId = (int)($_REQUEST['id'] ?? 0);
        $quantity = (int)($_REQUEST['quantity'] ?? 1);

        // Chuẩn bị phản hồi JSON mặc định
        $response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.', 'cartItemCount' => count($_SESSION['cart'] ?? [])];

        // --- Validation ---
        if ($quantity <= 0) {
            $response['message'] = 'Số lượng không hợp lệ.';
        } elseif ($productId <= 0) {
            $response['message'] = 'ID sản phẩm không hợp lệ.';
        } else {
            $product = Product::find($productId);
            if (!$product) {
                $response['message'] = 'Sản phẩm không tồn tại!';
            } else {
                $currentQuantityInCart = $_SESSION['cart'][$productId]['quantity'] ?? 0;
                $requestedTotalQuantity = $currentQuantityInCart + $quantity;
                $stock = (int)$product['stock'];

                if ($stock < $requestedTotalQuantity) {
                    $canAdd = max(0, $stock - $currentQuantityInCart); // Số lượng có thể thêm vào
                    if ($stock <= 0) {
                        $response['message'] = 'Sản phẩm "' . htmlspecialchars($product['name']) . '" đã hết hàng.';
                    } elseif ($canAdd > 0) {
                        // Chỉ thêm số lượng còn lại
                        $_SESSION['cart'][$productId]['quantity'] = $currentQuantityInCart + $canAdd;
                        $response['success'] = true; // Vẫn thành công (thêm được 1 phần)
                        $response['message'] = 'Chỉ còn '.$stock.' sản phẩm "' . htmlspecialchars($product['name']) . '". Đã thêm tối đa '.$canAdd.' vào giỏ.';
                    } else {
                        $response['message'] = 'Giỏ hàng đã có tối đa '.$currentQuantityInCart.' sản phẩm "' . htmlspecialchars($product['name']) . '", không thể thêm.';
                    }
                } else {
                    // --- Thêm hoặc cập nhật giỏ hàng ---
                    if (isset($_SESSION['cart'][$productId])) {
                        $_SESSION['cart'][$productId]['quantity'] = $requestedTotalQuantity;
                        $response['success'] = true;
                        $response['message'] = 'Đã cập nhật số lượng "' . htmlspecialchars($product['name']) . '" trong giỏ.';
                    } else {
                        $_SESSION['cart'][$productId] = [
                            'id'        => $productId,
                            'name'      => $product['name'],
                            'price'     => (float)$product['price'],
                            'image'     => $product['image'],
                            'quantity'  => $quantity // Số lượng ban đầu thêm vào
                        ];
                        $response['success'] = true;
                        $response['message'] = 'Đã thêm "' . htmlspecialchars($product['name']) . '" vào giỏ hàng!';
                    }
                }
            }
        }

        // Cập nhật số lượng item trong giỏ hàng cho phản hồi JSON
        $response['cartItemCount'] = count($_SESSION['cart'] ?? []);

        // --- Trả về kết quả ---
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            ob_clean(); // Clean buffer
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit; // Dừng script cho AJAX
        } else {
            // Request thường: đặt flash message và chuyển hướng về trang giỏ hàng
            $_SESSION['flash_message'] = ['type' => $response['success'] ? 'success' : 'error', 'message' => $response['message']];
            $this->redirect('?page=cart'); // Chuyển hướng về trang giỏ hàng như cũ
            exit;
        }
    }

    /**
     * Displays the cart page.
     */
    public function index() {
        $cartItems = $_SESSION['cart'] ?? [];
        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $price = isset($item['price']) ? (float)$item['price'] : 0;
            $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
            $totalPrice += $price * $quantity;
        }
        $flashMessage = $_SESSION['flash_message'] ?? null;
        if ($flashMessage) { unset($_SESSION['flash_message']); }
        $this->render('cart', [
            'cartItems' => $cartItems,
            'totalPrice' => $totalPrice,
            'flashMessage' => $flashMessage,
            'pageTitle' => 'Giỏ hàng' // Added pageTitle
        ]);
    }

    /**
     * Updates product quantity in the cart (supports AJAX).
     */
    public function update() {
        $isAjax = isset($_POST['ajax']) && $_POST['ajax'] == 1;
        // Default response structure
        $response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.', 'totalPrice' => 0, 'itemSubtotal' => 0, 'itemId' => null, 'newQuantity' => null, 'removed' => false];

        // Only process POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                $response['message'] = 'Phương thức không hợp lệ.';
                echo json_encode($response);
                exit;
            } else {
                $this->redirect('?page=cart');
                exit;
            }
        }

        // --- Start AJAX Handling (Single Item Update) ---
        if ($isAjax && isset($_POST['itemId']) && isset($_POST['quantity'])) {
            header('Content-Type: application/json; charset=utf-8'); // Set header with UTF-8 early
            ob_clean(); // Clean output buffer

            $productId = (int)$_POST['itemId'];
            $quantity = (int)$_POST['quantity'];

            // Prepare response structure
            $response = [
                'success' => false, 'message' => 'Lỗi mặc định khi cập nhật.',
                'itemId' => $productId, 'newQuantity' => $quantity, 'itemSubtotal' => 0,
                'totalPrice' => 0, 'removed' => false
            ];

            try {
                $cartItemExists = isset($_SESSION['cart'][$productId]);

                if (!$cartItemExists) {
                    $response['message'] = 'Sản phẩm không có trong giỏ.';
                } elseif ($quantity <= 0) {
                    unset($_SESSION['cart'][$productId]);
                    $response['success'] = true;
                    $response['message'] = 'Đã xóa sản phẩm khỏi giỏ.';
                    $response['removed'] = true;
                } else {
                    $product = Product::find($productId); // Find product details

                    if (!$product) { // Product doesn't exist in DB
                        unset($_SESSION['cart'][$productId]);
                        $response['success'] = true;
                        $response['message'] = 'Sản phẩm không tồn tại và đã được xóa khỏi giỏ.';
                        $response['removed'] = true;
                    } else {
                        $currentStock = (int)$product['stock'];
                        if ($currentStock < $quantity) { // Not enough stock
                            $adjustedQuantity = max(0, $currentStock);
                            if ($adjustedQuantity > 0) {
                                $_SESSION['cart'][$productId]['quantity'] = $adjustedQuantity;
                                $response['success'] = true;
                                $response['message'] = 'Không đủ hàng! Chỉ còn ' . $currentStock . '. Số lượng đã được điều chỉnh.';
                                $response['newQuantity'] = $adjustedQuantity;
                            } else { // Stock is zero
                                unset($_SESSION['cart'][$productId]);
                                $response['success'] = true;
                                $response['message'] = 'Sản phẩm đã hết hàng và được xóa khỏi giỏ.';
                                $response['removed'] = true;
                                $response['newQuantity'] = 0;
                            }
                        } else { // Enough stock
                            $_SESSION['cart'][$productId]['quantity'] = $quantity;
                            $response['success'] = true;
                            $response['message'] = 'Đã cập nhật số lượng.';
                            $response['newQuantity'] = $quantity;
                        }

                        // Calculate item subtotal IF item was updated (not removed)
                        if ($response['success'] && !$response['removed']) {
                            $itemPrice = (float)($_SESSION['cart'][$productId]['price'] ?? $product['price'] ?? 0);
                            $response['itemSubtotal'] = $itemPrice * $response['newQuantity'];
                        }
                    }
                }

                // Recalculate total cart price if any change was successful
                if ($response['success']) {
                    $totalPrice = 0;
                    foreach ($_SESSION['cart'] as $item) {
                        $totalPrice += (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 0);
                    }
                    $response['totalPrice'] = $totalPrice;
                }

            } catch (\Exception $e) {
                error_log("Cart Update Exception: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
                $response = [
                    'success' => false,
                    'message' => 'Lỗi server khi cập nhật giỏ hàng. Chi tiết đã được ghi lại.',
                    'itemId' => $productId,
                ];
                // Consider setting a 500 status code for server errors
                // http_response_code(500);
            }

            // Echo JSON response and exit for AJAX
            echo json_encode($response, JSON_UNESCAPED_UNICODE); // Ensure UTF-8 characters are handled
            exit;

            // --- END AJAX Handling ---

            // --- Start NON-AJAX Handling (Form Submit) ---
        } elseif (!$isAjax && isset($_POST['quantities']) && is_array($_POST['quantities'])) {
            $updated = false; $errorMessages = [];
            $quantities = $_POST['quantities'];
            foreach ($quantities as $productId => $quantity) {
                $productId = (int)$productId; $quantity = (int)$quantity; if (!isset($_SESSION['cart'][$productId])) continue;
                if ($quantity <= 0) { unset($_SESSION['cart'][$productId]); $updated = true; continue; }
                $product = Product::find($productId);
                if ($product && $product['stock'] < $quantity) { $errorMessages[] = 'Không đủ hàng cho "' . htmlspecialchars($_SESSION['cart'][$productId]['name']) . '". Tối đa: ' . $product['stock'] . '. Số lượng chưa được cập nhật.'; }
                else if ($product) { if ($_SESSION['cart'][$productId]['quantity'] != $quantity) { $_SESSION['cart'][$productId]['quantity'] = $quantity; $updated = true; } }
            }
            if (!empty($errorMessages)) { $_SESSION['flash_message'] = ['type' => 'error', 'message' => implode('<br>', $errorMessages)]; }
            elseif ($updated) { $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Giỏ hàng đã được cập nhật.']; }
        }
        // --- END NON-AJAX Handling ---

        // Redirect for non-AJAX request or if it wasn't a valid AJAX POST
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
                    unset($_SESSION['cart'][$productId]); // Remove item

                    // Recalculate total and count
                    $totalPrice = 0;
                    foreach ($_SESSION['cart'] as $item) {
                        $totalPrice += (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 0);
                    }
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

        // --- Return Result ---
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            ob_clean();
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            $this->redirect('?page=cart');
            exit;
        }
    }

    /**
     * Adds items from a previous order back to the current cart.
     */
    public function reorder() {
        if (!isset($_SESSION['user_id'])) { $this->redirect('?page=login'); return; }
        $userId = $_SESSION['user_id'];
        $orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$orderId) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID đơn hàng không hợp lệ để đặt lại.'];
            $this->redirect('?page=order_history'); return;
        }
        $order = Order::find($orderId);
        if (!$order || $order['user_id'] != $userId) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền đặt lại đơn hàng này.'];
            $this->redirect('?page=order_history'); return;
        }
        $orderItems = OrderItem::getItemsByOrder($orderId);
        if (empty($orderItems)) {
            $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'Đơn hàng này không có sản phẩm nào để đặt lại.'];
            $this->redirect('?page=order_history'); return;
        }

        $addedItems = []; $failedItems = []; $cart = $_SESSION['cart'] ?? [];
        foreach ($orderItems as $item) {
            $productId = $item['product_id'];
            $quantityToAdd = (int)$item['quantity'];
            $product = Product::find($productId); // Check current product status

            if (!$product) { $failedItems[] = "Sản phẩm ID {$productId} không còn tồn tại."; continue; }

            $productName = $product['name'];
            $currentStock = (int)$product['stock'];
            $quantityInCart = isset($cart[$productId]) ? (int)$cart[$productId]['quantity'] : 0;
            $totalQuantityNeeded = $quantityInCart + $quantityToAdd;

            if ($currentStock <= 0) { // Completely out of stock
                $failedItems[] = "\"{$productName}\" (Hết hàng)";
                continue;
            }

            if ($currentStock < $totalQuantityNeeded) { // Not enough stock for full amount
                $canAdd = max(0, $currentStock - $quantityInCart); // How many more can be added?
                if ($canAdd > 0) {
                    $cart[$productId] = [ 'id' => $productId, 'name' => $productName, 'price' => (float)$product['price'], 'image' => $product['image'], 'quantity' => $quantityInCart + $canAdd ];
                    $addedItems[] = "\"{$productName}\" (Đã thêm {$canAdd}, không đủ {$quantityToAdd})";
                } else {
                    $failedItems[] = "\"{$productName}\" (Không thể thêm, đã có {$quantityInCart}/{$currentStock} trong giỏ)";
                }
            } else { // Enough stock
                if (isset($cart[$productId])) {
                    $cart[$productId]['quantity'] += $quantityToAdd; // Update quantity
                } else {
                    $cart[$productId] = [ 'id' => $productId, 'name' => $productName, 'price' => (float)$product['price'], 'image' => $product['image'], 'quantity' => $quantityToAdd ]; // Add new
                }
                $addedItems[] = "\"{$productName}\" (SL: {$quantityToAdd})";
            }
        }
        $_SESSION['cart'] = $cart;
        $successMsg = !empty($addedItems) ? 'Đã thêm vào giỏ: ' . implode(', ', $addedItems) . '.' : '';
        $errorMsg = !empty($failedItems) ? ' Không thể thêm: ' . implode(', ', $failedItems) . '.' : '';
        $_SESSION['flash_message'] = ['type' => 'info', 'message' => trim($successMsg . $errorMsg)];
        $this->redirect('?page=cart');
    }
}