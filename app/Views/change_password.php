<?php
// Web/app/Views/change_password.php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// Chuyển hướng nếu chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ?page=login');
    exit;
}

$errors = $errors ?? []; // Lấy lỗi validation từ controller
$flashMessage = $flashMessage ?? null; // Lấy thông báo chung

// Hàm trợ giúp hiển thị lỗi
function display_error_cpw($field, $errors) {
    if (!empty($errors[$field])) {
        echo '<span style="color: red; font-size: 0.9em; display: block; margin-top: 3px;">' . htmlspecialchars($errors[$field]) . '</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thay đổi mật khẩu</title>
    <style> /* Style tương tự trang login/register */
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 80vh; background-color: #f4f4f4; }
        .change-pw-container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 400px; }
        h1 { text-align: center; margin-bottom: 25px; color: #333; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; font-weight: bold; color: #555; }
        input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 1em;}
        button { width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1.05em; margin-top: 10px;}
        button:hover { background-color: #0056b3; }
        .flash-message { padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center;}
        .flash-message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash-message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .profile-link { text-align: center; margin-top: 20px; font-size: 0.95em; }
        .profile-link a { color: #007bff; text-decoration: none; }
        .profile-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="change-pw-container">
    <h1>Thay đổi mật khẩu</h1>

    <?php // Hiển thị thông báo lỗi chung (nếu có) ?>
    <?php if ($flashMessage && $flashMessage['type'] === 'error'): ?>
        <div class="flash-message error">
            <?= htmlspecialchars($flashMessage['message']) ?>
        </div>
    <?php endif; ?>

    <form action="?page=handle_change_password" method="POST">
        <div class="form-group">
            <label for="current_password">Mật khẩu hiện tại:</label>
            <input type="password" id="current_password" name="current_password" required>
            <?php display_error_cpw('current_password', $errors); ?>
        </div>
        <div class="form-group">
            <label for="new_password">Mật khẩu mới (ít nhất 6 ký tự):</label>
            <input type="password" id="new_password" name="new_password" required>
            <?php display_error_cpw('new_password', $errors); ?>
        </div>
        <div class="form-group">
            <label for="confirm_new_password">Xác nhận mật khẩu mới:</label>
            <input type="password" id="confirm_new_password" name="confirm_new_password" required>
            <?php display_error_cpw('confirm_new_password', $errors); ?>
        </div>
        <button type="submit">Đổi mật khẩu</button>
    </form>
    <div class="profile-link">
        <a href="?page=profile">&laquo; Quay lại Hồ sơ</a>
    </div>
</div>
</body>
</html>