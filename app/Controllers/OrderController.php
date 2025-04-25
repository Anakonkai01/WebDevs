<?php
// Web/app/Controllers/OrderController.php
namespace App\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Core\Database;
use Exception; // <-- Cần cho try...catch

class OrderController extends BaseController {

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Phương thức showCheckoutForm giữ nguyên như phiên bản trước (đã xử lý selected_ids)
    public function showCheckoutForm() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Vui lòng đăng nhập để thanh toán.'];
            $this->redirect('?page=login');
            return;
        }

        $fullCart = $_SESSION['cart'] ?? [];
        $selectedIdsParam = $_GET['selected_ids'] ?? '';

        if (empty($fullCart)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Giỏ hàng trống! Hãy thêm sản phẩm trước khi thanh toán.'];
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
        foreach ($checkoutCart as $item) {
            $totalPrice += (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 0);
        }

        $_SESSION['checkout_cart'] = $checkoutCart;
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

    // ======================================================
    // PHƯƠNG THỨC placeOrder ĐÃ ĐƯỢC REFACTOR
    // ======================================================
    public function placeOrder() {
        // 1. Kiểm tra đăng nhập và giỏ hàng checkout
        if (!isset($_SESSION['user_id'])) { $this->redirect('?page=login'); return; }

        $checkoutCart = $_SESSION['checkout_cart'] ?? [];
        if (empty($checkoutCart)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Không có sản phẩm nào được chọn để thanh toán hoặc phiên làm việc đã hết hạn.'];
            $this->redirect('?page=cart');
            return;
        }

        // 2. Validate dữ liệu khách hàng từ POST
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
            // Quay lại giỏ hàng để người dùng chọn lại và nhập lại thông tin
            // Hoặc có thể quay lại trang checkout nhưng cần đảm bảo selected_ids còn
            $this->redirect('?page=cart');
            return;
        }

        // 3. Kiểm tra tồn kho và chuẩn bị dữ liệu item
        $preparedOrderData = $this->checkStockAndPrepareItems($checkoutCart);
        if ($preparedOrderData === false) {
            // flash_message đã được set trong checkStockAndPrepareItems
            $this->redirect('?page=cart');
            return;
        }
        $itemsToProcess = $preparedOrderData['itemsToProcess'];
        $totalPrice = $preparedOrderData['totalPrice'];
        $userId = $_SESSION['user_id'];

        // 4. Thực hiện giao dịch đặt hàng
        $orderId = $this->executeOrderTransaction($userId, $totalPrice, $customerInfo, $itemsToProcess);

        // 5. Xử lý kết quả giao dịch
        if ($orderId) {
            // Thành công: Dọn dẹp session và chuyển hướng
            $this->cleanupSessionAfterOrder($checkoutCart);
            $_SESSION['last_order_id'] = $orderId;
            $this->redirect('?page=order_success');
        } else {
            // Thất bại: Chuyển hướng về giỏ hàng (flash message đã được set trong executeOrderTransaction)
            $this->redirect('?page=cart');
        }
    }

    // ======================================================
    // CÁC PHƯƠNG THỨC PRIVATE HELPER CHO placeOrder
    // ======================================================

    /**
     * Validate dữ liệu thông tin giao hàng từ POST.
     * @param array $customerInfo Dữ liệu khách hàng từ POST
     * @return array Mảng lỗi (rỗng nếu không có lỗi)
     */
    private function validateCheckoutData(array $customerInfo): array {
        $errors = [];
        if (empty($customerInfo['name'])) $errors['customer_name'] = "Vui lòng nhập tên người nhận.";
        if (empty($customerInfo['address'])) $errors['customer_address'] = "Vui lòng nhập địa chỉ.";
        if (empty($customerInfo['phone'])) $errors['customer_phone'] = "Vui lòng nhập số điện thoại.";
        // Thêm validate khác nếu cần (định dạng SĐT, Email...)
        if (!empty($customerInfo['email']) && !filter_var($customerInfo['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['customer_email'] = "Định dạng email không hợp lệ.";
        }
        return $errors;
    }

    /**
     * Kiểm tra tồn kho và chuẩn bị danh sách item để xử lý.
     * @param array $checkoutCart Giỏ hàng đã lọc.
     * @return array|false Mảng chứa ['itemsToProcess', 'totalPrice'] nếu thành công, false nếu lỗi tồn kho.
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
     * Thực hiện toàn bộ giao dịch tạo đơn hàng trong database.
     * @param int $userId
     * @param float $totalPrice
     * @param array $customerInfo
     * @param array $itemsToProcess
     * @return int|false Order ID nếu thành công, false nếu thất bại.
     */
    private function executeOrderTransaction(int $userId, float $totalPrice, array $customerInfo, array $itemsToProcess): int|false {
        $conn = Database::conn();
        $conn->begin_transaction();

        try {
            // 1. Tạo đơn hàng chính
            $orderId = Order::createAndGetId(
                $userId, $totalPrice, $customerInfo['name'], $customerInfo['address'], $customerInfo['phone'],
                $customerInfo['email'], $customerInfo['notes'], 'Pending'
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
     * Dọn dẹp session sau khi đặt hàng thành công.
     * @param array $checkoutCart Các sản phẩm đã được xử lý trong đơn hàng.
     * @return void
     */
    private function cleanupSessionAfterOrder(array $checkoutCart): void {
        if (isset($_SESSION['cart'])) {
            foreach (array_keys($checkoutCart) as $productIdToRemove) {
                unset($_SESSION['cart'][$productIdToRemove]);
            }
        }
        unset($_SESSION['checkout_cart']);
    }

    // ======================================================
    // CÁC PHƯƠNG THỨC KHÁC GIỮ NGUYÊN
    // ======================================================

    public function showSuccessPage() {
        // ... (Giữ nguyên)
        $lastOrderId = $_SESSION['last_order_id'] ?? null;
        $this->render('order_success', ['orderId' => $lastOrderId]);
    }

    public function orderHistory() {
        // ... (Giữ nguyên)
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = '?page=order_history';
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Vui lòng đăng nhập để xem lịch sử đơn hàng.'];
            $this->redirect('?page=login');
            return;
        }
        $userId = $_SESSION['user_id'];
        $ordersPerPage = 10;
        $currentPage = isset($_GET['pg']) ? max(1, (int)$_GET['pg']) : 1;
        $offset = ($currentPage - 1) * $ordersPerPage;
        $validStatuses = ['all', 'Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
        $selectedStatus = $_GET['status'] ?? 'all';
        if (!in_array($selectedStatus, $validStatuses)) { $selectedStatus = 'all'; }
        $orders = Order::getByUser($userId, $selectedStatus, $ordersPerPage, $offset);
        $totalOrders = Order::countByUser($userId, $selectedStatus);
        $totalPages = $totalOrders > 0 ? ceil($totalOrders / $ordersPerPage) : 1;
        $flashMessage = $_SESSION['flash_message'] ?? null;
        if ($flashMessage) { unset($_SESSION['flash_message']); }
        $this->render('order_history', [
            'orders' => $orders, 'flashMessage' => $flashMessage, 'currentPage' => $currentPage,
            'totalPages' => $totalPages, 'totalOrders' => $totalOrders, 'selectedStatus' => $selectedStatus,
            'validStatuses' => $validStatuses
        ]);
    }

    public function orderDetail(int $orderId) {
        // ... (Giữ nguyên)
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = '?page=order_detail&id=' . $orderId;
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Vui lòng đăng nhập để xem chi tiết đơn hàng.'];
            $this->redirect('?page=login');
            return;
        }
        $userId = $_SESSION['user_id'];
        $order = Order::find($orderId);
        if (!$order || $order['user_id'] != $userId) {
            http_response_code(404);
            // Render view lỗi 404 hoặc echo
            echo "<h2>404 - Không tìm thấy đơn hàng hoặc bạn không có quyền xem đơn hàng này.</h2>";
            return;
        }
        $orderItems = OrderItem::getDetailedByOrder($orderId);
        $this->render('order_detail', [ 'order' => $order, 'orderItems' => $orderItems ]);
    }

    public function cancelOrder() {
        // ... (Giữ nguyên)
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
} // End Class OrderController