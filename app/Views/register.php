<?php
// Web/app/Views/register.php
$flashMessage = $flashMessage ?? null;
$errors = $errors ?? []; // Lỗi validation
$old = $old ?? []; // Dữ liệu form cũ

function display_error($field, $errors) {
    if (isset($errors[$field])) {
        echo '<span style="color: red; font-size: 0.9em;">' . htmlspecialchars($errors[$field]) . '</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản</title>
    <style>/* Copy style từ login.php và chỉnh sửa nếu cần */
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 90vh; background-color: #f4f4f4; }
        .register-container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 400px; }
        h1 { text-align: center; margin-bottom: 20px; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1em; }
        button:hover { background-color: #218838; }
        .flash-message { padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center;}
        .flash-message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash-message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .login-link { text-align: center; margin-top: 15px; }
        .login-link a { color: #007bff; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="register-container">
    <h1>Tạo tài khoản mới</h1>

    <?php if ($flashMessage): ?>
        <div class="flash-message <?= htmlspecialchars($flashMessage['type']) ?>">
            <?= htmlspecialchars($flashMessage['message']) ?>
        </div>
    <?php endif; ?>

    <form action="?page=handle_register" method="POST">
        <div class="form-group">
            <label for="username">Tên đăng nhập:</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($old['username'] ?? '') ?>" required>
            <?php display_error('username', $errors); ?>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
            <?php display_error('email', $errors); ?>
        </div>
        <div class="form-group">
            <label for="password">Mật khẩu (ít nhất 6 ký tự):</label>
            <input type="password" id="password" name="password" required>
            <?php display_error('password', $errors); ?>
        </div>
        <div class="form-group">
            <label for="password_confirm">Xác nhận mật khẩu:</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
            <?php display_error('password_confirm', $errors); ?>
        </div>
        <button type="submit">Đăng ký</button>
    </form>

    <div class="login-link">
        Đã có tài khoản? <a href="?page=login">Đăng nhập</a>
    </div>
</div>
</body>
</html>