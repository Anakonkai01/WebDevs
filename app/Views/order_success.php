<?php
// Web/app/Views/order_success.php
$orderId = $orderId ?? null; // Lấy ID đơn hàng từ controller
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt hàng thành công</title>
    <style>
        body { font-family: sans-serif; padding: 40px; text-align: center; background-color: #e9f7ef; color: #155724; }
        .success-container { background-color: #fff; max-width: 600px; margin: 40px auto; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border: 1px solid #c3e6cb;}
        .icon { font-size: 4em; color: #28a745; margin-bottom: 20px; } /* Simple checkmark */
        h1 { color: #155724; margin-bottom: 15px; }
        p { font-size: 1.1em; margin-bottom: 25px; line-height: 1.6; }
        strong { color: #007bff; }
        .links a {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        .links a.primary { background-color: #007bff; color: white; }
        .links a.secondary { background-color: #6c757d; color: white; }
        .links a:hover { opacity: 0.9; }
    </style>
</head>
<body>
<div class="success-container">
    <div class="icon">&#10004;</div> <h1>Đặt hàng thành công!</h1>
    <p>Cảm ơn bạn đã tin tưởng và mua hàng tại cửa hàng của chúng tôi. Đơn hàng của bạn đã được ghi nhận.</p>
    <?php if ($orderId): ?>
        <p>Mã đơn hàng của bạn là: <strong>#<?= htmlspecialchars($orderId) ?></strong></p>
        <p>Chúng tôi sẽ liên hệ với bạn sớm để xác nhận đơn hàng. Bạn có thể xem lại đơn hàng trong <a href="?page=order_history">lịch sử mua hàng</a>.</p>
        <?php unset($_SESSION['last_order_id']); // Xóa khỏi session sau khi hiển thị ?>
    <?php endif; ?>
    <div class="links">
        <a href="?page=shop_grid" class="secondary">Tiếp tục mua sắm</a>
        <a href="?page=home" class="primary">Quay về trang chủ</a>
    </div>
</div>
</body>
</html>