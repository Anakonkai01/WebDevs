<?php
// set page title
$pageTitle = $pageTitle ?? 'Your Profile';

// include header file
include_once __DIR__ . '/layout/header.php';

// check user login
if (!isset($_SESSION['user_id']) || !isset($user) || !$user) {
    // show message if user not logged in
    echo "<div class='alert alert-warning'>Please log in to view your profile.</div>";
    include_once __DIR__ . '/layout/footer.php';
    exit;
}

// get user info
$pageTitle = "Your Profile";
$username = htmlspecialchars($user['username'] ?? 'N/A');
$email = htmlspecialchars($user['email'] ?? 'N/A');
$createdAt = isset($user['created_at']) ? date('d/m/Y H:i', strtotime($user['created_at'])) : 'N/A';
?>

<link rel="stylesheet" href="/webfinal/public/css/profile.css">

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header"><h2 class="h5 mb-0">Personal Information</h2></div>
                            <div class="card-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4 text-sm-end">Username:</dt>
                                    <dd class="col-sm-8"><?= $username ?></dd>

                                    <dt class="col-sm-4 text-sm-end">Email:</dt>
                                    <dd class="col-sm-8"><?= $email ?></dd>

                                    <dt class="col-sm-4 text-sm-end">Ngày tham gia:</dt>
                                    <dd class="col-sm-8"><?= $createdAt ?></dd>
                                </dl>

                            </div>
                        </div>
                    </div>

                    <?php // Actions Column ?>
                    <!-- User Actions Section -->
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100 profile-actions">
                            <div class="card-header"><h2 class="h5 mb-0">Manage & Activity</h2></div>
                            <div class="list-group list-group-flush">
                                <a href="?page=edit_profile" class="list-group-item list-group-item-action">
                                    <i class="fas fa-user-edit me-2 text-warning"></i>Edit Profile (Username/Email)
                                </a>
                                <a href="?page=change_password" class="list-group-item list-group-item-action">
                                    <i class="fas fa-key me-2 text-primary"></i>Change Password
                                </a>
                                <a href="?page=order_history" class="list-group-item list-group-item-action">
                                    <i class="fas fa-history me-2 text-info"></i>Order History
                                </a>
                                <a href="?page=wishlist" class="list-group-item list-group-item-action">
                                    <i class="fas fa-heart me-2 text-danger"></i>Wishlist
                                </a>
                                <a href="?page=logout" class="list-group-item list-group-item-action text-danger" onclick="return confirm('Bạn chắc chắn muốn đăng xuất?')">
                                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                </a>
                            </div>
                        </div>
                    </div>
                </div> <?php // End inner row ?>

                <div class="mt-4 text-center">
                    <a href="?page=shop_grid" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left me-1"></i> Quay lại Cửa hàng</a>
                    <a href="?page=home" class="btn btn-primary"><i class="fas fa-home me-1"></i> Về Trang chủ</a>
                </div>
            </div>
        </div>
    </div>

<?php
// include footer file
include_once __DIR__ . '/layout/footer.php';