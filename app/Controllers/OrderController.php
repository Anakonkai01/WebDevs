<?php
// Web/app/Controllers/OrderController.php

require_once BASE_PATH . '/app/Controllers/BaseController.php';
require_once BASE_PATH . '/app/Models/Order.php';
require_once BASE_PATH . '/app/Models/OrderItem.php';
require_once BASE_PATH . '/app/Models/Product.php'; // Cần để kiểm tra/giảm stock
require_once BASE_PATH . '/app/Models/User.php';   // Cần để lấy thông tin user

class OrderController extends BaseController {

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Hiển thị form Checkout
     */
    public function showCheckoutForm() {
        // 1. Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = '?page=checkout'; // Lưu lại trang muốn đến
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Vui lòng đăng nhập để thanh toán.'];
            $this->redirect('?page=login');
            return;
        }

        // 2. Kiểm tra giỏ hàng không rỗng
        $cartItems = $_SESSION['cart'] ?? [];
        if (empty($cartItems)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Giỏ hàng trống! Hãy thêm sản phẩm trước khi thanh toán.'];
            $this->redirect('?page=cart');
            return;
        }

        // 3. Tính tổng tiền
        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 0);
        }

        // 4. Lấy thông tin user để điền sẵn vào form (tùy chọn)
        $user = User::find($_SESSION['user_id']);

        // 5. Lấy lỗi và dữ liệu cũ từ session (nếu có sau khi submit lỗi)
        $errors = $_SESSION['checkout_errors'] ?? [];
        $oldData = $_SESSION['checkout_data'] ?? [];
        unset($_SESSION['checkout_errors']); // Xóa khỏi session sau khi lấy
        unset($_SESSION['checkout_data']);

        // 6. Render view checkout
        $this->render('checkout', [
            'cartItems' => $cartItems,
            'totalPrice' => $totalPrice,
            'user' => $user,
            'errors' => $errors, // Truyền lỗi validation sang view
            'old' => $oldData   // Truyền dữ liệu form cũ sang view
        ]);
    }

    /**
     * Xử lý đặt hàng từ form Checkout
     */
    public function placeOrder() {
        // 1. Kiểm tra lại đăng nhập & giỏ hàng
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('?page=login');
            return;
        }
        $cartItems = $_SESSION['cart'] ?? [];
        if (empty($cartItems)) {
            $this->redirect('?page=cart');
            return;
        }

        // 2. Lấy và Validate dữ liệu POST
        $customer_name = trim($_POST['customer_name'] ?? '');
        $customer_address = trim($_POST['customer_address'] ?? '');
        $customer_phone = trim($_POST['customer_phone'] ?? '');
        $customer_email = trim($_POST['customer_email'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $payment_method = $_POST['payment_method'] ?? 'cod'; // Lấy phương thức thanh toán

        $errors = [];
        if (empty($customer_name)) $errors['customer_name'] = "Vui lòng nhập tên người nhận.";
        if (empty($customer_address)) $errors['customer_address'] = "Vui lòng nhập địa chỉ.";
        if (empty($customer_phone)) $errors['customer_phone'] = "Vui lòng nhập số điện thoại.";
        // Thêm các validate khác nếu cần (phone format, email format...)

        if (!empty($errors)) {
            $_SESSION['checkout_errors'] = $errors;
            $_SESSION['checkout_data'] = $_POST;
            $this->redirect('?page=checkout'); // Quay lại form checkout
            return;
        }

        // 3. Lấy User ID
        $userId = $_SESSION['user_id'];

        // 4. Kiểm tra tồn kho và tính lại tổng tiền (quan trọng!)
        $totalPrice = 0;
        $itemsToProcess = [];
        $canPlaceOrder = true;
        foreach ($cartItems as $productId => $item) {
            $productStock = Product::getStock($productId);
            $quantity = (int)($item['quantity'] ?? 0);
            $price = (float)($item['price'] ?? 0);

            if ($productStock === null || $productStock < $quantity) {
                $canPlaceOrder = false;
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Sản phẩm "' . htmlspecialchars($item['name'] ?? 'N/A') . '" không đủ số lượng tồn kho (' . ($productStock ?? 0) . '). Vui lòng cập nhật giỏ hàng.'];
                $this->redirect('?page=cart');
                return; // Dừng ngay lập tức
            }
            $totalPrice += $price * $quantity;
            $itemsToProcess[$productId] = ['quantity' => $quantity, 'price' => $price];
        }

        // --- BẮT ĐẦU TRANSACTION ---
        $conn = Database::conn();
        $conn->begin_transaction();

        try {
            // 5. Tạo đơn hàng chính
            $orderId = Order::createAndGetId(
                $userId, $totalPrice, $customer_name, $customer_address, $customer_phone,
                $customer_email, $notes, 'Pending' // Trạng thái ban đầu
            );

            if (!$orderId) throw new Exception("Không thể tạo bản ghi đơn hàng chính.");

            // 6. Tạo chi tiết đơn hàng và giảm tồn kho
            foreach ($itemsToProcess as $productId => $itemInfo) {
                $orderItemSuccess = OrderItem::create($orderId, $productId, $itemInfo['quantity'], $itemInfo['price']);
                if (!$orderItemSuccess) throw new Exception("Lỗi khi lưu chi tiết sản phẩm ID: $productId.");

                $decreaseStockSuccess = Product::decreaseStock($productId, $itemInfo['quantity']);
                if (!$decreaseStockSuccess) throw new Exception("Lỗi khi cập nhật kho sản phẩm ID: $productId (có thể đã hết hàng).");
            }

            // --- COMMIT TRANSACTION ---
            $conn->commit();

            // 7. Xóa giỏ hàng
            unset($_SESSION['cart']);

            // 8. Lưu ID đơn hàng để hiển thị trang thành công
            $_SESSION['last_order_id'] = $orderId;

            // 9. Chuyển hướng
            $this->redirect('?page=order_success');

        } catch (Exception $e) {
            // --- ROLLBACK TRANSACTION ---
            $conn->rollback();

            error_log("Checkout Error: " . $e->getMessage()); // Ghi log lỗi chi tiết
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Đã có lỗi xảy ra trong quá trình đặt hàng. Vui lòng thử lại. Chi tiết: ' . $e->getMessage()];
            $this->redirect('?page=checkout'); // Quay lại trang checkout
        }
    }

    /**
     * Hiển thị trang đặt hàng thành công
     */
    public function showSuccessPage() {
        $lastOrderId = $_SESSION['last_order_id'] ?? null;
        // Có thể lấy thêm thông tin đơn hàng nếu muốn hiển thị chi tiết hơn
        // $orderInfo = $lastOrderId ? Order::find($lastOrderId) : null;

        // Xóa ID khỏi session để tránh hiển thị lại nếu refresh trang success
        // unset($_SESSION['last_order_id']);

        $this->render('order_success', ['orderId' => $lastOrderId]);
    }




    /**
     * Hiển thị trang Lịch sử đơn hàng của người dùng hiện tại
     */
    public function orderHistory() {
        // 1. Kiểm tra đăng nhập -> Chuyển hướng nếu chưa đăng nhập
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = '?page=order_history';
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Vui lòng đăng nhập để xem lịch sử đơn hàng.'];
            $this->redirect('?page=login');
            return;
        }

        // 2. Lấy User ID từ session
        $userId = $_SESSION['user_id'];

        // 3. Gọi Model để lấy danh sách đơn hàng (Hàm getByUser đã có sẵn và sắp xếp theo ngày mới nhất)
        $orders = Order::getByUser($userId);

        // 4. Lấy thông báo flash (nếu có)
        $flashMessage = $_SESSION['flash_message'] ?? null;
        if ($flashMessage) {
            unset($_SESSION['flash_message']);
        }

        // 5. Render view order_history và truyền danh sách đơn hàng
        $this->render('order_history', [
            'orders' => $orders,
            'flashMessage' => $flashMessage
        ]);
    }

    /**
     * Hiển thị trang Chi tiết đơn hàng
     * @param int $orderId ID của đơn hàng cần xem
     */
    public function orderDetail(int $orderId) {
        // 1. Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = '?page=order_detail&id=' . $orderId;
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Vui lòng đăng nhập để xem chi tiết đơn hàng.'];
            $this->redirect('?page=login');
            return;
        }
        $userId = $_SESSION['user_id'];

        // 2. Lấy thông tin đơn hàng chính từ DB
        $order = Order::find($orderId);

        // 3. Kiểm tra xem đơn hàng có tồn tại không và có phải của user này không
        if (!$order || $order['user_id'] != $userId) {
            http_response_code(404);
            // Có thể render view lỗi 404 chung
            // $this->render('errors/404', ['message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền xem đơn hàng này.']);
            echo "<h2>404 - Không tìm thấy đơn hàng hoặc bạn không có quyền xem đơn hàng này.</h2>";
            return;
        }

        // 4. Lấy chi tiết các sản phẩm trong đơn hàng (dùng hàm có sẵn trong OrderItem model)
        $orderItems = OrderItem::getDetailedByOrder($orderId);

        // 5. Render view chi tiết đơn hàng
        $this->render('order_detail', [
            'order' => $order,
            'orderItems' => $orderItems
        ]);
    }
}