<?php
// Web/app/Views/register.php
$errors = $errors ?? []; // Lỗi validation từ lần submit trước
$old = $old ?? []; // Dữ liệu form cũ từ lần submit trước
$flashMessage = $flashMessage ?? null; // Thông báo lỗi chung (nếu có)
$pageTitle = 'Đăng ký tài khoản';

// Helper functions để hiển thị lỗi
function display_error_bs($field, $errors) {
    if (isset($errors[$field])) {
        // Thêm class d-block để lỗi hiển thị ngay cả khi input không focus
        echo '<div class="invalid-feedback d-block">' . htmlspecialchars($errors[$field]) . '</div>';
    }
}
function error_class_bs($field, $errors) {
    return isset($errors[$field]) ? 'is-invalid' : '';
}
?>
<!DOCTYPE html>
<html lang="vi">   

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    </style>
    <link rel="stylesheet" href="public/css/register.css">
</head>
<body>
<div class="register-container p-4">
    <div class="card shadow-sm">
        <div class="card-body p-4 p-lg-5">
            <div class="text-center mb-4">
                <a href="?page=home" class="navbar-brand fw-bold fs-4 mb-3 d-inline-block">MyShop</a>
                <h1 class="h3 mb-3 fw-normal">Tạo tài khoản mới</h1>
            </div>

            <?php // Hiển thị Flash message chung (ví dụ lỗi DB khi đăng ký) ?>
            <?php if (isset($flashMessage) && is_array($flashMessage)): ?>
                <div class="alert alert-<?= htmlspecialchars($flashMessage['type'] ?? 'info') ?> alert-dismissible fade show small" role="alert">
                    <?= htmlspecialchars($flashMessage['message'] ?? '') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="?page=handle_register" method="POST" novalidate>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control <?= error_class_bs('username', $errors) ?>" id="username" name="username" placeholder="Tên đăng nhập" value="<?= htmlspecialchars($old['username'] ?? '') ?>" required>
                    <label for="username">Tên đăng nhập</label>
                    <?php display_error_bs('username', $errors); ?>
                </div>
                <div class="form-floating mb-3">
                    <input type="email" class="form-control <?= error_class_bs('email', $errors) ?>" id="email" name="email" placeholder="name@example.com" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                    <label for="email">Email</label>
                    <?php display_error_bs('email', $errors); ?>
                </div>
                <div class="form-floating mb-3 position-relative">
                    <input type="password" class="form-control <?= error_class_bs('password', $errors) ?>" id="password" name="password" placeholder="Mật khẩu" required>
                    <label for="password">Mật khẩu (ít nhất 6 ký tự)</label>
                    <span class="password-toggle" onclick="togglePasswordVisibility('password', this)">
                         <i class="fas fa-eye"></i>
                     </span>
                    <?php display_error_bs('password', $errors); ?>
                </div>
                <div class="form-floating mb-3 position-relative">
                    <input type="password" class="form-control <?= error_class_bs('password_confirm', $errors) ?>" id="password_confirm" name="password_confirm" placeholder="Xác nhận mật khẩu" required>
                    <label for="password_confirm">Xác nhận mật khẩu</label>
                    <span class="password-toggle" onclick="togglePasswordVisibility('password_confirm', this)">
                         <i class="fas fa-eye"></i>
                     </span>
                    <?php display_error_bs('password_confirm', $errors); ?>
                </div>
                <button class="w-100 btn btn-lg btn-primary" type="submit">Đăng ký</button>
            </form>

            <div class="text-center mt-4">
                <small class="text-muted">Đã có tài khoản? <a href="?page=login" class="text-decoration-none">Đăng nhập</a></small>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="public/js/register.js"></script>


</body>
</html>
