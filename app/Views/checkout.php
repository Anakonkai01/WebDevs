<?php
// Web/app/Views/checkout.php
$cartItems = $cartItems ?? [];
$totalPrice = $totalPrice ?? 0;
$user = $user ?? null; // Thông tin user đăng nhập
$errors = $errors ?? []; // Lỗi validation từ controller
$old = $old ?? []; // Dữ liệu form cũ nếu có lỗi

function display_error_checkout($field, $errors) {
    if (!empty($errors[$field])) {
        echo '<span style="color: red; font-size: 0.9em; display: block; margin-top: 2px;">' . htmlspecialchars($errors[$field]) . '</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán đơn hàng</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f8f9fa; line-height: 1.6; }
        .container { max-width: 1100px; margin: auto; }
        .checkout-title { text-align: center; margin-bottom: 20px; }
        .checkout-title h1 { margin-bottom: 5px; }
        .checkout-container { display: flex; flex-wrap: wrap; gap: 30px; }
        .billing-details, .order-summary { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 1px 5px rgba(0,0,0,0.1); }
        .billing-details { flex: 2 1 500px; } /* Chiếm nhiều không gian hơn */
        .order-summary { flex: 1 1 350px; } /* Ít không gian hơn */
        h2, h3 { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 0; margin-bottom: 25px; color: #333;}
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 6px; font-weight: bold; color: #555; }
        input[type="text"], input[type="email"], input[type="tel"], textarea {
            width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 1em;
        }
        textarea { min-height: 100px; resize: vertical; }
        .order-summary ul { list-style: none; padding: 0; margin: 0; }
        .order-summary li { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px dashed #eee; font-size: 0.95em; }
        .order-summary li:last-child { border-bottom: none; }
        .order-summary .item-info { display: flex; align-items: center; }
        .order-summary .item-info img { width: 40px; height: auto; margin-right: 10px; border: 1px solid #eee; }
        .order-summary .item-info span { display: block; line-height: 1.3; }
        .order-summary .item-info .item-name { font-weight: bold; }
        .order-summary .item-price { white-space: nowrap; }
        .order-summary .summary-row { display: flex; justify-content: space-between; padding: 8px 0; }
        .order-summary .total { font-weight: bold; font-size: 1.3em; padding-top: 15px; margin-top: 15px; border-top: 2px solid #333;}
        .place-order-btn {
            display: block; width: 100%; padding: 15px; background-color: #dc3545; color: white;
            border: none; border-radius: 5px; cursor: pointer; font-size: 1.2em; font-weight: bold; margin-top: 25px; text-align: center;
        }
        .place-order-btn:hover { background-color: #c82333; }
        .payment-method label { display: flex; align-items: center; padding: 10px; border: 1px solid #eee; border-radius: 4px; margin-bottom: 10px; cursor: pointer;}
        .payment-method label:has(input:checked) { border-color: #007bff; background-color: #f8f9fa; }
        .payment-method input[type="radio"] { width: auto; margin-right: 10px; }
        /* Flash message style */
        .flash-message { padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid transparent; }
        .flash-message.success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .flash-message.error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
    </style>
</head>
<body>
<div class="container">
    <div class="checkout-title">
        <h1>Tiến hành thanh toán</h1>
        <p><a href="?page=cart">&laquo; Quay lại giỏ hàng</a></p>
    </div>

    <?php // Hiển thị flash message nếu có lỗi ?>
    <?php $flashMessage = $_SESSION['flash_message'] ?? null; if ($flashMessage): unset($_SESSION['flash_message']); ?>
        <div class="flash-message <?= htmlspecialchars($flashMessage['type']) ?>">
            <?= htmlspecialchars($flashMessage['message']) ?>
        </div>
    <?php endif; ?>

    <div class="checkout-container">
        <div class="billing-details">
            <h2>Thông tin giao hàng</h2>
            <form action="?page=handle_checkout" method="POST" id="checkout-form">
                <div class="form-group">
                    <label for="customer_name">Họ và tên người nhận <span style="color:red">*</span></label>
                    <input type="text" id="customer_name" name="customer_name" value="<?= htmlspecialchars($old['customer_name'] ?? $user['username'] ?? '') ?>" required>
                    <?php display_error_checkout('customer_name', $errors); ?>
                </div>

                <div class="form-group">
                    <label for="customer_address">Địa chỉ nhận hàng <span style="color:red">*</span></label>
                    <textarea id="customer_address" name="customer_address" required><?= htmlspecialchars($old['customer_address'] ?? '') ?></textarea>
                    <?php display_error_checkout('customer_address', $errors); ?>
                </div>

                <div class="form-group">
                    <label for="customer_phone">Số điện thoại <span style="color:red">*</span></label>
                    <input type="tel" id="customer_phone" name="customer_phone" value="<?= htmlspecialchars($old['customer_phone'] ?? '') ?>" required>
                    <?php display_error_checkout('customer_phone', $errors); ?>
                </div>

                <div class="form-group">
                    <label for="customer_email">Email</label>
                    <input type="email" id="customer_email" name="customer_email" value="<?= htmlspecialchars($old['customer_email'] ?? $user['email'] ?? '') ?>">
                    <?php display_error_checkout('customer_email', $errors); ?>
                </div>

                <div class="form-group">
                    <label for="notes">Ghi chú đơn hàng (Tùy chọn)</label>
                    <textarea id="notes" name="notes"><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
                </div>

                <h3>Phương thức thanh toán</h3>
                <div class="form-group payment-method">
                    <label>
                        <input type="radio" name="payment_method" value="cod" checked>
                        Thanh toán khi nhận hàng (COD)
                    </label>
                </div>

                <button type="submit" class="place-order-btn">Đặt hàng</button>
            </form>
        </div>

        <div class="order-summary">
            <h2>Tóm tắt đơn hàng</h2>
            <?php if (!empty($cartItems)): ?>
                <ul>
                    <?php foreach ($cartItems as $itemId => $item): ?>
                        <li>
                            <div class="item-info">
                                <img src="/public/img/<?= htmlspecialchars($item['image'] ?? 'default.jpg') ?>" alt="">
                                <span>
                                         <span class="item-name"><?= htmlspecialchars($item['name'] ?? 'N/A') ?></span>
                                         <span>Số lượng: <?= (int)($item['quantity'] ?? 0) ?></span>
                                      </span>
                            </div>
                            <span class="item-price"><?= number_format((float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 0), 0, ',', '.') ?>₫</span>
                        </li>
                    <?php endforeach; ?>
                    <li class="summary-row total">
                        <span>Tổng cộng</span>
                        <span><?= number_format($totalPrice, 0, ',', '.') ?>₫</span>
                    </li>
                </ul>
            <?php else: ?>
                <p>Không có sản phẩm nào trong giỏ hàng.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>