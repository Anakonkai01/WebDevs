<?php
// Web/app/Views/reset_password.php
$token = $token ?? ''; // Lấy token từ controller
$errors = $_SESSION['form_errors'] ?? []; // Lấy lỗi từ session nếu redirect về
if (!empty($errors)) unset($_SESSION['form_errors']); // Xóa lỗi sau khi đọc

$pageTitle = $pageTitle ?? 'Đặt Lại Mật Khẩu';

// Helper functions (copy từ register.php nếu cần)
function display_error_bs_rpw($field, $errors) { if (isset($errors[$field])) { echo '<div class="invalid-feedback d-block">' . htmlspecialchars($errors[$field]) . '</div>'; } }
function error_class_bs_rpw($field, $errors) { return isset($errors[$field]) ? 'is-invalid' : ''; }

// Quyết định dùng layout hay trang độc lập
$useLayout = false; // Đặt là true nếu muốn dùng header/footer chung

if ($useLayout) {
    include_once __DIR__ . '/../layout/header.php';
} else { ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/webfinal/public/css/reset_password.css"> <style>
        body { background-color: #eef2f7; }
        .reset-pw-container { min-height: 100vh; }
        .reset-pw-card { max-width: 480px; width: 100%; border: none; border-radius: 0.75rem; }
        .reset-pw-card .card-body { padding: 2rem 2.5rem; }
         /* CSS cho nút toggle password (giống login.css/register.css) */
        .password-input-group .form-control { /* border-right: none; */ }
        .password-toggle-btn { /* border-left: none !important; */ background-color: #fff; color: #6c757d; cursor: pointer; border: 1px solid #ced4da; border-left: none; }
        .password-toggle-btn:hover, .password-toggle-btn:focus { background-color: #f8f9fa; box-shadow: none; z-index: 3; }
        .input-group.has-validation .invalid-feedback { width: 100%; margin-top: 0.25rem; display: block; text-align: left; }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center reset-pw-container p-3 p-md-4">
<?php } ?>

<div class="card shadow-lg reset-pw-card">
    <div class="card-body">
        <div class="text-center mb-4">
            <a href="?page=home" class="text-decoration-none">
                 <h1 class="h2 fw-bold text-primary mb-2">MyShop</h1>
            </a>
            <p class="text-muted">Đặt Lại Mật Khẩu</p>
        </div>

        <?php // Flash message đã xử lý trong header (nếu dùng layout) hoặc tự xử lý nếu cần ?>
        <?php // Hiển thị lỗi validation từ session (nếu có) ?>
         <?php if (isset($errors['database'])): ?>
             <div class="alert alert-danger small" role="alert"><?= htmlspecialchars($errors['database']) ?></div>
         <?php endif; ?>

        <form action="?page=handle_reset_password" method="POST" novalidate>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="mb-3">
                <label for="new_password" class="form-label visually-hidden">Mật khẩu mới</label>
                <div class="input-group has-validation password-input-group">
                    <span class="input-group-text"><i class="fas fa-lock fa-fw"></i></span>
                    <input type="password" class="form-control <?= error_class_bs_rpw('new_password', $errors) ?>" id="new_password" name="new_password" placeholder="Mật khẩu mới (ít nhất 6 ký tự)" required autofocus>
                     <button class="btn btn-outline-secondary password-toggle-btn" type="button" onclick="togglePasswordVisibility('new_password', this)" aria-label="Hiện/Ẩn mật khẩu">
                         <i class="fas fa-eye"></i>
                     </button>
                    <?php display_error_bs_rpw('new_password', $errors); ?>
                </div>
            </div>

             <div class="mb-4">
                <label for="confirm_new_password" class="form-label visually-hidden">Xác nhận mật khẩu mới</label>
                <div class="input-group has-validation password-input-group">
                    <span class="input-group-text"><i class="fas fa-check-circle fa-fw"></i></span>
                    <input type="password" class="form-control <?= error_class_bs_rpw('confirm_new_password', $errors) ?>" id="confirm_new_password" name="confirm_new_password" placeholder="Xác nhận lại mật khẩu mới" required>
                     <button class="btn btn-outline-secondary password-toggle-btn" type="button" onclick="togglePasswordVisibility('confirm_new_password', this)" aria-label="Hiện/Ẩn mật khẩu">
                         <i class="fas fa-eye"></i>
                     </button>
                     <?php display_error_bs_rpw('confirm_new_password', $errors); ?>
                </div>
            </div>

            <button class="w-100 btn btn-lg btn-primary" type="submit">
                 <i class="fas fa-save me-2"></i>Đặt Lại Mật Khẩu
            </button>
        </form>
        <div class="text-center mt-4">
            <small><a href="?page=login" class="text-decoration-none">Quay lại Đăng nhập</a></small>
        </div>
    </div>
</div>

<?php if (!$useLayout): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
     <script>
        // Copy hàm togglePasswordVisibility từ login.js/register.js vào đây
        function togglePasswordVisibility(inputId, buttonElement) {
            const input = document.getElementById(inputId);
            const icon = buttonElement.querySelector('i');
            if (!input || !icon) return;

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
<?php else:
    // Nếu dùng layout, đảm bảo footer.php có load JS chứa hàm togglePasswordVisibility
    // Hoặc bạn có thể nhúng script đó riêng vào đây nếu footer chưa có
    include_once __DIR__ . '/../layout/footer.php';
endif; ?>