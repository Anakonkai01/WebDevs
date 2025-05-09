<?php
// Web/app/Views/order_success.php
$orderId = $orderId ?? null; // Lấy orderId từ controller nếu có
// Trang này thường không cần $pageTitle vì nó là trang xác nhận cuối cùng
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công - MyShop</title>

    <?php // Nhúng Bootstrap và Font Awesome trực tiếp ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <?php // Nhúng file CSS riêng của trang này ?>
    <link rel="stylesheet" href="/webfinal/public/css/order_success.css">

</head>
<body>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg border-success text-center success-card">
        <div class="card-body p-4 p-lg-5">

            <i class="fas fa-check-circle fa-5x text-success mb-4"></i> <?php // Icon lớn hơn ?>

            <h1 class="card-title display-6 text-success mb-3">Đặt hàng thành công!</h1>

            <p class="card-text lead text-muted mb-4">
                Cảm ơn bạn đã tin tưởng và mua hàng tại <strong>MyShop</strong>. Đơn hàng của bạn đã được ghi nhận.
            </p>

            <?php if ($orderId): ?>
                <div class="order-info bg-light p-3 rounded mb-4 border">
                    <p class="card-text mb-1">Mã đơn hàng của bạn là:</p>
                    <p class="h4 text-primary fw-bold mb-2">#<?= htmlspecialchars($orderId) ?></p>
                    <p class="card-text small text-muted mb-0">
                        Chúng tôi sẽ liên hệ với bạn sớm để xác nhận. Bạn có thể xem chi tiết đơn hàng trong
                        <a href="?page=order_history" class="text-decoration-none fw-medium">Lịch sử mua hàng</a>.
                    </p>
                </div>
                <?php
                // Không nên unset session ở đây, controller nên xử lý việc này nếu cần
                // unset($_SESSION['last_order_id']);
                ?>
            <?php else: ?>
                 <p class="card-text small text-muted mb-4">
                    Bạn có thể xem lại các đơn hàng đã đặt trong
                    <a href="?page=order_history" class="text-decoration-none fw-medium">Lịch sử mua hàng</a>.
                </p>
            <?php endif; ?>

            <hr class="my-4">

            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <a href="?page=shop_grid" class="btn btn-outline-secondary btn-lg px-4 gap-3">
                    <i class="fas fa-arrow-left me-1"></i>Tiếp tục mua sắm
                </a>
                <a href="?page=home" class="btn btn-primary btn-lg px-4">
                    <i class="fas fa-home me-1"></i>Về trang chủ
                </a>
            </div>

        </div>
    </div>
</div>

<?php // Nhúng Bootstrap JS Bundle (nếu cần cho các tương tác khác, nhưng trang này có thể không cần) ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

</body>
</html>