<?php
// Web/app/Views/order_history.php
$orders = $orders ?? []; // Lấy danh sách đơn hàng từ controller
$flashMessage = $flashMessage ?? null; // Lấy thông báo flash nếu có
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử đơn hàng</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f8f9fa; line-height: 1.6; }
        .container { max-width: 950px; margin: auto; background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 1px 5px rgba(0,0,0,0.1); }
        h1 { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 25px; text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px 15px; text-align: left; }
        th { background-color: #e9ecef; font-weight: bold; white-space: nowrap;}
        td { vertical-align: middle; }
        td a { color: #007bff; text-decoration: none; font-weight: 500;}
        td a:hover { text-decoration: underline; }
        .status-Pending { color: #ffc107; font-weight: bold; }
        .status-Processing { color: #17a2b8; font-weight: bold; }
        .status-Shipped { color: #007bff; font-weight: bold; }
        .status-Delivered { color: #28a745; font-weight: bold; }
        .status-Cancelled { color: #dc3545; font-weight: bold; }
        .no-orders { text-align: center; color: #6c757d; padding: 30px; border: 1px dashed #ccc; border-radius: 5px; background-color: #f9f9f9; }
        .no-orders p { margin-bottom: 15px; font-size: 1.1em; }
        /* Flash message style */
        .flash-message { padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid transparent; }
        .flash-message.success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .flash-message.error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
    </style>
</head>
<body>
<div class="container">
    <h1>Lịch sử đơn hàng</h1>

    <?php // Hiển thị thông báo (nếu có) ?>
    <?php if ($flashMessage): ?>
        <div class="flash-message <?= htmlspecialchars($flashMessage['type']) ?>">
            <?= htmlspecialchars($flashMessage['message']) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="no-orders">
            <p>Bạn chưa có đơn hàng nào.</p>
            <a href="?page=shop_grid">Bắt đầu mua sắm ngay!</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>Mã ĐH</th>
                <th>Ngày đặt</th>
                <th>Người nhận</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?= htmlspecialchars($order['id']) ?></td>
                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))) // Định dạng ngày giờ ?></td>
                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                    <td><?= number_format($order['total'], 0, ',', '.') ?>₫</td>
                    <td>
                        <?php
                        $status = htmlspecialchars(ucfirst(strtolower($order['status'] ?? 'Pending')));
                        $statusClass = "status-" . $status; // Tạo class CSS dựa trên trạng thái
                        ?>
                        <span class="<?= $statusClass ?>">
                                    <?= str_replace('_', ' ', $status) // Thay gạch dưới bằng dấu cách nếu cần ?>
                                </span>
                    </td>
                    <td>
                        <a href="?page=order_detail&id=<?= $order['id'] ?>">Xem chi tiết</a>
                        <?php // Có thể thêm nút Hủy đơn hàng ở đây nếu trạng thái là 'Pending' ?>
                        <?php /* if (($order['status'] ?? '') === 'Pending'): ?>
                                    | <a href="?page=cancel_order&id=<?= $order['id'] ?>" onclick="return confirm('Bạn muốn hủy đơn hàng này?')" style="color: red;">Hủy</a>
                                <?php endif; */ ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php // Thêm phân trang cho lịch sử đơn hàng nếu cần ?>
    <?php endif; ?>
</div>
</body>
</html>