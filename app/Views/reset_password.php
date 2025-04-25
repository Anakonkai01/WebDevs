<?php
// Web/app/Views/reset_password.php
$token = $token ?? ''; // Lấy token từ controller
$errors = $errors ?? [];
$pageTitle = $pageTitle ?? 'Đặt Lại Mật Khẩu';

// Helper functions (copy từ register.php nếu cần)
function display_error_bs_rpw($field, $errors) { if (isset($errors[$field])) { echo '<div class="invalid-feedback">' . htmlspecialchars($errors[$field]) . '</div>'; } }
function error_class_bs_rpw($field, $errors) { return isset($errors[$field]) ? 'is-invalid' : ''; }

include_once __DIR__ . '/../layout/header.php'; // Hoặc dùng cấu trúc trang riêng
?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <h1 class="h3 mb-4 text-center">Đặt Lại Mật Khẩu</h1>
                        <p class="text-center text-muted mb-4">Nhập mật khẩu mới cho tài khoản của bạn.</p>

                        <?php // Flash message đã xử lý trong header ?>

                        <form action="?page=handle_reset_password" method="POST">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                            <div class="form-floating mb-3">
                                <input type="password" class="form-control <?= error_class_bs_rpw('new_password', $errors) ?>" id="new_password" name="new_password" placeholder="Mật khẩu mới" required>
                                <label for="new_password">Mật khẩu mới (ít nhất 6 ký tự)</label>
                                <?php display_error_bs_rpw('new_password', $errors); ?>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control <?= error_class_bs_rpw('confirm_new_password', $errors) ?>" id="confirm_new_password" name="confirm_new_password" placeholder="Xác nhận mật khẩu mới" required>
                                <label for="confirm_new_password">Xác nhận mật khẩu mới</label>
                                <?php display_error_bs_rpw('confirm_new_password', $errors); ?>
                            </div>

                            <button class="w-100 btn btn-lg btn-primary" type="submit">Đặt Lại Mật Khẩu</button>
                        </form>
                        <div class="text-center mt-4">
                            <small><a href="?page=login" class="text-decoration-none">Quay lại Đăng nhập</a></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include_once __DIR__ . '/../layout/footer.php'; // Hoặc dùng cấu trúc trang riêng ?>