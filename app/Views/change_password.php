<?php
// Web/app/Views/change_password.php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header('Location: ?page=login'); exit; }

$errors = $errors ?? [];
$flashMessage = $flashMessage ?? null; // Flash message is handled by header if layout is used

// Helper functions (copy from register.php if not using full layout)
function display_error_bs_cpw($field, $errors) { if (isset($errors[$field])) { echo '<div class="invalid-feedback">' . htmlspecialchars($errors[$field]) . '</div>'; } }
function error_class_bs_cpw($field, $errors) { return isset($errors[$field]) ? 'is-invalid' : ''; }

// Decide whether to use full layout or standalone page
$useLayout = true; // Set to false for standalone page like login

if ($useLayout) {
    $pageTitle = 'Thay đổi mật khẩu';
    include_once __DIR__ . '/../layout/header.php';
} else {
    // Minimal standalone HTML head
    ?>
    <!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Thay đổi mật khẩu</title>
        <link href="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
        <style> body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #f8f9fa; } .change-pw-container { max-width: 450px; width: 100%; } </style>
    </head><body>
    <?php
}
?>

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm change-pw-container">
                    <div class="card-body p-4 p-lg-5">
                        <h1 class="h3 mb-4 text-center">Thay đổi mật khẩu</h1>

                        <?php // Flash message display (handled by header if $useLayout is true) ?>
                        <?php if (!$useLayout && $flashMessage && $flashMessage['type'] === 'error'): ?>
                            <div class="alert alert-danger small" role="alert"> <?= htmlspecialchars($flashMessage['message']) ?> </div>
                        <?php endif; ?>

                        <form action="?page=handle_change_password" method="POST">
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control <?= error_class_bs_cpw('current_password', $errors) ?>" id="current_password" name="current_password" placeholder="Mật khẩu hiện tại" required>
                                <label for="current_password">Mật khẩu hiện tại</label>
                                <?php display_error_bs_cpw('current_password', $errors); ?>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control <?= error_class_bs_cpw('new_password', $errors) ?>" id="new_password" name="new_password" placeholder="Mật khẩu mới" required>
                                <label for="new_password">Mật khẩu mới (ít nhất 6 ký tự)</label>
                                <?php display_error_bs_cpw('new_password', $errors); ?>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control <?= error_class_bs_cpw('confirm_new_password', $errors) ?>" id="confirm_new_password" name="confirm_new_password" placeholder="Xác nhận mật khẩu mới" required>
                                <label for="confirm_new_password">Xác nhận mật khẩu mới</label>
                                <?php display_error_bs_cpw('confirm_new_password', $errors); ?>
                            </div>
                            <button class="w-100 btn btn-lg btn-primary" type="submit">Đổi mật khẩu</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="?page=profile" class="text-decoration-none small"><i class="fas fa-arrow-left me-1"></i> Quay lại Hồ sơ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
if ($useLayout) {
    include_once __DIR__ . '/../layout/footer.php';
} else {
    // Minimal standalone footer
    ?>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script></body></html>
    <?php
}
?>