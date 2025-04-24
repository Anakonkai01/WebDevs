<?php
// Web/app/Views/order_success.php
$orderId = $orderId ?? null;
// No full layout needed for this simple confirmation page
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công</title>
    <link href="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background-color: #e9f7ef; /* Light green background */ }
        .success-container { max-width: 650px; margin-top: 5rem; }
        .icon { font-size: 4em; color: #198754; /* Bootstrap success green */ }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
<div class="success-container text-center">
    <div class="card shadow-lg border-success">
        <div class="card-body p-4 p-lg-5">
            <div class="icon mb-3"><i class="fas fa-check-circle"></i></div>
            <h1 class="card-title text-success mb-3">Đặt hàng thành công!</h1>
            <p class="card-text lead text-muted">Cảm ơn bạn đã tin tưởng và mua hàng tại MyShop. Đơn hàng của bạn đã được ghi nhận.</p>
            <?php if ($orderId): ?>
                <p class="card-text">Mã đơn hàng của bạn là: <strong class="text-primary">#<?= htmlspecialchars($orderId) ?></strong></p>
                <p class="card-text small">Chúng tôi sẽ liên hệ với bạn sớm để xác nhận. Bạn có thể xem lại đơn hàng trong <a href="?page=order_history" class="text-decoration-none">lịch sử mua hàng</a>.</p>
                <?php // Optionally unset session here if not done elsewhere
                // unset($_SESSION['last_order_id']);
                ?>
            <?php endif; ?>
            <hr class="my-4">
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <a href="?page=shop_grid" class="btn btn-outline-secondary btn-lg px-4 gap-3"><i class="fas fa-arrow-left me-1"></i>Tiếp tục mua sắm</a>
                <a href="?page=home" class="btn btn-primary btn-lg px-4"><i class="fas fa-home me-1"></i>Về trang chủ</a>
            </div>
        </div>
    </div>
</div>
<script src="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>