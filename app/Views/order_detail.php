<?php
// Web/app/Views/order_detail.php
$order = $order ?? null; // Lấy thông tin đơn hàng chính
$orderItems = $orderItems ?? []; // Lấy danh sách sản phẩm chi tiết

if (!$order) {
    echo "<p>Không tìm thấy thông tin đơn hàng.</p>";
    return;
}

// Helper để hiển thị trạng thái với màu sắc
function getStatusClass($status) {
    $statusLower = strtolower($status ?? 'Pending');
    switch ($statusLower) {
        case 'processing': return 'status-Processing';
        case 'shipped': return 'status-Shipped';
        case 'delivered': return 'status-Delivered';
        case 'cancelled': return 'status-Cancelled';
        case 'pending':
        default: return 'status-Pending';
    }
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết Đơn hàng #<?= htmlspecialchars($order['id']) ?></title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f8f9fa; line-height: 1.6; }
        .container { max-width: 900px; margin: auto; background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 1px 5px rgba(0,0,0,0.1); }
        h1 { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 25px; text-align: center; color: #333; }
        .order-info, .shipping-info, .items-info { margin-bottom: 30px; }
        h2 { border-bottom: 1px dashed #ddd; padding-bottom: 8px; margin-bottom: 15px; font-size: 1.4em; color: #0056b3;}
        .info-grid { display: grid; grid-template-columns: 150px 1fr; gap: 5px 15px; margin-bottom: 10px; }
        .info-grid dt { font-weight: bold; color: #555; text-align: right; }
        .info-grid dd { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px 12px; text-align: left; }
        th { background-color: #e9ecef; font-weight: bold; }
        td img { max-width: 50px; height: auto; vertical-align: middle; margin-right: 10px; border: 1px solid #eee;}
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; font-size: 1.1em; border-top: 2px solid #aaa;}
        .back-link { display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        /* Status colors (copy from order_history) */
        .status-Pending { color: #ffc107; font-weight: bold; }
        .status-Processing { color: #17a2b8; font-weight: bold; }
        .status-Shipped { color: #007bff; font-weight: bold; }
        .status-Delivered { color: #28a745; font-weight: bold; }
        .status-Cancelled { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h1>Chi tiết Đơn hàng #<?= htmlspecialchars($order['id']) ?></h1>

    <div class="order-info">
        <h2>Thông tin Đơn hàng</h2>
        <dl class="info-grid">
            <dt>Mã đơn hàng:</dt>
            <dd>#<?= htmlspecialchars($order['id']) ?></dd>

            <dt>Ngày đặt:</dt>
            <dd><?= htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))) ?></dd>

            <dt>Tổng tiền:</dt>
            <dd><?= number_format($order['total'], 0, ',', '.') ?>₫</dd>

            <dt>Trạng thái:</dt>
            <dd>
                <?php $status = htmlspecialchars($order['status'] ?? 'Pending'); ?>
                <span class="<?= getStatusClass($status) ?>"><?= str_replace('_', ' ', $status) ?></span>
            </dd>

            <dt>Ghi chú:</dt>
            <dd><?= htmlspecialchars($order['notes'] ?? '(Không có)') ?></dd>
        </dl>
    </div>

    <div class="shipping-info">
        <h2>Thông tin Giao hàng</h2>
        <dl class="info-grid">
            <dt>Người nhận:</dt>
            <dd><?= htmlspecialchars($order['customer_name']) ?></dd>

            <dt>Địa chỉ:</dt>
            <dd><?= nl2br(htmlspecialchars($order['customer_address'])) ?></dd>

            <dt>Điện thoại:</dt>
            <dd><?= htmlspecialchars($order['customer_phone']) ?></dd>

            <dt>Email:</dt>
            <dd><?= htmlspecialchars($order['customer_email'] ?? '(Không có)') ?></dd>
        </dl>
    </div>

    <div class="items-info">
        <h2>Sản phẩm trong Đơn hàng</h2>
        <?php if (!empty($orderItems)): ?>
            <table>
                <thead>
                <tr>
                    <th colspan="2">Sản phẩm</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th class="text-right">Thành tiền</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($orderItems as $item): ?>
                    <tr>
                        <td style="width: 60px;">
                            <img src="/public/img/<?= htmlspecialchars($item['product_image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                        </td>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= number_format($item['item_price'], 0, ',', '.') ?>₫</td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td class="text-right"><?= number_format($item['item_price'] * $item['quantity'], 0, ',', '.') ?>₫</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                <tr class="total-row">
                    <td colspan="4" class="text-right"><strong>Tổng cộng</strong></td>
                    <td class="text-right"><strong><?= number_format($order['total'], 0, ',', '.') ?>₫</strong></td>
                </tr>
                </tfoot>
            </table>
        <?php else: ?>
            <p>Không có thông tin chi tiết sản phẩm cho đơn hàng này.</p>
        <?php endif; ?>
    </div>

    <a href="?page=order_history" class="back-link">&laquo; Quay lại Lịch sử đơn hàng</a>

</div>
</body>
</html>