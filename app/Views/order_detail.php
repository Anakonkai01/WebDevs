<?php
// Initialize order and order items
$order = $order ?? null;
$orderItems = $orderItems ?? [];

// Check if order exists
if (!$order) {
    // Display error message if no order found
    $pageTitle = 'Lỗi đơn hàng'; include_once __DIR__ . '/layout/header.php';
    echo "<div class='container my-4'><div class='alert alert-danger'>Không tìm thấy thông tin đơn hàng.</div></div>";
    include_once __DIR__ . '/layout/footer.php';
    return;
}

// Function to get status badge class
function getStatusBadgeClassOd($status) {
    switch ($status) { case 'Processing': return 'bg-info text-dark'; case 'Shipped': return 'bg-primary'; case 'Delivered': return 'bg-success'; case 'Cancelled': return 'bg-danger'; case 'Pending': default: return 'bg-warning text-dark'; }
}

// Function to translate order status
function translate_status_od(string $status): string {
    $map = [ 'all' => 'Tất cả', 'Pending' => 'Chờ xử lý', 'Processing' => 'Đang xử lý', 'Shipped' => 'Đang giao', 'Delivered' => 'Đã giao', 'Cancelled' => 'Đã hủy' ];
    return $map[$status] ?? ucfirst($status);
}

// Set page title
$pageTitle = 'Chi tiết Đơn hàng #' . htmlspecialchars($order['id']);
include_once __DIR__ . '/layout/header.php';
// Set css path
$cssPath = '/webfinal/public/css/order_detail.css';
?>
<head>
    <link rel="stylesheet" href="<?= htmlspecialchars($cssPath) ?>">
</head>

    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <!-- Display order details title and order ID -->
            <h1>Chi tiết Đơn hàng <span class="text-primary">#<?= htmlspecialchars($order['id']) ?></span></h1>
            <a href="?page=order_history" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Quay lại Lịch sử</a>
        </div>

        <div class="row g-4">
            <!-- Order and shipping info -->
            <div class="col-lg-5">
                <div class="card shadow-sm mb-4">                    
                    <div class="card-header"><h2 class="h5 mb-0">Thông tin Đơn hàng</h2></div>
                    <div class="card-body">
                         <!-- Display Order Information Details -->
                        <dl class="row mb-0 small">
                             <!-- Display order ID -->
                            <dt class="col-5 text-sm-end">Mã đơn hàng:</dt>
                            <dd class="col-7 fw-bold">#<?= htmlspecialchars($order['id']) ?></dd>
                             <!-- Display order creation date -->
                            <dt class="col-5 text-sm-end">Ngày đặt:</dt>
                            <dd class="col-7"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))) ?></dd>

                            <dt class="col-5 text-sm-end">Tổng tiền:</dt>
                            <dd class="col-7 fw-bold text-danger"><?= number_format($order['total'], 0, ',', '.') ?>₫</dd>

                            <dt class="col-5 text-sm-end">Trạng thái:</dt>
                            <dd class="col-7">
                                <?php $status = htmlspecialchars($order['status'] ?? 'Pending'); ?>
                                <span class="badge rounded-pill <?= getStatusBadgeClassOd($status) ?>"><?= translate_status_od($status) ?></span>
                            </dd>

                            <dt class="col-5 text-sm-end pt-2">Ghi chú:</dt>
                            <dd class="col-7 pt-2"><?= htmlspecialchars($order['notes'] ?? '(Không có)') ?></dd>
                        </dl>
                    </div>
                </div>
                 <!-- Shipping Information -->
                <div class="card shadow-sm">                    
                    <div class="card-header"><h2 class="h5 mb-0">Thông tin Giao hàng</h2></div>
                    <div class="card-body">
                         <!-- Display Shipping Information Details -->
                        <dl class="row mb-0 small">
                             <!-- Display customer name -->
                            <dt class="col-5 text-sm-end">Người nhận:</dt>
                            <dd class="col-7"><?= htmlspecialchars($order['customer_name']) ?></dd>
                             <!-- Display customer address -->
                            <dt class="col-5 text-sm-end pt-2">Địa chỉ:</dt>
                            <dd class="col-7 pt-2"><?= nl2br(htmlspecialchars($order['customer_address'])) ?></dd>
                             <!-- Display customer phone number -->
                            <dt class="col-5 text-sm-end">Điện thoại:</dt>
                            <dd class="col-7"><?= htmlspecialchars($order['customer_phone']) ?></dd>
                             <!-- Display customer email -->
                            <dt class="col-5 text-sm-end">Email:</dt>
                            <dd class="col-7"><?= htmlspecialchars($order['customer_email'] ?? '(Không có)') ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <!-- Items info -->
            <div class="col-lg-7">
                <div class="card shadow-sm">                    
                    <!-- Order Items Section Header -->
                    <div class="card-header"><h2 class="h5 mb-0">Sản phẩm trong Đơn hàng</h2></div>
                    <div class="card-body p-0">
                        <!-- Check if there are any items in the order -->
                        <?php if (!empty($orderItems)): ?>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th scope="col" colspan="2">Sản phẩm</th>
                                        <th scope="col" class="text-end">Giá</th>
                                        <th scope="col" class="text-center">Số lượng</th>
                                        <th scope="col" class="text-end">Thành tiền</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <!-- Loop items -->
                                    <?php foreach ($orderItems as $item): ?>
                                        <tr>
                                            <td style="width: 70px;">
                                                <img src="/webfinal/public/img/<?= htmlspecialchars($item['product_image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="img-fluid rounded border order-detail-item-img">
                                            </td>
                                            <!-- Display product name -->
                                            <td>
                                                <a href="?page=product_detail&id=<?= $item['product_id'] ?>" class="text-dark text-decoration-none fw-bold small">
                                                    <?= htmlspecialchars($item['product_name']) ?>
                                                </a>
                                            </td>
                                            <!-- Display product price -->
                                            <td class="text-end small"><?= number_format($item['item_price'], 0, ',', '.') ?>₫</td>                                            
                                            <td class="text-center small"><?= htmlspecialchars($item['quantity']) ?></td>
                                            <td class="text-end small"><?= number_format($item['item_price'] * $item['quantity'], 0, ',', '.') ?>₫</td>                                            
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                         <!-- Display total price for this order -->
                                    <tr class="table-light">
                                        <td colspan="4" class="text-end fw-bold">Tổng cộng</td>
                                        <td class="text-end fw-bold text-danger"><?= number_format($order['total'], 0, ',', '.') ?>₫</td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted p-3">Không có thông tin chi tiết sản phẩm cho đơn hàng này.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Reorder -->
                <div class="mt-3 text-end">
                    <a href="?page=reorder&id=<?= $order['id'] ?>" class="btn btn-success"><i class="fas fa-redo-alt me-2"></i>Đặt lại đơn hàng</a>
                </div>
            </div>
        </div>
    </div>

<?php include_once __DIR__ . '/layout/footer.php'; ?>