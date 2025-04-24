<?php
// Web/app/Views/profile.php
$pageTitle = $pageTitle ?? 'Hồ sơ của bạn';
include_once __DIR__ . '/../layout/header.php';

if (!isset($_SESSION['user_id']) || !isset($user) || !$user) {
    echo "<div class='alert alert-warning'>Vui lòng đăng nhập để xem hồ sơ.</div>";
    include_once __DIR__ . '/../layout/footer.php';
    exit;
}

$username = htmlspecialchars($user['username'] ?? 'N/A');
$email = htmlspecialchars($user['email'] ?? 'N/A');
$createdAt = isset($user['created_at']) ? date('d/m/Y H:i', strtotime($user['created_at'])) : 'N/A';
$flashMessage = $flashMessage ?? null; // Flash message is handled by header
?>
    <style>
        /* Optional: Custom profile styles */
        .profile-actions .list-group-item i { width: 20px; text-align: center; }
    </style>

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <div class="text-center mb-4">
                    <h1>Hồ sơ của bạn</h1>
                    <p class="lead text-muted">Quản lý thông tin cá nhân và các hoạt động của bạn.</p>
                </div>

                <?php // Flash message display is handled by header.php ?>

                <div class="row g-4">
                    <?php // Info Column ?>
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header"><h2 class="h5 mb-0">Thông tin cá nhân</h2></div>
                            <div class="card-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4 text-sm-end">Tên đăng nhập:</dt>
                                    <dd class="col-sm-8"><?= $username ?></dd>

                                    <dt class="col-sm-4 text-sm-end">Email:</dt>
                                    <dd class="col-sm-8"><?= $email ?></dd>

                                    <dt class="col-sm-4 text-sm-end">Ngày tham gia:</dt>
                                    <dd class="col-sm-8"><?= $createdAt ?></dd>
                                </dl>
                                <?php // Optional Edit Button:
                                /*
                                <div class="text-end mt-3">
                                   <a href="?page=edit_profile" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-pencil-alt me-1"></i>Chỉnh sửa
                                    </a>
                                </div>
                                */
                                ?>
                            </div>
                        </div>
                    </div>

                    <?php // Actions Column ?>
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100 profile-actions">
                            <div class="card-header"><h2 class="h5 mb-0">Quản lý & Hoạt động</h2></div>
                            <div class="list-group list-group-flush">
                                <a href="?page=change_password" class="list-group-item list-group-item-action">
                                    <i class="fas fa-key me-2 text-primary"></i>Thay đổi mật khẩu
                                </a>
                                <a href="?page=order_history" class="list-group-item list-group-item-action">
                                    <i class="fas fa-history me-2 text-info"></i>Lịch sử đơn hàng
                                </a>
                                <a href="?page=wishlist" class="list-group-item list-group-item-action">
                                    <i class="fas fa-heart me-2 text-danger"></i>Danh sách yêu thích
                                </a>
                                <?php // Optional Address Link
                                /*
                                <a href="?page=manage_addresses" class="list-group-item list-group-item-action">
                                    <i class="fas fa-map-marker-alt me-2 text-success"></i>Quản lý địa chỉ
                                </a>
                                */
                                ?>
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

            </div> <?php // End outer col ?>
        </div> <?php // End outer row ?>
    </div> <?php // End container ?>

<?php
include_once __DIR__ . '/../layout/footer.php';
?>