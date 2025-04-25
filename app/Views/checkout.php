<?php
// Web/app/Views/checkout.php
$cartItems = $cartItems ?? [];
$totalPrice = $totalPrice ?? 0;
$user = $user ?? null; // User info
$errors = $errors ?? []; // Validation errors
$old = $old ?? []; // Old form data

function display_error_checkout_bs($field, $errors) {
    if (!empty($errors[$field])) {
        echo '<div class="invalid-feedback d-block">' . htmlspecialchars($errors[$field]) . '</div>';
    }
}
// Add Bootstrap 'is-invalid' class if error exists
function error_class($field, $errors) {
    return !empty($errors[$field]) ? 'is-invalid' : '';
}

// Include header
include_once __DIR__ . '/../layout/header.php';
?>
    <style>
        /* Custom styles for checkout */
        .order-summary-card { position: sticky; top: 80px; /* Adjust based on header height */ }
        .order-summary-item img { width: 50px; height: 50px; object-fit: contain; }
    </style>

    <div class="container my-4">
        <div class="text-center mb-4">
            <h1>Tiến hành thanh toán</h1>
            <p class="lead"><a href="?page=cart" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Quay lại giỏ hàng</a></p>
        </div>

        <?php // Flash message display is handled by header.php ?>

        <?php if (empty($cartItems)): ?>
            <div class="alert alert-warning text-center">Giỏ hàng của bạn trống. Không thể tiến hành thanh toán.</div>
        <?php else: ?>
            <form action="?page=handle_checkout" method="POST" id="checkout-form">
                <div class="row g-4">

                    <?php // Billing Details Column ?>
                    <div class="col-lg-7">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h2 class="h4 mb-0">Thông tin giao hàng</h2>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="customer_name" class="form-label">Họ và tên người nhận <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= error_class('customer_name', $errors) ?>" id="customer_name" name="customer_name" value="<?= htmlspecialchars($old['customer_name'] ?? $user['username'] ?? '') ?>" required>
                                    <?php display_error_checkout_bs('customer_name', $errors); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="customer_address" class="form-label">Địa chỉ nhận hàng <span class="text-danger">*</span></label>
                                    <textarea class="form-control <?= error_class('customer_address', $errors) ?>" id="customer_address" name="customer_address" required rows="3"><?= htmlspecialchars($old['customer_address'] ?? '') ?></textarea>
                                    <?php display_error_checkout_bs('customer_address', $errors); ?>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="customer_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control <?= error_class('customer_phone', $errors) ?>" id="customer_phone" name="customer_phone" value="<?= htmlspecialchars($old['customer_phone'] ?? '') ?>" required>
                                        <?php display_error_checkout_bs('customer_phone', $errors); ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="customer_email" class="form-label">Email</label>
                                        <input type="email" class="form-control <?= error_class('customer_email', $errors) ?>" id="customer_email" name="customer_email" value="<?= htmlspecialchars($old['customer_email'] ?? $user['email'] ?? '') ?>">
                                        <?php display_error_checkout_bs('customer_email', $errors); ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">Ghi chú đơn hàng (Tùy chọn)</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
                                </div>


                                <h3 class="h5 mt-4 mb-3">Phương thức thanh toán</h3>
                                <div class="list-group">
                                    <label class="list-group-item d-flex gap-2">
                                        <input class="form-check-input flex-shrink-0" type="radio" name="payment_method" id="payment_cod" value="cod" checked>
                                        <span>
                                            Thanh toán khi nhận hàng (COD)
                                            <small class="d-block text-muted">Trả tiền mặt khi nhận được hàng.</small>
                                         </span>
                                    </label>
                                    <?php // Add other payment methods here (e.g., Bank Transfer, Online Payment) ?>
                                    <?php /*
                                    <label class="list-group-item d-flex gap-2">
                                        <input class="form-check-input flex-shrink-0" type="radio" name="payment_method" id="payment_bank" value="bank">
                                         <span>
                                             Chuyển khoản ngân hàng
                                             <small class="d-block text-muted">Thực hiện thanh toán vào tài khoản ngân hàng của chúng tôi.</small>
                                         </span>
                                    </label>
                                    */ ?>
                                </div>

                            </div> <?php // end card-body ?>
                        </div> <?php // end card ?>
                    </div> <?php // End Billing Details Column ?>


                    <?php // Order Summary Column ?>
                    <div class="col-lg-5">
                        <div class="card shadow-sm order-summary-card">
                            <div class="card-header">
                                <h2 class="h4 mb-0">Tóm tắt đơn hàng</h2>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush mb-3">
                                    <?php foreach ($cartItems as $itemId => $item): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center order-summary-item">
                                            <div class="d-flex align-items-center">
                                                <img src="/webfinal/public/img/<?= htmlspecialchars($item['image'] ?? 'default.jpg') ?>" alt="" class="border rounded me-2">
                                                <div>
                                                    <span class="d-block small fw-bold"><?= htmlspecialchars($item['name'] ?? 'N/A') ?></span>
                                                    <span class="d-block text-muted small">SL: <?= (int)($item['quantity'] ?? 0) ?></span>
                                                </div>
                                            </div>
                                            <span class="text-nowrap small"><?= number_format((float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 0), 0, ',', '.') ?>₫</span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>

                                <div class="d-flex justify-content-between pt-3 border-top">
                                    <span class="fw-bold fs-5">Tổng cộng:</span>
                                    <span class="fw-bold fs-5 text-danger"><?= number_format($totalPrice, 0, ',', '.') ?>₫</span>
                                </div>
                            </div> <?php // end card-body ?>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-danger btn-lg w-100">
                                    <i class="fas fa-check-circle me-2"></i>Đặt hàng
                                </button>
                            </div>
                        </div> <?php // end card ?>
                    </div> <?php // End Order Summary Column ?>

                </div> <?php // End Row ?>
            </form>
        <?php endif; // End check for empty cart ?>
    </div> <?php // End Container ?>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>