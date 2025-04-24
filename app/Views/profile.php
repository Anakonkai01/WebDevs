<?php
// Web/app/Views/profile.php

// --- START: Include Header ---
$pageTitle = $pageTitle ?? 'Hồ sơ của bạn'; // Lấy tiêu đề từ Controller hoặc đặt mặc định
include_once __DIR__ . '/../layout/header.php';
// --- END: Include Header ---


// Chuyển hướng nếu chưa đăng nhập hoặc không có dữ liệu user (Controller đã xử lý, đây là lớp bảo vệ thêm)
if (!isset($_SESSION['user_id']) || !isset($user) || !$user) {
    // Thông báo lỗi nhẹ nhàng hơn vì Controller đã có redirect chính
    echo "<p>Vui lòng đăng nhập để xem hồ sơ.</p>";
    // Include footer và dừng
    include_once __DIR__ . '/../layout/footer.php';
    exit;
}

// Lấy dữ liệu user và flash message từ controller
$username = htmlspecialchars($user['username'] ?? 'N/A');
$email = htmlspecialchars($user['email'] ?? 'N/A');
$createdAt = isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : 'N/A';
$flashMessage = $flashMessage ?? null;

?>
    <style>
        /* Cập nhật CSS cho trang profile */
        .profile-container { max-width: 800px; margin: 20px auto; /* Giảm margin top */ }
        .profile-header { text-align: center; margin-bottom: 30px; }
        .profile-section { background-color: #fff; padding: 25px 30px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); margin-bottom: 25px; }
        .profile-section h2 { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; font-size: 1.4em; color: #333; margin-top: 0;}
        .info-list dt { font-weight: bold; color: #555; width: 120px; float: left; clear: left; margin-bottom: 10px;}
        .info-list dd { margin-left: 130px; margin-bottom: 10px; color: #333; }
        .profile-actions ul { list-style: none; padding: 0; margin: 0; }
        .profile-actions li { margin-bottom: 12px; }
        .profile-actions li a { color: #007bff; text-decoration: none; font-weight: 500; font-size: 1.05em; display: block; /* Link thành block */ padding: 5px 0; }
        .profile-actions li a:hover { text-decoration: underline; color: #0056b3; }
        .profile-actions i.fas { margin-right: 8px; width: 15px; /* Icon căn chỉnh */ }

        .navigation-links { margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee; display: flex; justify-content: space-between; gap: 15px; flex-wrap: wrap; }
        .navigation-links a {
            color: #fff;
            background-color: #6c757d; /* Màu xám */
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.95em;
            transition: background-color 0.2s ease;
        }
        .navigation-links a:hover {
            background-color: #5a6268;
            text-decoration: none;
        }
        .navigation-links a.home-link {
            background-color: #007bff; /* Màu xanh */
        }
        .navigation-links a.home-link:hover {
            background-color: #0056b3;
        }
        .logout-link { text-align: center; margin-top: 30px; }
        .logout-link a { color: #dc3545; font-weight: bold; }
        .logout-link a:hover { color: #c82333; }

        /* Flash message style */
        .flash-message { padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid transparent; text-align: center; }
        .flash-message.success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .flash-message.error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .flash-message.info { background-color: #d1ecf1; color: #0c5460; border-color: #bee5eb; }
    </style>

    <div class="profile-container">
        <?php /* Bỏ thẻ container cũ nếu header đã có */ ?>

        <div class="profile-header">
            <h1>Hồ sơ của bạn</h1>
            <p>Quản lý thông tin cá nhân và các hoạt động của bạn trên MyShop.</p>
        </div>

        <?php // Hiển thị thông báo (ví dụ: đổi MK thành công) ?>
        <?php if ($flashMessage): ?>
            <div class="flash-message <?= htmlspecialchars($flashMessage['type']) ?>">
                <?= htmlspecialchars($flashMessage['message']) ?>
            </div>
        <?php endif; ?>

        <div class="profile-section">
            <h2>Thông tin cá nhân</h2>
            <dl class="info-list">
                <dt>Tên đăng nhập:</dt>
                <dd><?= $username ?></dd>

                <dt>Email:</dt>
                <dd><?= $email ?></dd>

                <dt>Ngày tham gia:</dt>
                <dd><?= $createdAt ?></dd>
            </dl>
            <?php /*
         // Nút chỉnh sửa thông tin (nếu có chức năng này sau)
         <div style="text-align: right; margin-top: 15px;">
             <a href="?page=edit_profile" style="font-size: 0.9em;">Chỉnh sửa thông tin</a>
         </div>
         */ ?>
        </div>

        <div class="profile-section profile-actions">
            <h2>Quản lý tài khoản & Đơn hàng</h2>
            <ul>
                <li><a href="?page=change_password"><i class="fas fa-key"></i>Thay đổi mật khẩu</a></li>
                <li><a href="?page=order_history"><i class="fas fa-history"></i>Xem lịch sử đơn hàng</a></li>
                <li><a href="?page=wishlist"><i class="fas fa-heart"></i>Danh sách yêu thích</a></li>
                <?php // Thêm link quản lý địa chỉ nếu cần ?>
                <?php /* <li><a href="?page=manage_addresses"><i class="fas fa-map-marker-alt"></i>Quản lý địa chỉ</a></li> */ ?>
            </ul>
        </div>

        <div class="navigation-links">
            <a href="?page=shop_grid"><i class="fas fa-arrow-left"></i> Quay lại Cửa hàng</a>
            <a href="?page=home" class="home-link"><i class="fas fa-home"></i> Về Trang chủ</a>
        </div>

        <div class="logout-link">
            <a href="?page=logout" onclick="return confirm('Bạn chắc chắn muốn đăng xuất?')"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>

    </div> <?php // End profile-container ?>

<?php
// --- START: Include Footer ---
include_once __DIR__ . '/../layout/footer.php';
// --- END: Include Footer ---
?>