<?php
// Web/app/Controllers/CartController.php

require_once BASE_PATH . '/app/Controllers/BaseController.php';
require_once BASE_PATH . '/app/Models/Product.php'; // Cần Product model để lấy thông tin SP

class CartController extends BaseController {

    public function __construct() {
        // Đảm bảo session đã được khởi động (thường ở index.php)
        if (session_status() == PHP_SESSION_NONE) {
            // Dòng này có thể không cần thiết nếu index.php luôn chạy session_start() trước
            // Nhưng thêm vào để chắc chắn
            session_start();
        }
        // Khởi tạo giỏ hàng trong session nếu chưa có
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = []; // Giỏ hàng là một mảng ['product_id' => [thông tin sản phẩm], ...]
        }
    }

    /**
     * Thêm sản phẩm vào giỏ hàng (lưu trong session)
     * @param int $productId ID sản phẩm
     * @param int $quantity Số lượng
     */
    public function add(int $productId, int $quantity = 1) {
        // --- Validation ---
        if ($quantity <= 0) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Số lượng không hợp lệ.'];
            // Quay lại trang trước đó (có thể dùng HTTP_REFERER nhưng không đáng tin cậy)
            // Tốt nhất là quay lại trang chi tiết nếu có thể
            $this->redirect('?page=product_detail&id=' . $productId);
            return;
        }

        $product = Product::find($productId);

        // Kiểm tra sản phẩm tồn tại
        if (!$product) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Sản phẩm không tồn tại!'];
            $this->redirect('?page=shop_grid'); // Chuyển về trang shop
            return;
        }

        // Kiểm tra số lượng tồn kho
        $currentQuantityInCart = $_SESSION['cart'][$productId]['quantity'] ?? 0; // Số lượng hiện có trong giỏ
        $requestedTotalQuantity = $currentQuantityInCart + $quantity; // Tổng số lượng muốn có

        if ($product['stock'] < $requestedTotalQuantity) {
            // Không đủ hàng
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Không đủ hàng! Chỉ còn ' . $product['stock'] . ' sản phẩm "' . htmlspecialchars($product['name']) . '".'];
            // Quay lại trang chi tiết
            $this->redirect('?page=product_detail&id=' . $productId);
            return;
        }

        // --- Thêm hoặc cập nhật giỏ hàng ---
        if (isset($_SESSION['cart'][$productId])) {
            // Sản phẩm đã có -> cập nhật số lượng
            $_SESSION['cart'][$productId]['quantity'] = $requestedTotalQuantity;
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đã cập nhật số lượng "' . htmlspecialchars($product['name']) . '".'];

        } else {
            // Sản phẩm chưa có -> thêm mới vào giỏ hàng
            $_SESSION['cart'][$productId] = [
                'id'        => $productId,
                'name'      => $product['name'],
                'price'     => (float)$product['price'], // Đảm bảo là số
                'image'     => $product['image'],
                'quantity'  => $quantity
            ];
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đã thêm "' . htmlspecialchars($product['name']) . '" vào giỏ hàng!'];
        }

        // --- Chuyển hướng ---
        // Chuyển đến trang xem giỏ hàng sau khi thêm thành công
        $this->redirect('?page=cart');
    }

    /**
     * Hiển thị trang giỏ hàng
     */
    public function index() {
        // Dữ liệu giỏ hàng lấy từ session
        $cartItems = $_SESSION['cart'] ?? [];

        // Tính tổng tiền
        $totalPrice = 0;
        foreach ($cartItems as $item) {
            // Kiểm tra lại kiểu dữ liệu phòng trường hợp lỗi
            $price = isset($item['price']) ? (float)$item['price'] : 0;
            $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
            $totalPrice += $price * $quantity;
        }

        // Lấy thông báo flash (nếu có) và xóa nó khỏi session để chỉ hiển thị 1 lần
        $flashMessage = $_SESSION['flash_message'] ?? null;
        if ($flashMessage) {
            unset($_SESSION['flash_message']);
        }

        // Render view giỏ hàng và truyền dữ liệu
        $this->render('cart', [
            'cartItems' => $cartItems,
            'totalPrice' => $totalPrice,
            'flashMessage' => $flashMessage // Truyền thông báo sang view
        ]);
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng (Triển khai sau)
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantities']) && is_array($_POST['quantities'])) {
            $quantities = $_POST['quantities'];
            $updated = false;
            $errorMessages = [];

            foreach ($quantities as $productId => $quantity) {
                $productId = (int)$productId;
                $quantity = (int)$quantity;

                if (!isset($_SESSION['cart'][$productId])) continue; // Bỏ qua nếu SP không có trong giỏ

                if ($quantity <= 0) { // Nếu muốn xóa thì nên dùng nút xóa riêng
                    unset($_SESSION['cart'][$productId]);
                    $updated = true;
                    continue;
                }

                // Kiểm tra lại tồn kho trước khi cập nhật
                $product = Product::find($productId);
                if ($product && $product['stock'] < $quantity) {
                    // Lưu lỗi, không cập nhật SP này
                    $errorMessages[] = 'Không đủ hàng cho "' . htmlspecialchars($_SESSION['cart'][$productId]['name']) . '". Tối đa: ' . $product['stock'];
                    // Giữ nguyên số lượng cũ hoặc đặt lại tối đa? Tạm thời giữ nguyên
                } else if ($product) {
                    // Cập nhật số lượng nếu đủ hàng
                    $_SESSION['cart'][$productId]['quantity'] = $quantity;
                    $updated = true;
                }
                // Nếu product không tìm thấy (dữ liệu cũ), có thể xóa khỏi giỏ?
                // else { unset($_SESSION['cart'][$productId]); }
            }

            if (!empty($errorMessages)) {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => implode('<br>', $errorMessages)];
            } elseif ($updated) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Giỏ hàng đã được cập nhật.'];
            }
        }
        $this->redirect('?page=cart');
    }

    /**
     * Xóa một sản phẩm khỏi giỏ hàng (Triển khai sau)
     */
    public function remove() {
        $productId = $_GET['id'] ?? null;
        if ($productId) {
            $productId = (int)$productId;
            if (isset($_SESSION['cart'][$productId])) {
                $productName = $_SESSION['cart'][$productId]['name'];
                unset($_SESSION['cart'][$productId]);
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đã xóa "' . htmlspecialchars($productName) . '" khỏi giỏ hàng.'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Sản phẩm không có trong giỏ hàng.'];
            }
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Thiếu ID sản phẩm để xóa.'];
        }
        $this->redirect('?page=cart');
    }




    /**
     * Thêm lại các sản phẩm từ một đơn hàng cũ vào giỏ hàng hiện tại
     */
    public function reorder() {
        // 1. Kiểm tra đăng nhập (người dùng phải đăng nhập để xem lịch sử và đặt lại)
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('?page=login');
            return;
        }
        $userId = $_SESSION['user_id'];

        // 2. Lấy orderId từ GET và validate
        $orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$orderId) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID đơn hàng không hợp lệ để đặt lại.'];
            $this->redirect('?page=order_history');
            return;
        }

        // 3. Lấy thông tin đơn hàng cũ và kiểm tra quyền sở hữu
        $order = Order::find($orderId);
        if (!$order || $order['user_id'] != $userId) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền đặt lại đơn hàng này.'];
            $this->redirect('?page=order_history');
            return;
        }

        // 4. Lấy chi tiết các sản phẩm trong đơn hàng cũ
        $orderItems = OrderItem::getItemsByOrder($orderId);
        if (empty($orderItems)) {
            $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'Đơn hàng này không có sản phẩm nào để đặt lại.'];
            $this->redirect('?page=order_history');
            return;
        }

        // 5. Xử lý thêm vào giỏ hàng hiện tại
        $addedItems = [];
        $failedItems = [];
        $cart = $_SESSION['cart'] ?? []; // Lấy giỏ hàng hiện tại

        foreach ($orderItems as $item) {
            $productId = $item['product_id'];
            $quantityToAdd = (int)$item['quantity'];

            // Lấy thông tin sản phẩm hiện tại (để check stock và lấy info)
            $product = Product::find($productId);

            if (!$product) {
                $failedItems[] = "Sản phẩm ID {$productId} không còn tồn tại.";
                continue; // Bỏ qua sản phẩm này
            }

            $productName = $product['name'];
            $currentStock = (int)$product['stock'];
            $quantityInCart = isset($cart[$productId]) ? (int)$cart[$productId]['quantity'] : 0;
            $totalQuantityNeeded = $quantityInCart + $quantityToAdd;

            // Kiểm tra tồn kho
            if ($currentStock < $totalQuantityNeeded) {
                // Không đủ hàng, chỉ thêm tối đa số lượng còn lại (nếu còn)
                $canAdd = $currentStock - $quantityInCart;
                if ($canAdd > 0) {
                    // Thêm số lượng tối đa có thể
                    $cart[$productId] = [
                        'id'        => $productId,
                        'name'      => $productName,
                        'price'     => (float)$product['price'],
                        'image'     => $product['image'],
                        'quantity'  => $quantityInCart + $canAdd // Cập nhật số lượng tối đa
                    ];
                    $addedItems[] = "\"{$productName}\" (Đã thêm {$canAdd}, không đủ {$quantityToAdd})";
                } else {
                    $failedItems[] = "\"{$productName}\" (Hết hàng hoặc giỏ hàng đã có tối đa)";
                }

            } else {
                // Đủ hàng, thêm hoặc cập nhật giỏ hàng
                if (isset($cart[$productId])) {
                    $cart[$productId]['quantity'] += $quantityToAdd; // Cập nhật số lượng
                } else {
                    $cart[$productId] = [
                        'id'        => $productId,
                        'name'      => $productName,
                        'price'     => (float)$product['price'],
                        'image'     => $product['image'],
                        'quantity'  => $quantityToAdd // Thêm mới
                    ];
                }
                $addedItems[] = "\"{$productName}\" (SL: {$quantityToAdd})";
            }
        }

        // 6. Lưu lại giỏ hàng vào session
        $_SESSION['cart'] = $cart;

        // 7. Tạo thông báo kết quả
        $successMsg = !empty($addedItems) ? 'Đã thêm vào giỏ: ' . implode(', ', $addedItems) . '.' : '';
        $errorMsg = !empty($failedItems) ? ' Không thể thêm: ' . implode(', ', $failedItems) . '.' : '';
        $_SESSION['flash_message'] = ['type' => 'info', 'message' => trim($successMsg . $errorMsg)];

        // 8. Chuyển hướng đến trang giỏ hàng
        $this->redirect('?page=cart');
    }
}