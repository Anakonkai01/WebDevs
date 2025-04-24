<?php
// Web/app/Views/order_history.php

// Lấy dữ liệu từ controller, đặt giá trị mặc định
$orders = $orders ?? [];
$flashMessage = $flashMessage ?? null;
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$totalOrders = $totalOrders ?? 0;
$selectedStatus = $selectedStatus ?? 'all';
$validStatuses = $validStatuses ?? ['all', 'Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];

// Helper function để tạo link phân trang/filter giữ lại các tham số hiện tại
function build_order_query_string(array $params): string {
    $currentParams = $_GET;
    // Luôn giữ page=order_history
    $currentParams['page'] = 'order_history';

    // Ghi đè hoặc thêm tham số mới
    foreach ($params as $key => $value) {
        // Xóa tham số nếu giá trị mới là null/rỗng hoặc mặc định
        if ($value === null || $value === '' || ($key === 'status' && $value === 'all')) {
            unset($currentParams[$key]);
        } else {
            $currentParams[$key] = $value;
        }
    }
    // Xóa pg nếu là trang 1
    if (isset($currentParams['pg']) && $currentParams['pg'] == 1) {
        unset($currentParams['pg']);
    }

    return http_build_query($currentParams);
}

// Hàm để dịch status (tùy chọn)
function translate_status(string $status): string {
    $map = [
        'all' => 'Tất cả',
        'Pending' => 'Chờ xử lý',
        'Processing' => 'Đang xử lý',
        'Shipped' => 'Đang giao',
        'Delivered' => 'Đã giao',
        'Cancelled' => 'Đã hủy'
    ];
    return $map[$status] ?? ucfirst($status);
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử đơn hàng</title>
    <?php // Include Header để có CSS chung và Nav ?>
    <?php include_once __DIR__ . '/../layout/header.php'; ?>

    <style>
        /* CSS Cụ thể cho trang này */
        .order-history-container { padding-top: 20px; } /* Thêm padding top */
        .filter-bar { margin-bottom: 20px; background-color: #f8f9fa; padding: 15px; border-radius: 5px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .filter-bar label { font-weight: bold; margin-right: 5px; }
        .filter-bar select, .filter-bar a { padding: 8px 12px; border-radius: 4px; border: 1px solid #ced4da; text-decoration: none; }
        .filter-bar a { background-color: #fff; color: #007bff; }
        .filter-bar a.active { background-color: #007bff; color: #fff; font-weight: bold; border-color: #007bff; }
        .filter-bar select { /* Nếu dùng dropdown thay vì link */
            min-width: 150px;
        }

        .order-table-container { overflow-x: auto; /* Cho phép cuộn ngang trên màn hình nhỏ */ }
        .order-table { width: 100%; border-collapse: collapse; margin-top: 20px; background-color: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .order-table th, .order-table td { border: 1px solid #ddd; padding: 12px 15px; text-align: left; }
        .order-table th { background-color: #e9ecef; font-weight: bold; white-space: nowrap;}
        .order-table td { vertical-align: middle; font-size: 0.95em; }
        .order-table td a { color: #007bff; text-decoration: none; font-weight: 500;}
        .order-table td a:hover { text-decoration: underline; }
        .order-table .order-id { font-weight: bold; }
        .order-table .order-actions a { margin-right: 10px; }
        .order-table .cancel-link { color: #dc3545; }

        /* Style cho các trạng thái */
        .status-label { padding: 3px 8px; border-radius: 12px; font-size: 0.85em; font-weight: bold; color: #fff; white-space: nowrap; }
        .status-Pending { background-color: #ffc107; color: #333; }
        .status-Processing { background-color: #17a2b8; }
        .status-Shipped { background-color: #007bff; }
        .status-Delivered { background-color: #28a745; }
        .status-Cancelled { background-color: #dc3545; }

        .no-orders { text-align: center; color: #6c757d; padding: 40px; border: 1px dashed #ccc; border-radius: 5px; background-color: #f9f9f9; margin-top: 20px; }
        .no-orders p { margin-bottom: 15px; font-size: 1.1em; }
        .no-orders a { color: #fff; background-color: #007bff; padding: 10px 15px; border-radius: 4px; }

        /* Pagination styles (copy/adapt from shop_grid) */
        .pagination { margin-top: 30px; text-align: center; }
        .pagination a, .pagination strong, .pagination span { margin: 0 3px; text-decoration: none; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; display: inline-block; min-width: 40px; text-align: center; box-sizing: border-box;}
        .pagination strong { font-weight: bold; background-color: #007bff; color: white; border-color: #007bff;}
        .pagination a:hover { background-color: #f0f0f0; }
        .pagination span { color: #ccc; background-color: #f8f9fa; }
    </style>
</head>
<body> <?php // Body tag is opened in header.php ?>
<div class="container order-history-container"> <?php // Container is opened in header.php ?>
    <h1>Lịch sử đơn hàng</h1>

    <?php // Hiển thị thông báo (nếu có) ?>
    <?php if ($flashMessage && is_array($flashMessage)): ?>
        <div class="flash-message <?= htmlspecialchars($flashMessage['type'] ?? 'info') ?>">
            <?= htmlspecialchars($flashMessage['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <?php // --- Thanh Lọc --- ?>
    <div class="filter-bar">
        <label for="status-filter">Lọc theo trạng thái:</label>
        <?php // Sử dụng link cho đơn giản, có thể đổi thành dropdown nếu muốn ?>
        <?php foreach ($validStatuses as $status): ?>
            <a href="?<?= build_order_query_string(['status' => $status, 'pg' => null]) ?>"
               class="<?= ($selectedStatus == $status) ? 'active' : '' ?>">
                <?= translate_status($status) ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php // --- Kết thúc Thanh Lọc --- ?>


    <?php if (empty($orders)): ?>
        <div class="no-orders">
            <p>Bạn chưa có đơn hàng nào<?= ($selectedStatus !== 'all' ? ' với trạng thái "' . translate_status($selectedStatus) . '"' : '') ?>.</p>
            <a href="?page=shop_grid">Bắt đầu mua sắm ngay!</a>
        </div>
    <?php else: ?>
        <p>Hiển thị <?= count($orders) ?> trong tổng số <?= $totalOrders ?> đơn hàng<?= ($selectedStatus !== 'all' ? ' có trạng thái "' . translate_status($selectedStatus) . '"' : '') ?>.</p>
        <div class="order-table-container">
            <table class="order-table">
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
                        <td class="order-id">#<?= htmlspecialchars($order['id']) ?></td>
                        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))) ?></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td><?= number_format($order['total'], 0, ',', '.') ?>₫</td>
                        <td>
                            <?php
                            $status = htmlspecialchars($order['status'] ?? 'Pending');
                            $statusClass = "status-" . $status;
                            ?>
                            <span class="status-label <?= $statusClass ?>">
                                <?= translate_status($status) ?>
                            </span>
                        </td>


                        <td class="order-actions">
                            <a href="?page=order_detail&id=<?= $order['id'] ?>" title="Xem chi tiết đơn hàng">Xem chi tiết</a>
                            <?php if (($order['status'] ?? '') === 'Pending'): ?>
                                | <a href="?page=cancel_order&id=<?= $order['id'] ?>" class="cancel-link" onclick="return confirm('Bạn chắc chắn muốn hủy đơn hàng #<?= $order['id'] ?>?\nHành động này sẽ hoàn lại sản phẩm vào kho.')">Hủy</a>
                            <?php endif; ?>
                            | <a href="?page=reorder&id=<?= $order['id'] ?>" title="Đặt lại các sản phẩm trong đơn hàng này vào giỏ hàng hiện tại">Đặt lại</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php // --- Thanh Phân Trang --- ?>
        <div class="pagination">
            <?php if ($totalPages > 1): ?>
                <?php // Previous Page Link ?>
                <?php if ($currentPage > 1): ?>
                    <a href="?<?= build_order_query_string(['pg' => $currentPage - 1]) ?>" aria-label="Trang trước">&laquo; Trước</a>
                <?php else: ?>
                    <span aria-disabled="true">&laquo; Trước</span>
                <?php endif; ?>

                <?php // Page Number Links ?>
                <?php
                $maxPagesToShow = 5;
                $currentPageInt = (int)$currentPage;
                $totalPagesInt = (int)$totalPages;
                $maxPagesToShowInt = (int)$maxPagesToShow;

                $halfMax = (int)floor($maxPagesToShowInt / 2);
                $startPage = max(1, $currentPageInt - $halfMax);
                $endPage = min($totalPagesInt, $currentPageInt + $halfMax);
                if ($endPage - $startPage + 1 < $maxPagesToShowInt) {
                    if ($currentPageInt <= $halfMax) { $endPage = min($totalPagesInt, $startPage + $maxPagesToShowInt - 1); }
                    elseif ($currentPageInt >= $totalPagesInt - $halfMax) { $startPage = max(1, $endPage - $maxPagesToShowInt + 1); }
                }
                if ($totalPagesInt <= $maxPagesToShowInt) { $startPage = 1; $endPage = $totalPagesInt; }

                if ($startPage > 1) {
                    echo '<a href="?' . build_order_query_string(['pg' => 1]) . '">1</a>';
                    if ($startPage > 2) { echo '<span>...</span>'; }
                }
                for ($i = $startPage; $i <= $endPage; $i++) {
                    if ($i == $currentPageInt) { echo '<strong>' . $i . '</strong>'; }
                    else { echo '<a href="?' . build_order_query_string(['pg' => $i]) . '">' . $i . '</a>'; }
                }
                if ($endPage < $totalPagesInt) {
                    if ($endPage < $totalPagesInt - 1) { echo '<span>...</span>'; }
                    echo '<a href="?' . build_order_query_string(['pg' => $totalPagesInt]) . '">' . $totalPagesInt . '</a>';
                }
                ?>

                <?php // Next Page Link ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?<?= build_order_query_string(['pg' => $currentPage + 1]) ?>" aria-label="Trang sau">Sau &raquo;</a>
                <?php else: ?>
                    <span aria-disabled="true">Sau &raquo;</span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php // --- Kết thúc Thanh Phân Trang --- ?>

    <?php endif; ?>
</div> <?php // End container ?>
<?php // Include Footer ?>
<?php include_once __DIR__ . '/../layout/footer.php'; ?>
</body> <?php // Body tag is closed in footer.php ?>
</html>