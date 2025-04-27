<?php
// Web/app/Views/errors/404.php
$message = $message ?? 'Xin lỗi, trang bạn tìm kiếm không tồn tại.'; // Lấy thông báo lỗi nếu có
$pageTitle = $pageTitle ?? '404 - Không tìm thấy trang';

// Quyết định có dùng layout chung hay không
$useLayout = true; // Đặt là false nếu muốn trang lỗi hoàn toàn riêng biệt

if ($useLayout) {
    // Giả sử BASE_PATH đã được định nghĩa
    include_once BASE_PATH . '/app/layout/header.php';
} else {
    // Hoặc tạo phần head HTML cơ bản tại đây nếu không dùng layout
    echo "<!DOCTYPE html><html lang='vi'><head><meta charset='UTF-8'><title>$pageTitle</title>";
    // Thêm link CSS Bootstrap nếu cần
    echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">';
    echo "<style> body { display: flex; align-items: center; justify-content: center; min-height: 100vh; } .error-container { text-align: center; max-width: 500px;} </style>";
    echo "</head><body>";
}
?>

    <div class="container my-5 error-container">
        <h1 class="display-1 text-danger">404</h1>
        <h2 class="mb-4"><?= htmlspecialchars($pageTitle) ?></h2>
        <p class="lead text-muted"><?= htmlspecialchars($message) ?></p>
        <hr class="my-4">
        <a href="?page=home" class="btn btn-primary me-2">Về Trang chủ</a>
        <a href="javascript:history.back()" class="btn btn-outline-secondary">Quay lại</a>
    </div>

<?php
if ($useLayout) {
    include_once BASE_PATH . '/app/layout/footer.php';
} else {
    echo "</body></html>";
}
?>
