<?php
$orders = $orders ?? [];
$flashMessage = $flashMessage ?? null;
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$totalOrders = $totalOrders ?? 0;
$selectedStatus = $selectedStatus ?? 'all';
$validStatuses = $validStatuses ?? ['all', 'Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled']; // Default valid status

// Function to build query string for links
function build_order_query_string_bs(array $params): string
{
    $currentParams = $_GET; // Get current URL parameters
    $currentParams['page'] = 'order_history'; // Ensure 'page' parameter is set to 'order_history'
    foreach ($params as $key => $value) {
        // Remove parameters with null, empty, or 'all' (for status) values
        if ($value === null || $value === '' || ($key === 'status' && $value === 'all')) {
            unset($currentParams[$key]);
        } else {
            $currentParams[$key] = $value;
        }
    }
    // Remove 'pg' parameter if its value is 1
    if (isset($currentParams['pg']) && $currentParams['pg'] == 1) {
        unset($currentParams['pg']);
    }
    return http_build_query($currentParams);
}
// Function to translate status to Vietnamese
function translate_status_bs(string $status): string
{
    $map = ['all' => 'Tất cả', 'Pending' => 'Chờ xử lý', 'Processing' => 'Đang xử lý', 'Shipped' => 'Đang giao', 'Delivered' => 'Đã giao', 'Cancelled' => 'Đã hủy'];
    return $map[$status] ?? ucfirst($status);
}

// Function to get badge class for status
function getStatusBadgeClass($status) {
    return match ($status) {
        'Processing' => 'bg-info text-dark',
        'Shipped' => 'bg-primary',
        'Delivered' => 'bg-success',
        'Cancelled' => 'bg-danger',
        default => 'bg-warning text-dark', // 'Pending' or other unknown statuses
    };
}

$pageTitle = 'Lịch sử đơn hàng';
include_once __DIR__ . '/layout/header.php'; // Include header file (contains Bootstrap and flash message handling)
?>
    <link rel="stylesheet" href="/webfinal/public/css/order_history.css"> 

    <!-- Main container -->
    <div class="container my-4">
        <h1 class="mb-4">Lịch sử đơn hàng</h1>
        <div class="card shadow-sm mb-4">
            <div class="card-body d-flex align-items-center flex-wrap gap-2">
                <label for="status-filter-select" class="form-label fw-bold mb-0 me-2">Lọc theo trạng thái:</label>
                <form method="GET" action="" class="d-inline-block me-2" id="statusFilterForm">
                    <!-- Form for select -->
                    <input type="hidden" name="page" value="order_history">
                    <input type="hidden" name="pg" value="1">
                    <!-- Reset page on filter change -->
                    <select class="form-select form-select-sm d-inline-block" id="status-filter-select" name="status" style="width: auto;" onchange="document.getElementById('statusFilterForm').submit();">//Form filter
                        <?php foreach ($validStatuses as $status) : ?>
                            <option value="<?= $status ?>" <?= ($selectedStatus == $status) ? 'selected' : '' ?>>
                                <?= translate_status_bs($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <!-- Check if has order -->
        <?php if (empty($orders)): ?>
            <div class="alert alert-info text-center" role="alert">
                <p class="mb-3 fs-5">Bạn chưa có đơn hàng nào<?= ($selectedStatus !== 'all' ? ' với trạng thái "' . translate_status_bs($selectedStatus) . '"' : '') ?>.</p>
                <a href="?page=shop_grid" class="btn btn-primary"><i class="fas fa-shopping-bag me-2"></i>Bắt đầu mua sắm</a>
            </div>
        <?php else: ?>
            <!-- Show the number of orders -->
            <p class="text-muted mb-3">Hiển thị <?= count($orders) ?> trên tổng số <?= $totalOrders ?> đơn hàng<?= ($selectedStatus !== 'all' ? ' có trạng thái "' . translate_status_bs($selectedStatus) . '"' : '') ?>.</p>
            <div class="table-responsive card shadow-sm">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th scope="col">Mã ĐH</th>
                        <th scope="col">Ngày đặt</th>
                        <th scope="col">Người nhận</th>
                        <th scope="col" class="text-end">Tổng tiền</th>
                        <th scope="col" class="text-center">Trạng thái</th>
                        <th scope="col" class="text-center">Thao tác</th> 
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $order) : ?>
                        <tr>
                            <td class="fw-bold">#<?= htmlspecialchars($order['id']) ?></td> 
                            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))) ?></td> 
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td class="text-end"><?= number_format($order['total'], 0, ',', '.') ?>₫</td> 
                            <td class="text-center">
                                <?php $status = htmlspecialchars($order['status'] ?? 'Pending'); ?>
                                <span class="badge rounded-pill status-badge <?= getStatusBadgeClass($status) ?>">
                                    <?= translate_status_bs($status) ?> 
                                </span>
                            </td>
                            <td class="text-center order-actions">
                                <a href="?page=order_detail&id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i> <span class="d-none d-md-inline">Chi tiết</span>
                                </a>
                                 <!-- Reorder action -->
                                <a href="?page=reorder&id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-success ms-1" title="Đặt lại đơn hàng">
                                    <i class="fas fa-redo-alt"></i> <span class="d-none d-md-inline">Đặt lại</span>
                                </a>
                                <?php if (($order['status'] ?? '') === 'Pending'): ?>
                                    <a href="?page=cancel_order&id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-danger ms-1" title="Hủy đơn hàng" onclick="return confirm('Bạn chắc chắn muốn hủy đơn hàng #<?= $order['id'] ?>?\nHành động này sẽ hoàn lại sản phẩm vào kho.')">
                                        <i class="fas fa-times-circle"></i> <span class="d-none d-md-inline">Hủy</span>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Navigation page -->
            <nav aria-label="Order History Navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($totalPages > 1) :
                        $currentPageInt = (int)$currentPage;
                        $totalPagesInt = (int)$totalPages;

                        $prevDisabled = ($currentPageInt <= 1) ? 'disabled' : '';
                        
                        $nextDisabled = ($currentPageInt >= $totalPagesInt) ? 'disabled' : '';
                        ?>
                        <li class="page-item <?= $prevDisabled ?>"> <a class="page-link" href="?<?= build_order_query_string_bs(['pg' => $currentPageInt - 1]) ?>">&laquo;</a> </li>
                        <?php
                        // Pagination logic
                        $maxPagesToShow = 5;
                        $halfMax = floor($maxPagesToShow / 2);
                        $startPage = max(1, $currentPageInt - $halfMax);
                        $endPage = min($totalPagesInt, $startPage + $maxPagesToShow - 1);
                        if ($endPage - $startPage + 1 < $maxPagesToShow) {
                            $startPage = max(1, $endPage - $maxPagesToShow + 1);
                        }
                        if ($startPage > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?' . build_order_query_string_bs(['pg' => 1]) . '">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        for ($i = $startPage; $i <= $endPage; $i++) {
                            echo '<li class="page-item ' . ($i == $currentPageInt ? 'active' : '') . '"><a class="page-link" href="?' . build_order_query_string_bs(['pg' => $i]) . '">' . $i . '</a></li>';
                        }
                        if ($endPage < $totalPagesInt) {
                            if ($endPage < $totalPagesInt - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?' . build_order_query_string_bs(['pg' => $totalPagesInt]) . '">' . $totalPagesInt . '</a></li>';
                        }
                        ?> 
                        <li class="page-item <?= $nextDisabled ?>"> <a class="page-link" href="?<?= build_order_query_string_bs(['pg' => $currentPageInt + 1]) ?>">&raquo;</a> </li> 
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

<?php include_once __DIR__ . '/layout/footer.php'; ?> 