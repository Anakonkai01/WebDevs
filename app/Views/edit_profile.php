<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ?page=login');
    exit;
}

// Get data from controller
$user = $user ?? null; 
$errors = $errors ?? []; 
$old = $old ?? []; 
$pageTitle = $pageTitle ?? 'Chỉnh sửa Hồ sơ'; 

// If user data not found
if (!$user) {
    include_once __DIR__ . '/layout/header.php';
    echo "<div class='container my-4'><div class='alert alert-danger'>Lỗi: Không thể tải thông tin người dùng.</div></div>";
    include_once __DIR__ . '/layout/footer.php';
    exit;
}
// Display error for field
function display_error_bs_edit(string $field, array $errors): void
{
    if (isset($errors[$field])) {
        echo '<div class="invalid-feedback">' . htmlspecialchars($errors[$field]) . '</div>';
    }
}

// Check error and return error class
function error_class_bs_edit(string $field, array $errors): string {
    return isset($errors[$field]) ? 'is-invalid' : '';
}

// Include header
include_once __DIR__ . '/layout/header.php';
echo '<link rel="stylesheet" href="/webfinal/public/css/edit_profile.css">'; 
?>

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-9">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h1 class="h4 mb-0 py-1">Chỉnh sửa Hồ sơ</h1>
                    </div>
                    <div class="card-body p-4">
                        <?php
                        // Check database error
                        if (isset($errors['database'])): ?>
                            <div class="alert alert-danger small" role="alert"><?= htmlspecialchars($errors['database']) ?></div>
                        <?php endif; ?>

                        <form action="?page=handle_update_profile" method="POST">
                        
                        <!--Username input-->
                            <div class="mb-3">
                                <label for="username" class="form-label">Tên đăng nhập:</label>
                                <input type="text" class="form-control <?= error_class_bs_edit('username', $errors) ?>" id="username" name="username" value="<?= htmlspecialchars($old['username'] ?? $user['username']) ?>" required>
                                <small class="form-text text-muted">Tên đăng nhập mới của bạn.</small>
                                <?php display_error_bs_edit('username', $errors); ?>
                            </div>

                             <!--Email input-->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" class="form-control <?= error_class_bs_edit('email', $errors) ?>" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? $user['email']) ?>" required>
                                <small class="form-text text-muted">Địa chỉ email mới.</small>
                                <?php display_error_bs_edit('email', $errors); ?>
                            </div>

                            <hr class="my-4">
                            
                            <!--Current Password input-->
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                                <input type="password" class="form-control <?= error_class_bs_edit('current_password', $errors) ?>" id="current_password" name="current_password" required>
                                <small class="form-text text-muted">Nhập mật khẩu hiện tại của bạn để xác nhận thay đổi.</small>
                                <?php display_error_bs_edit('current_password', $errors); ?>
                            </div>

                            <!--Button-->
                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-end mt-4">
                                <a href="?page=profile" class="btn btn-outline-secondary">Hủy bỏ</a>
                                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
include_once __DIR__ . '/layout/footer.php'; // Include the footer layout.