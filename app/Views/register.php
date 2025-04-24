<?php
// Web/app/Views/register.php
$flashMessage = $flashMessage ?? null;
$errors = $errors ?? [];
$old = $old ?? [];

function display_error_bs($field, $errors) {
    if (isset($errors[$field])) {
        echo '<div class="invalid-feedback">' . htmlspecialchars($errors[$field]) . '</div>';
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
    <title>Đăng ký tài khoản</title>
    <link href="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #f8f9fa; }
        .register-container { max-width: 450px; width: 100%; }
        .form-floating > .form-control:not(:placeholder-shown) ~ label { /* Adjust floating label */
            opacity: .65; transform: scale(.85) translateY(-.5rem) translateX(.15rem);
        }
        .form-floating > .form-control.is-invalid ~ label { color: #dc3545; }
    </style>
</head>
<body>
<div class="register-container p-4">
    <div class="card shadow-sm">
        <div class="card-body p-4 p-lg-5">
            <div class="text-center mb-4">
                <a href="?page=home" class="navbar-brand fw-bold fs-4 mb-3 d-inline-block">MyShop</a>
                <h1 class="h3 mb-3 fw-normal">Tạo tài khoản mới</h1>
            </div>

            <?php // Flash message display is handled by header.php if included, or use Bootstrap alert here if standalone ?>
            <?php if ($flashMessage && is_array($flashMessage) && $flashMessage['type'] !== 'success'): // Show non-success messages here ?>
                <div class="alert alert-<?= htmlspecialchars($flashMessage['type'] ?? 'info') ?> small" role="alert">
                    <?= htmlspecialchars($flashMessage['message'] ?? '') ?>
                </div>
            <?php endif; ?>

            <form action="?page=handle_register" method="POST">
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
                <div class="form-floating mb-3">
                    <input type="password" class="form-control <?= error_class_bs('password', $errors) ?>" id="password" name="password" placeholder="Mật khẩu" required>
                    <label for="password">Mật khẩu (ít nhất 6 ký tự)</label>
                    <?php display_error_bs('password', $errors); ?>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control <?= error_class_bs('password_confirm', $errors) ?>" id="password_confirm" name="password_confirm" placeholder="Xác nhận mật khẩu" required>
                    <label for="password_confirm">Xác nhận mật khẩu</label>
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
<script src="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>