<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'shopping_cart');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// --- THÊM CẤU HÌNH EMAIL ---
define('MAIL_HOST', 'smtp.gmail.com'); // Thay bằng host của bạn
define('MAIL_PORT', 587);              // Thay bằng port của bạn (587 cho TLS, 465 cho SSL)
define('MAIL_USERNAME', 'nguyentranhoangnhan18@gmail.com'); // Thay bằng email của bạn
// !!! QUAN TRỌNG: Sử dụng Mật khẩu ứng dụng nếu bật 2FA !!!
define('MAIL_PASSWORD', 'hnej fzyu rcye vhng');     // Thay bằng mật khẩu SMTP/App Password MỚI
define('MAIL_ENCRYPTION', 'tls');      // 'tls' hoặc 'ssl'
define('MAIL_FROM_ADDRESS', 'nguyentranhoangnhan18@gmail.com'); // Email người gửi
define('MAIL_FROM_NAME', 'MyShop');       // Tên người gửi