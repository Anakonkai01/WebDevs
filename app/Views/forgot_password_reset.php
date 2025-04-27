<?php
// Web/app/Views/forgot_password_request.php
$pageTitle = $pageTitle ?? 'Quên Mật Khẩu';

// Quyết định dùng layout hay trang độc lập
$useLayout = false; // Đặt là true nếu muốn dùng header/footer chung

if ($useLayout) {
    include_once __DIR__ . '/../layout/header.php';
} else { ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/webfinal/public/css/forgot_password_request.css"> <style>
        body { background-color: #eef2f7; }
        .forgot-pw-container { min-height: 100vh; }
        .forgot-pw-card { max-width: 480px; width: 100%; border: none; border-radius: 0.75rem; }
        .forgot-pw-card .card-body { padding: 2rem 2.5rem; }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center forgot-pw-container p-3 p-md-4">
<?php } ?>

<div class="card shadow-lg forgot-pw-card">
    <div class="card-body">
        <div class="text-center mb-4">
            <a href="?page=home" class="text-decoration-none">
                <h1 class="h2 fw-bold text-primary mb-2">MyShop</h1>
             </a>
            <p class="text-muted">Quên Mật Khẩu</p>
        </div>

        <?php // Flash message display ?>
        <?php
          // Cần khởi động session nếu chưa có để đọc flash message
          if (session_status() == PHP_SESSION_NONE) { session_start(); }
          $flashMessage = $_SESSION['flash_message'] ?? null;
          if ($flashMessage){
              echo '<div class="alert alert-' . htmlspecialchars($flashMessage['type'] ?? 'info') . ' alert-dismissible fade show small" role="alert">';
              echo htmlspecialchars($flashMessage['message'] ?? '');
              echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
              echo '</div>';
              unset($_SESSION['flash_message']); // Xóa sau khi hiển thị
          }
        ?>

        <p class="text-center text-muted mb-4 small">Nhập địa chỉ email của bạn. Nếu tài khoản tồn tại, chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu.</p>

        <form action="?page=handle_forgot_password" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label visually-hidden">Địa chỉ Email</label>
                 <div class="input-group input-group-lg">
                    <span class="input-group-text"><i class="fas fa-envelope fa-fw"></i></span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Nhập email của bạn" required autofocus>
                </div>
            </div>
            <button class="w-100 btn btn-primary btn-lg" type="submit">
                 <i class="fas fa-paper-plane me-2"></i>Gửi yêu cầu
            </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top">
            <small class="text-muted"><a href="?page=login" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Quay lại Đăng nhập</a></small>
        </div>
    </div>
</div>

<?php if (!$useLayout): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php else:
    include_once __DIR__ . '/../layout/footer.php';
endif; ?>