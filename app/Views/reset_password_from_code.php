<?php
// Web/app/Views/reset_password_from_code.php
$errors = $errors ?? [];
$flashMessage = $flashMessage ?? null;
$pageTitle = $pageTitle ?? 'Đặt Lại Mật Khẩu Mới';

function display_error_bs_rpfc($field, $errors) { if (isset($errors[$field])) { echo '<div class="invalid-feedback d-block small mt-1">' . htmlspecialchars($errors[$field]) . '</div>'; } }
function error_class_bs_rpfc($field, $errors) { return isset($errors[$field]) ? 'is-invalid' : ''; }

// Quyết định dùng layout hay trang độc lập
$useLayout = false;

if ($useLayout) {
    include_once __DIR__ . '/layout/header.php';
} else { ?>
<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= htmlspecialchars($pageTitle) ?></title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style> body { background-color: #eef2f7; } .reset-container { min-height: 100vh; } .reset-card { max-width: 480px; width: 100%; border: none; border-radius: 0.75rem; } .reset-card .card-body { padding: 2rem 2.5rem; } .password-input-group .form-control {} .password-toggle-btn { background-color: #fff; color: #6c757d; cursor: pointer; border: 1px solid #ced4da; border-left: none; } .password-toggle-btn:hover, .password-toggle-btn:focus { background-color: #f8f9fa; box-shadow: none; z-index: 3; } .input-group.has-validation .invalid-feedback { width: 100%; margin-top: 0.25rem; display: block; text-align: left; } </style>
</head><body class="d-flex justify-content-center align-items-center reset-container p-3 p-md-4">
<?php } ?>

<div class="card shadow-lg reset-card">
    <div class="card-body">
        <div class="text-center mb-4">
             <a href="?page=home" class="text-decoration-none"><h1 class="h2 fw-bold text-primary mb-2">MyShop</h1></a>
            <p class="text-muted">Đặt Lại Mật Khẩu Mới</p>
        </div>

        <?php if ($flashMessage && is_array($flashMessage)): ?>
            <div class="alert alert-<?= htmlspecialchars($flashMessage['type'] ?? 'info') ?> alert-dismissible fade show small" role="alert">
                <?= htmlspecialchars($flashMessage['message'] ?? '') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($errors['database'])): ?>
            <div class="alert alert-danger small" role="alert"><?= htmlspecialchars($errors['database']) ?></div>
        <?php endif; ?>

        <form action="?page=handle_reset_password_from_code" method="POST" novalidate>
            <?php // Không cần trường token ẩn ở đây ?>

            <div class="mb-3">
                <label for="new_password" class="form-label visually-hidden">Mật khẩu mới</label>
                <div class="input-group input-group-lg has-validation password-input-group">
                    <span class="input-group-text"><i class="fas fa-lock fa-fw"></i></span>
                    <input type="password" class="form-control <?= error_class_bs_rpfc('new_password', $errors) ?>" id="new_password" name="new_password" placeholder="Mật khẩu mới (ít nhất 6 ký tự)" required autofocus>
                    <button class="btn btn-outline-secondary password-toggle-btn" type="button" onclick="togglePasswordVisibility('new_password', this)" aria-label="Hiện/Ẩn mật khẩu">
                        <i class="fas fa-eye"></i>
                    </button>
                     <?php display_error_bs_rpfc('new_password', $errors); ?>
                </div>
            </div>

            <div class="mb-4">
                <label for="confirm_new_password" class="form-label visually-hidden">Xác nhận mật khẩu mới</label>
                <div class="input-group input-group-lg has-validation password-input-group">
                    <span class="input-group-text"><i class="fas fa-check-circle fa-fw"></i></span>
                    <input type="password" class="form-control <?= error_class_bs_rpfc('confirm_new_password', $errors) ?>" id="confirm_new_password" name="confirm_new_password" placeholder="Xác nhận lại mật khẩu mới" required>
                     <button class="btn btn-outline-secondary password-toggle-btn" type="button" onclick="togglePasswordVisibility('confirm_new_password', this)" aria-label="Hiện/Ẩn mật khẩu">
                        <i class="fas fa-eye"></i>
                    </button>
                    <?php display_error_bs_rpfc('confirm_new_password', $errors); ?>
                </div>
            </div>

            <button class="w-100 btn btn-primary btn-lg" type="submit">
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
<?php // Nhúng JS chứa hàm togglePasswordVisibility ?>
<script> function togglePasswordVisibility(inputId, buttonElement) { const input = document.getElementById(inputId); const icon = buttonElement.querySelector('i'); if (!input || !icon) return; if (input.type === "password") { input.type = "text"; icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); } else { input.type = "password"; icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); } } </script>
</body></html>
<?php else: include_once __DIR__ . '/layout/footer.php'; endif; ?>