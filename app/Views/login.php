<?php
// Web/app/Views/login.php
$errorMessage = $errorMessage ?? null;
$flashMessage = $flashMessage ?? null;
$pageTitle = 'Đăng nhập';
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
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #f8f9fa; }
        .login-container { max-width: 400px; width: 100%; }
        .password-toggle { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6c757d; z-index: 5; }
    </style>
</head>
<body>
<div class="login-container p-4">
    <div class="card shadow-sm">
        <div class="card-body p-4 p-lg-5">
            <div class="text-center mb-4">
                <a href="?page=home" class="navbar-brand fw-bold fs-4 mb-3 d-inline-block">MyShop</a>
                <h1 class="h3 mb-3 fw-normal">Đăng nhập</h1>
            </div>

            <?php if ($flashMessage && is_array($flashMessage)): ?>
                <div class="alert alert-<?= htmlspecialchars($flashMessage['type'] ?? 'info') ?> alert-dismissible fade show small" role="alert">
                    <?= htmlspecialchars($flashMessage['message'] ?? '') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger small" role="alert"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <form action="?page=handle_login" method="POST">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Tên đăng nhập hoặc Email" required autofocus>
                    <label for="username">Tên đăng nhập hoặc Email</label>
                </div>
                <div class="form-floating mb-3 position-relative">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Mật khẩu" required>
                    <label for="password">Mật khẩu</label>
                    <span class="password-toggle" onclick="togglePasswordVisibility('password', this)">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <button class="w-100 btn btn-lg btn-primary mt-3" type="submit">Đăng nhập</button>
            </form>

            <div class="text-center mt-4">
                <small class="text-muted">Chưa có tài khoản? <a href="?page=register" class="text-decoration-none">Đăng ký ngay</a></small>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
    function togglePasswordVisibility(inputId, iconElement) {
        const input = document.getElementById(inputId);
        const icon = iconElement.querySelector('i');
        if (!input || !icon) return;

        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
</body>
</html>