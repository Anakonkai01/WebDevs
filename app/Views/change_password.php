<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in, redirect to login if not
if (!isset($_SESSION['user_id'])) {
    header('Location: ?page=login');
    exit;
}

// Get errors from session
$errors = $_SESSION['form_errors'] ?? [];
if (!empty($errors)) {
    unset($_SESSION['form_errors']); // Clear errors
}

// Get flash message from session
$flashMessage = $_SESSION['flash_message'] ?? null;
if ($flashMessage) {
    unset($_SESSION['flash_message']); // Clear flash message
}

// Display error message for a field
function display_error_bs_cpw($field, $errors)
{
    if (isset($errors[$field])) {
        echo '<div class="invalid-feedback d-block small mt-1">' . htmlspecialchars($errors[$field]) . '</div>';
    }
}
// Return CSS class if error exists
function error_class_bs_cpw($field, $errors)
{
    return isset($errors[$field]) ? 'is-invalid' : '';
}

// Determine whether to use the layout (header/footer).
$useLayout = false;

if ($useLayout) {
    $pageTitle = $pageTitle ?? 'Thay đổi mật khẩu';
    include_once __DIR__ . '/layout/header.php';
} else {
?>
    <!DOCTYPE html> <html lang="vi"> <head> <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Thay đổi mật khẩu</title> <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            body { background-color: #eef2f7; }
            .change-pw-container { min-height: 100vh; }
            .change-pw-card { max-width: 480px; width: 100%; border: none; border-radius: 0.75rem; }
            .change-pw-card .card-body { padding: 2rem 2.5rem; }
            .password-input-group .form-control {}
            .password-toggle-btn { background-color: #fff; color: #6c757d; cursor: pointer; border: 1px solid #ced4da; border-left: none; }
            .password-toggle-btn:hover, .password-toggle-btn:focus { background-color: #f8f9fa; box-shadow: none; z-index: 3; }
            .input-group.has-validation .invalid-feedback { width: 100%; margin-top: 0.25rem; display: block; text-align: left; }
        </style>
    </head>
    <body class="d-flex justify-content-center align-items-center change-pw-container p-3 p-md-4"> <?php
}
?> <!-- End of Header Section -->

    <div class="card shadow-lg change-pw-card">
        <div class="card-body">
            <div class="text-center mb-4">
                <a href="?page=home" class="text-decoration-none"><h1 class="h2 fw-bold text-primary mb-2">MyShop</h1></a>
                <p class="text-muted">Thay đổi mật khẩu</p>
            </div>

            <!-- Display flash message if available -->
            <?php if (!$useLayout && $flashMessage && is_array($flashMessage)): ?>
                <div class="alert alert-<?= htmlspecialchars($flashMessage['type'] ?? 'info') ?> alert-dismissible fade show small" role="alert">
                    <?= htmlspecialchars($flashMessage['message'] ?? '') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
             <!-- Display general database error if any. -->
            <?php if (isset($errors['database'])): ?>
                <div class="alert alert-danger small" role="alert"><?= htmlspecialchars($errors['database']) ?></div>
            <?php endif; ?>
            <!-- Change Password Form -->
            <form action="?page=handle_change_password" method="POST" novalidate>
                <?php // Input Group cho Mật khẩu hiện tại ?>
                <div class="mb-3">
                    <label for="current_password" class="form-label visually-hidden">Mật khẩu hiện tại</label>
                    <div class="input-group input-group-lg has-validation password-input-group">
                        <span class="input-group-text"><i class="fas fa-key fa-fw"></i></span>
                        <input type="password" class="form-control <?= error_class_bs_cpw('current_password', $errors) ?>" id="current_password" name="current_password" placeholder="Mật khẩu hiện tại" required autofocus>
                        <button class="btn btn-outline-secondary password-toggle-btn" type="button" onclick="togglePasswordVisibility('current_password', this)" aria-label="Hiện/Ẩn mật khẩu">
                            <i class="fas fa-eye"></i>
                        </button>
                        <?php display_error_bs_cpw('current_password', $errors); ?>
                    </div>
                </div>

                <?php // Input Group cho Mật khẩu mới ?>
                <div class="mb-3">
                    <label for="new_password" class="form-label visually-hidden">Mật khẩu mới</label>
                    <div class="input-group input-group-lg has-validation password-input-group">
                        <span class="input-group-text"><i class="fas fa-lock fa-fw"></i></span>
                        <input type="password" class="form-control <?= error_class_bs_cpw('new_password', $errors) ?>" id="new_password" name="new_password" placeholder="Mật khẩu mới (ít nhất 6 ký tự)" required>
                        <button class="btn btn-outline-secondary password-toggle-btn" type="button" onclick="togglePasswordVisibility('new_password', this)" aria-label="Hiện/Ẩn mật khẩu">
                            <i class="fas fa-eye"></i>
                        </button>
                        <?php display_error_bs_cpw('new_password', $errors); ?>
                    </div>
                </div>

                <?php // Input Group cho Xác nhận mật khẩu mới ?>
                <div class="mb-4">
                    <label for="confirm_new_password" class="form-label visually-hidden">Xác nhận mật khẩu mới</label>
                    <div class="input-group input-group-lg has-validation password-input-group">
                        <span class="input-group-text"><i class="fas fa-check-circle fa-fw"></i></span>
                        <input type="password" class="form-control <?= error_class_bs_cpw('confirm_new_password', $errors) ?>" id="confirm_new_password" name="confirm_new_password" placeholder="Xác nhận lại mật khẩu mới" required>
                         <button class="btn btn-outline-secondary password-toggle-btn" type="button" onclick="togglePasswordVisibility('confirm_new_password', this)" aria-label="Hiện/Ẩn mật khẩu">
                            <i class="fas fa-eye"></i>
                        </button>
                        <?php display_error_bs_cpw('confirm_new_password', $errors); ?>
                    </div>
                </div>

                <button class="w-100 btn btn-primary btn-lg" type="submit">
                    <i class="fas fa-save me-2"></i>Đổi mật khẩu
                </button>
            </form><!-- End form -->
            <div class="text-center mt-4 pt-3 border-top">
                <small><a href="?page=profile" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Quay lại Hồ sơ</a></small>
            </div>
        </div>
    </div>
<?php // End of Content / Body Section
if (!$useLayout) {
?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script> 
    <script> function togglePasswordVisibility(inputId, buttonElement) {
            // Retrieve the input element by ID.
            const input = document.getElementById(inputId);
            // Retrieve the icon element within the button.
            const icon = buttonElement.querySelector('i');
            // Check if the input and icon elements exist.
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
    </body> </html> <?php
} else { // If using layout, include the footer.
    include_once __DIR__ . '/layout/footer.php';
}

?>