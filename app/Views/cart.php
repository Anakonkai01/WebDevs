<?php
// Web/app/Views/cart.php
$cartItems = $cartItems ?? [];
$totalPrice = $totalPrice ?? 0;
$flashMessage = $flashMessage ?? null; // Lấy thông báo flash từ controller
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        h1 { border-bottom: 1px solid #eee; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; vertical-align: middle;}
        th { background-color: #f2f2f2; font-weight: bold;}
        td img { max-width: 70px; height: auto; margin-right: 15px; vertical-align: middle; border: 1px solid #eee;}
        .quantity-input { width: 60px; text-align: center; padding: 5px; }
        .remove-link { color: #dc3545; text-decoration: none; font-weight: bold;}
        .remove-link:hover { text-decoration: underline; }
        .cart-actions { text-align: right; margin-bottom: 20px; padding-top: 10px;}
        .cart-actions button { padding: 10px 20px; background-color: #ffc107; border: none; color: #333; cursor: pointer; border-radius: 3px; font-weight: bold; margin-left: 10px; }
        .cart-actions button:hover { background-color: #e0a800; }
        .cart-summary { text-align: right; margin-top: 20px; border-top: 2px solid #eee; padding-top: 15px;}
        .cart-summary h3 { margin-bottom: 15px; }
        .cart-summary strong { font-size: 1.4em; color: #dc3545; }
        .checkout-btn {
            display: inline-block; padding: 12px 25px; background-color: #28a745; color: white;
            text-decoration: none; border-radius: 5px; font-size: 1.1em; margin-top: 10px;
        }
        .checkout-btn:hover { background-color: #218838; }
        .continue-shopping { display: inline-block; margin-top: 15px; color: #007bff; text-decoration: none; margin-right: 20px;}
        .continue-shopping:hover { text-decoration: underline; }
        .empty-cart { border: 1px dashed #ccc; padding: 30px; text-align: center; background-color: #f9f9f9;}
        .empty-cart p { font-size: 1.1em; margin-bottom: 15px;}
        /* Flash Messages */
        .flash-message { padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid transparent; }
        .flash-message.success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .flash-message.error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .product-name a { text-decoration: none; color: #007bff; font-weight: bold;}
        .product-name a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<h1>Giỏ hàng của bạn</h1>

<?php // Hiển thị flash message nếu có ?>
<?php if ($flashMessage): ?>
    <div class="flash-message <?= htmlspecialchars($flashMessage['type']) ?>">
        <?= htmlspecialchars($flashMessage['message']) ?>
    </div>
<?php endif; ?>

<?php if (empty($cartItems)): ?>
    <div class="empty-cart">
        <p>Giỏ hàng của bạn hiện đang trống.</p>
        <a href="?page=shop_grid" class="checkout-btn" style="background-color: #007bff;">Bắt đầu mua sắm</a>
    </div>
<?php else: ?>
    <form action="?page=cart_update" method="POST">
        <table>
            <thead>
            <tr>
                <th colspan="2">Sản phẩm</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
                <th>Xóa</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($cartItems as $itemId => $item): ?>
                <?php
                // Lấy thông tin an toàn, đặt giá trị mặc định nếu thiếu
                $itemName = htmlspecialchars($item['name'] ?? 'N/A');
                $itemImage = htmlspecialchars($item['image'] ?? 'default.jpg');
                $itemPrice = isset($item['price']) ? (float)$item['price'] : 0;
                $itemQuantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
                $itemSubtotal = $itemPrice * $itemQuantity;
                ?>
                <tr>
                    <td style="width: 80px;">
                        <a href="?page=product_detail&id=<?= $itemId ?>">
                            <img src="/public/img/<?= $itemImage ?>" alt="<?= $itemName ?>">
                        </a>
                    </td>
                    <td class="product-name">
                        <a href="?page=product_detail&id=<?= $itemId ?>">
                            <?= $itemName ?>
                        </a>
                    </td>
                    <td><?= number_format($itemPrice, 0, ',', '.') ?>₫</td>
                    <td>
                        <input type="number" name="quantities[<?= $itemId ?>]" value="<?= $itemQuantity ?>" min="1" class="quantity-input" aria-label="Số lượng cho <?= $itemName ?>">
                    </td>
                    <td><?= number_format($itemSubtotal, 0, ',', '.') ?>₫</td>
                    <td>
                        <a href="?page=cart_remove&id=<?= $itemId ?>" class="remove-link" onclick="return confirm('Bạn chắc chắn muốn xóa sản phẩm \"<?= $itemName ?>\" khỏi giỏ hàng?')">Xóa</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-actions">
            <button type="submit">Cập nhật giỏ hàng</button>
        </div>
    </form>

    <div class="cart-summary">
        <a href="?page=shop_grid" class="continue-shopping">&laquo; Tiếp tục mua sắm</a>
        <h3>Tổng cộng: <strong><?= number_format($totalPrice, 0, ',', '.') ?>₫</strong></h3>
        <a href="?page=checkout" class="checkout-btn">Tiến hành thanh toán</a>
    </div>

<?php endif; ?>

</body>
</html>