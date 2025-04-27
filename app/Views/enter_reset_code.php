<?php
// Web/app/Views/enter_reset_code.php
$email = $email ?? null;
$errors = $errors ?? [];
$flashMessage = $flashMessage ?? null;
$pageTitle = $pageTitle ?? 'Nhập Mã Đặt Lại Mật Khẩu';

function display_error_bs_erc($field, $errors) { if (isset($errors[$field])) { echo '<div class="invalid-feedback d-block small mt-1">' . htmlspecialchars($errors[$field]) . '</div>'; } }
function error_class_bs_erc($field, $errors) { return isset($errors[$field]) ? 'is-invalid' : ''; }

// Quyết định dùng layout hay trang độc lập
$useLayout = false;

if ($useLayout) {
    include_once __DIR__ . '/layout/header.php';
} else { ?>
<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= htmlspecialchars($pageTitle) ?></title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style> body { background-color: #eef2f7; } .verify-container { min-height: 100vh; } .verify-card { max-width: 480px; width: 100%; border: none; border-radius: 0.75rem; } .verify-card .card-body { padding: 2rem 2.5rem; } </style>
</head><body class="d-flex justify-content-center align-items-center verify-container p-3 p-md-4">
<?php } ?>

<div class="card shadow-lg verify-card">
    <div class="card-body">
        <div class="text-center mb-4">
            <a href="?page=home" class="text-decoration-none"><h1 class="h2 fw-bold text-primary mb-2">MyShop</h1></a>
            <p class="text-muted">Nhập Mã Đặt Lại Mật Khẩu</p>
        </div>

        <?php if ($flashMessage && is_array($flashMessage)): ?>
            <div class="alert alert-<?= htmlspecialchars($flashMessage['type'] ?? 'info') ?> alert-dismissible fade show small" role="alert">
                <?= htmlspecialchars($flashMessage['message'] ?? '') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <p class="text-center small mb-3">
            Một mã gồm 6 chữ số đã được gửi đến email của bạn.
            Vui lòng nhập mã đó vào ô bên dưới.
        </p>

        <form action="?page=handle_enter_reset_code" method="POST" novalidate>
            <div class="mb-3">
                <label for="email_input" class="form-label visually-hidden">Địa chỉ Email</label>
                <div class="input-group input-group-lg has-validation">
                    <span class="input-group-text"><i class="fas fa-envelope fa-fw"></i></span>
                    <input type="email" class="form-control <?= error_class_bs_erc('email', $errors) ?>" id="email_input" name="email" placeholder="Nhập email của bạn" value="<?= htmlspecialchars($email ?? '') ?>" required>
                    <?php display_error_bs_erc('email', $errors); ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="reset_code" class="form-label visually-hidden">Mã đặt lại</label>
                <div class="input-group input-group-lg has-validation">
                    <span class="input-group-text"><i class="fas fa-key fa-fw"></i></span>
                    <input type="text" class="form-control text-center fs-4 <?= error_class_bs_erc('reset_code', $errors) ?>" id="reset_code" name="reset_code" placeholder="------" required maxlength="6" pattern="\d{6}" title="Nhập mã 6 chữ số">
                     <?php display_error_bs_erc('reset_code', $errors); ?>
                </div>
            </div>

            <button class="w-100 btn btn-primary btn-lg" type="submit">
                <i class="fas fa-check-circle me-2"></i>Xác nhận mã
            </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top">
            <small class="text-muted">Chưa nhận được mã? <a href="?page=forgot_password" class="text-decoration-none fw-medium">Gửi lại yêu cầu</a></small>
        </div>
    </div>
</div>

<?php if (!$useLayout): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body></html>
<?php else: include_once __DIR__ . '/layout/footer.php'; endif; ?>