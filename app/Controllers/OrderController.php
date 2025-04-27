<?php
namespace App\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Core\Database; 
use Exception;

class OrderController extends BaseController { //extends base controller
    /**
     * OrderController constructor.
     * Starts a session if one does not already exist.
     */
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Show checkout form
     */
    public function showCheckoutForm() {
        // Check user logged in
        if (!isset($_SESSION['user_id'])) { 
            //save url redirect after login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Vui lòng đăng nhập để thanh toán.'];
            $this->redirect('?page=login');
            return;
        }

        // Get full cart and selected IDs
        $fullCart = $_SESSION['cart'] ?? [];
        $selectedIdsParam = $_GET['selected_ids'] ?? '';

        if (empty($fullCart)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Giỏ hàng trống! Hãy thêm sản phẩm trước khi thanh toán.'];
            // Redirect to cart page
            $this->redirect('?page=cart');
            return;
        }

        $selectedIds = [];
        if (!empty($selectedIdsParam)) {
            $selectedIds = array_map('intval', explode(',', $selectedIdsParam));
            $selectedIds = array_filter($selectedIds, function($id) { return $id > 0; });
        }

        if (empty($selectedIds)) {
            $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Vui lòng chọn ít nhất một sản phẩm trong giỏ hàng để thanh toán.'];
            $this->redirect('?page=cart');
            // Redirect to cart page
            return;
        }

        $checkoutCart = [];
        foreach ($selectedIds as $id) {
            if (isset($fullCart[$id])) {
                $checkoutCart[$id] = $fullCart[$id];
            }
        }

        if (empty($checkoutCart)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Các sản phẩm được chọn không hợp lệ hoặc không có trong giỏ hàng.'];
            $this->redirect('?page=cart');
            return;
        }

        $totalPrice = 0;
        foreach ($checkoutCart as $item) { //calculate total price
            $totalPrice += (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 0);
        }

        $_SESSION['checkout_cart'] = $checkoutCart;
        // Get user data
        $user = User::find($_SESSION['user_id']); 
        $errors = $_SESSION['checkout_errors'] ?? [];
        $oldData = $_SESSION['checkout_data'] ?? [];
        unset($_SESSION['checkout_errors'], $_SESSION['checkout_data']);

        $this->render('checkout', [
            'cartItems' => $checkoutCart,
            'totalPrice' => $totalPrice,
            'user' => $user,
            'errors' => $errors,
            'old' => $oldData
        ]);
    }

    /** 
     * Place order 
     */
    public function placeOrder() { 
        //check user and checkout_cart session
        if (!isset($_SESSION['user_id'])) { $this->redirect('?page=login'); return; } 

        $checkoutCart = $_SESSION['checkout_cart'] ?? []; 
        if (empty($checkoutCart)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Không có sản phẩm nào được chọn để thanh toán hoặc phiên làm việc đã hết hạn.'];
            $this->redirect('?page=cart');
            // Redirect to cart page if cart is empty

            return;
        }
        // Validate customer data
        $customerInfo = [
            'name' => trim($_POST['customer_name'] ?? ''),
            'address' => trim($_POST['customer_address'] ?? ''),
            'phone' => trim($_POST['customer_phone'] ?? ''),
            'email' => trim($_POST['customer_email'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'payment_method' => $_POST['payment_method'] ?? 'cod' // Giữ lại nếu cần dùng sau
        ];
        $validationErrors = $this->validateCheckoutData($customerInfo);

        if (!empty($validationErrors)) {
            $_SESSION['checkout_errors'] = $validationErrors;
            $_SESSION['checkout_data'] = $_POST; // Lưu lại toàn bộ POST data
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Vui lòng kiểm tra lại thông tin giao hàng.'];
            $this->redirect('?page=cart');
            return;
        }

        //check stock and prepare data to order
        $preparedOrderData = $this->checkStockAndPrepareItems($checkoutCart);
        if ($preparedOrderData === false) {
            // flash_message is set in checkStockAndPrepareItems
            $this->redirect('?page=cart');
            return;
        }
        // Extract items to process, total price and user id
        $itemsToProcess = $preparedOrderData['itemsToProcess'];
        $totalPrice = $preparedOrderData['totalPrice'];
        $userId = $_SESSION['user_id'];

        // execute transaction
        $orderId = $this->executeOrderTransaction($userId, $totalPrice, $customerInfo, $itemsToProcess);

        // handle transaction result
        if ($orderId) { // if success
            // Thành công: Dọn dẹp session và chuyển hướng
            $this->cleanupSessionAfterOrder($checkoutCart);
            $_SESSION['last_order_id'] = $orderId;
            $this->redirect('?page=order_success');
        } else {
            // Thất bại: Chuyển hướng về giỏ hàng (flash message đã được set trong executeOrderTransaction)
            $this->redirect('?page=cart');
        }
    }

    /** 
     * validate customer data
     */
    private function validateCheckoutData(array $customerInfo): array {
        $errors = [];
        if (empty($customerInfo['name'])) $errors['customer_name'] = "Vui lòng nhập tên người nhận.";
        if (empty($customerInfo['address'])) $errors['customer_address'] = "Vui lòng nhập địa chỉ.";
        if (empty($customerInfo['phone'])) $errors['customer_phone'] = "Vui lòng nhập số điện thoại.";
        if (!empty($customerInfo['email']) && !filter_var($customerInfo['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['customer_email'] = "Định dạng email không hợp lệ.";
        }
        return $errors;
    }

    /** 
     * Check stock and prepare item to process
     */

    private function checkStockAndPrepareItems(array $checkoutCart): array|false {
        $totalPrice = 0;
        $itemsToProcess = [];
        foreach ($checkoutCart as $productId => $item) {
            $productStock = Product::getStock($productId);
            $quantity = (int)($item['quantity'] ?? 0);
            $price = (float)($item['price'] ?? 0);

            if ($productStock === null || $productStock < $quantity) {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Sản phẩm "' . htmlspecialchars($item['name'] ?? 'N/A') . '" không đủ số lượng tồn kho (' . ($productStock ?? 0) . '). Vui lòng quay lại giỏ hàng và cập nhật.'];
                return false; // Trả về false nếu có lỗi tồn kho
            } 
            $totalPrice += $price * $quantity;
            $itemsToProcess[$productId] = ['quantity' => $quantity, 'price' => $price];
        } 
        return ['itemsToProcess' => $itemsToProcess, 'totalPrice' => $totalPrice];
    }

    /**
     *  create new order
     */
    private function executeOrderTransaction(int $userId, float $totalPrice, array $customerInfo, array $itemsToProcess): int|false {
        $conn = Database::conn();
        $conn->begin_transaction();

        try { //try catch error
            //create order
            $orderId = Order::createAndGetId(
                $userId, $totalPrice, $customerInfo['name'], $customerInfo['address'], $customerInfo['phone'],
                $customerInfo['email'], $customerInfo['notes'], 'Pending' // default status is Pending
            );
            if (!$orderId) throw new Exception("Không thể tạo bản ghi đơn hàng chính.");

            // 2. Tạo chi tiết đơn hàng và giảm tồn kho
            foreach ($itemsToProcess as $productId => $itemInfo) {
                if (!OrderItem::create($orderId, $productId, $itemInfo['quantity'], $itemInfo['price'])) {
                    throw new Exception("Lỗi khi lưu chi tiết sản phẩm ID: $productId.");
                }
                if (!Product::decreaseStock($productId, $itemInfo['quantity'])) {
                    throw new Exception("Lỗi khi cập nhật kho sản phẩm ID: $productId (có thể đã hết hàng).");
                }
            }

            // 3. Commit transaction
            $conn->commit();
            return $orderId; // Trả về Order ID nếu thành công

        } catch (Exception $e) {
            // Rollback transaction nếu có lỗi
            $conn->rollback();
            error_log("Checkout Transaction Error: " . $e->getMessage());
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Đã có lỗi xảy ra trong quá trình xử lý đơn hàng. Vui lòng thử lại.'];
            return false; // Trả về false nếu thất bại
        }
    }

    /** 
     * clean session after order success
     */
    private function cleanupSessionAfterOrder(array $checkoutCart): void {
        if (isset($_SESSION['cart'])) { // remove product from cart session
            foreach (array_keys($checkoutCart) as $productIdToRemove) {
                unset($_SESSION['cart'][$productIdToRemove]);
            }
        } 
        unset($_SESSION['checkout_cart']);
    }

    /**
     * show success page
     */
    public function showSuccessPage() {
        //get order id 
        /**
         * @var int|null $lastOrderId The ID of the last order placed by the user.
         * @var array $orderId An array with one element, the ID of the last order placed by the user.
         */
        $lastOrderId = $_SESSION['last_order_id'] ?? null;
        $this->render('order_success', ['orderId' => $lastOrderId]);
    }

    /** Show history page */
    public function orderHistory() {
        if (!isset($_SESSION['user_id'])) { // Check user
            $_SESSION['redirect_after_login'] = '?page=order_history'; //save url redirect
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Vui lòng đăng nhập để xem lịch sử đơn hàng.'];
            $this->redirect('?page=login');
            return;
        }

        // Retrieve user ID
        $userId = $_SESSION['user_id'];
        // Define pagination
        $ordersPerPage = 10;
        // Get current page from query string
        $currentPage = isset($_GET['pg']) ? max(1, (int)$_GET['pg']) : 1;
        // Calculate offset
        $offset = ($currentPage - 1) * $ordersPerPage;
        // Define valid status
        $validStatuses = ['all', 'Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
        // Get selected status from query string
        $selectedStatus = $_GET['status'] ?? 'all';
        // Check if the selected status is valid
        if (!in_array($selectedStatus, $validStatuses)) { $selectedStatus = 'all'; }
        // Get order by user
        $orders = Order::getByUser($userId, $selectedStatus, $ordersPerPage, $offset);
        // Count orders by user
        $totalOrders = Order::countByUser($userId, $selectedStatus);
        // Calculate total page
        $totalPages = $totalOrders > 0 ? ceil($totalOrders / $ordersPerPage) : 1;
        $flashMessage = $_SESSION['flash_message'] ?? null;
        if ($flashMessage) { unset($_SESSION['flash_message']); }

        $this->render('order_history', [
            'orders' => $orders, 'flashMessage' => $flashMessage, 'currentPage' => $currentPage,

            'totalPages' => $totalPages, 'totalOrders' => $totalOrders, 'selectedStatus' => $selectedStatus,
            'validStatuses' => $validStatuses
        ]);
    }

    /** show order detail */
    public function orderDetail(int $orderId) {
        if (!isset($_SESSION['user_id'])) { //check user
            $_SESSION['redirect_after_login'] = '?page=order_detail&id=' . $orderId; //save url redirect
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Vui lòng đăng nhập để xem chi tiết đơn hàng.'];
            $this->redirect('?page=login');
            return;
        }
        $userId = $_SESSION['user_id'];
        $order = Order::find($orderId);
        if (!$order || $order['user_id'] != $userId) {
            http_response_code(404);
            echo "<h2>404 - Không tìm thấy đơn hàng hoặc bạn không có quyền xem đơn hàng này.</h2>";
            return;
        }
        $orderItems = OrderItem::getDetailedByOrder($orderId);
        $this->render('order_detail', [ 'order' => $order, 'orderItems' => $orderItems ]);
    }

    /** 
     * Cancel order
     */
    public function cancelOrder() {
        if (!isset($_SESSION['user_id'])) { $this->redirect('?page=login'); return; }
        $userId = $_SESSION['user_id'];
        $orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$orderId) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID đơn hàng không hợp lệ.'];
            $this->redirect('?page=order_history'); return;
        }
        $conn = Database::conn();
        $conn->begin_transaction();
        try {
            $order = Order::find($orderId);
            if (!$order) throw new Exception('Đơn hàng không tồn tại.');
            if ($order['user_id'] != $userId) throw new Exception('Bạn không có quyền hủy đơn hàng này.');
            if ($order['status'] !== 'Pending') throw new Exception('Chỉ có thể hủy đơn hàng ở trạng thái "Chờ xử lý".');
            $orderItems = OrderItem::getItemsByOrder($orderId);
            if (!Order::updateStatus($orderId, 'Cancelled')) { throw new Exception('Không thể cập nhật trạng thái đơn hàng.'); }
            foreach ($orderItems as $item) {
                if (!Product::increaseStock($item['product_id'], $item['quantity'])) {
                    error_log("CRITICAL: Failed to increase stock for product ID {$item['product_id']} during cancellation (Order ID: {$orderId})");
                }
            }
            $conn->commit();
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => "Đã hủy đơn hàng #{$orderId} thành công."];
            $this->redirect('?page=order_history');
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Order Cancellation Error (Order ID: {$orderId}, User ID: {$userId}): " . $e->getMessage());
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Lỗi khi hủy đơn hàng: ' . $e->getMessage()];
            $this->redirect('?page=order_history');
        }
    }
}