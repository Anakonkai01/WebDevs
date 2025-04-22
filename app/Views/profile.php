<?php
// Web/app/Views/profile.php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// Chuyển hướng nếu chưa đăng nhập (Controller đã xử lý, đây là lớp bảo vệ thêm)
if (!isset($_SESSION['user_id'])) {
    header('Location: ?page=login');
    exit;
}

$username = $_SESSION['username'] ?? 'User'; // Lấy username từ session
$flashMessage = $flashMessage ?? null; // Lấy thông báo từ controller
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ của bạn</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f8f9fa; line-height: 1.6; }
        .container { max-width: 700px; margin: 40px auto; background-color: #fff; padding: 30px 40px; border-radius: 8px; box-shadow: 0 1px 5px rgba(0,0,0,0.1); }
        h1 { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 25px; text-align: center; color: #333; }
        h2 { margin-top: 30px; margin-bottom: 15px; color: #0056b3; font-size: 1.3em;}
        ul { list-style: none; padding: 0; }
        li { margin-bottom: 12px; }
        li a { color: #007bff; text-decoration: none; font-weight: 500; font-size: 1.05em; }
        li a:hover { text-decoration: underline; }
        .welcome-msg { font-size: 1.1em; margin-bottom: 20px; }
        .logout-link { display: block; text-align: center; margin-top: 30px; }
        /* Flash message style */
        .flash-message { padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid transparent; text-align: center; }
        .flash-message.success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .flash-message.error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
    </style>
</head>
<body>
<div class="container">
    <h1>Hồ sơ của bạn</h1>

    <?php // Hiển thị thông báo (ví dụ: đổi MK thành công) ?>
    <?php if ($flashMessage): ?>
        <div class="flash-message <?= htmlspecialchars($flashMessage['type']) ?>">
            <?= htmlspecialchars($flashMessage['message']) ?>
        </div>
    <?php endif; ?>

    <p class="welcome-msg">Xin chào, <strong><?= htmlspecialchars($username) ?></strong>!</p>

    <h2>Quản lý tài khoản</h2>
    <ul>
        <li><a href="?page=change_password">Thay đổi mật khẩu</a></li>
        <?php // Thêm link quản lý thông tin khác nếu cần ?>
    </ul>

    <h2>Đơn hàng</h2>
    <ul>
        <li><a href="?page=order_history">Xem lịch sử đơn hàng</a></li>
    </ul>

    <div class="logout-link">
        <a href="?page=logout">Đăng xuất</a>
    </div>
</div>
</body>
</html>