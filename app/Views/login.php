<?php
// Web/app/Views/login.php
$errorMessage = $errorMessage ?? null;
// No need for full header/footer for this simple page, but include Bootstrap CSS
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link href="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f8f9fa; /* Light background */
        }
        .login-container {
            max-width: 400px;
            width: 100%;
        }
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

            <?php if ($errorMessage): ?>
                <div class="alert alert-danger small" role="alert"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <form action="?page=handle_login" method="POST">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Tên đăng nhập" required>
                    <label for="username">Tên đăng nhập</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Mật khẩu" required>
                    <label for="password">Mật khẩu</label>
                </div>
                <?php // Optional: Add "Remember me" checkbox ?>
                <button class="w-100 btn btn-lg btn-primary" type="submit">Đăng nhập</button>
            </form>

            <div class="text-center mt-4">
                <small class="text-muted">Chưa có tài khoản? <a href="?page=register" class="text-decoration-none">Đăng ký ngay</a></small>
            </div>
        </div>
    </div>
</div>
<script src="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>