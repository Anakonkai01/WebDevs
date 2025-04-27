<?php
// Function to display error message
function display_error_bs($field, $errors) {
    if (isset($errors[$field])) {
        echo '<div class="invalid-feedback d-block small mt-1">' . htmlspecialchars($errors[$field]) . '</div>';
    }
}

// Function to add error class
function error_class_bs($field, $errors) {
    return isset($errors[$field]) ? 'is-invalid' : '';
}
$errors = $errors ?? [];
$old = $old ?? [];
$flashMessage = $flashMessage ?? null;
$pageTitle = 'Đăng ký tài khoản - MyShop';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/register.css">
</head>
<body class="register-page-background">

    <div class="register-container d-flex justify-content-center align-items-center min-vh-100 p-3 p-md-4">
        <div class="card shadow-lg register-card">
            <div class="card-body p-4 p-lg-5">
                <!-- Logo/Shop Name -->
                <div class="text-center mb-4"> 
                    <a href="?page=home" class="text-decoration-none">
                        <h1 class="h2 fw-bold text-primary mb-2">MyShop</h1>
                    </a>
                    <p class="text-muted">Tạo tài khoản mới để mua sắm</p>
                </div>
                <!-- Flash Message Display -->
                <?php if (isset($flashMessage) && is_array($flashMessage)): ?>
                    <div class="alert alert-<?= htmlspecialchars($flashMessage['type'] ?? 'info') ?> alert-dismissible fade show small" role="alert">
                        <?= htmlspecialchars($flashMessage['message'] ?? '') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Registration Form --> 
                <form action="?page=handle_register" method="POST" novalidate>
                    <!-- Username Input Group -->
                    <div class="mb-3"> 
                        <label for="username" class="form-label visually-hidden">Tên đăng nhập</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="fas fa-user fa-fw"></i></span>
                            <input type="text" class="form-control <?= error_class_bs('username', $errors) ?>" id="username" name="username" placeholder="Tên đăng nhập (3-20 ký tự)" value="<?= htmlspecialchars($old['username'] ?? '') ?>" required>
                            <?php display_error_bs('username', $errors); ?>
                        </div>
                    </div>

                    <!-- Email Input Group -->
                    <div class="mb-3"> 
                        <label for="email" class="form-label visually-hidden">Email</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="fas fa-envelope fa-fw"></i></span>
                            <input type="email" class="form-control <?= error_class_bs('email', $errors) ?>" id="email" name="email" placeholder="Địa chỉ Email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                            <?php display_error_bs('email', $errors); ?>
                        </div>
                    </div>

                    <!-- Password Input Group -->
                    <div class="mb-3"> 
                        <label for="password" class="form-label visually-hidden">Mật khẩu</label>
                        <div class="input-group has-validation password-input-group">
                            <span class="input-group-text"><i class="fas fa-lock fa-fw"></i></span>
                            <input type="password" class="form-control <?= error_class_bs('password', $errors) ?>" id="password" name="password" placeholder="Mật khẩu (ít nhất 6 ký tự)" required>
                            <button class="btn btn-outline-secondary password-toggle-btn" type="button" onclick="togglePasswordVisibility('password', this)" aria-label="Hiện/Ẩn mật khẩu">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php display_error_bs('password', $errors); ?>
                        </div>
                    </div>

                    <!-- Confirm Password Input Group -->
                    <div class="mb-4"> 
                        <label for="password_confirm" class="form-label visually-hidden">Xác nhận mật khẩu</label>
                        <div class="input-group has-validation password-input-group">
                            <span class="input-group-text"><i class="fas fa-check-circle fa-fw"></i></span>
                            <input type="password" class="form-control <?= error_class_bs('password_confirm', $errors) ?>" id="password_confirm" name="password_confirm" placeholder="Xác nhận lại mật khẩu" required>
                            <button class="btn btn-outline-secondary password-toggle-btn" type="button" onclick="togglePasswordVisibility('password_confirm', this)" aria-label="Hiện/Ẩn mật khẩu">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php display_error_bs('password_confirm', $errors); ?>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button class="w-100 btn btn-primary btn-lg" type="submit"> 
                        <i class="fas fa-user-plus me-2"></i>Đăng Ký Ngay
                    </button>
                </form>

                <!-- Login Link -->
                <div class="text-center mt-4 pt-3 border-top">
                    <small class="text-muted">Đã có tài khoản? <a href="?page=login" class="text-decoration-none fw-medium">Đăng nhập tại đây</a></small> 
                </div>
            </div>
        </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/public/js/register.js"></script>

</body>
</html>