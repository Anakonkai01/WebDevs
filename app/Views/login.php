<?php
// Variables for the page
$errorMessage = $errorMessage ?? null; 
$flashMessage = $flashMessage ?? null; 
$pageTitle = 'Đăng nhập - MyShop';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title> 
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> 
    <link rel="stylesheet" href="/webfinal/public/css/login.css"> 
</head>
<body class="login-page-background">
<div class="login-container d-flex justify-content-center align-items-center min-vh-100 p-3 p-md-4">
    <div class="card shadow-lg login-card">
        <div class="card-body p-4 p-lg-5">
            <div class="text-center mb-4">
                <a href="?page=home" class="text-decoration-none"><h1 class="h2 fw-bold text-primary mb-2">MyShop</h1></a>
                <p class="text-muted">Chào mừng trở lại!</p>
            </div>
            <!-- Display flash message -->
            <?php if ($flashMessage && is_array($flashMessage)): ?>
                <div class="alert alert-<?= htmlspecialchars($flashMessage['type'] ?? 'info') ?> alert-dismissible fade show small" role="alert">
                    <?= htmlspecialchars($flashMessage['message'] ?? '') ?> 
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> 
                </div>
            <?php endif; ?>

            <!-- Display error message -->
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger small p-2" role="alert">
                    <i class="fas fa-exclamation-triangle me-1"></i> <?= htmlspecialchars($errorMessage) ?> 
                </div>
            <?php endif; ?>
            <!-- Login form -->
            <form action="?page=handle_login" method="POST" novalidate>
                <?php 
                 //Login form
                ?>
               
                
                 <!-- Input Group for Username/Email -->
                 <div class="mb-3">
                     <label for="username" class="form-label visually-hidden">Tên đăng nhập hoặc Email</label>
                     <div class="input-group input-group-lg"> 
                         <span class="input-group-text"><i class="fas fa-user fa-fw"></i></span>
                         <input type="text" class="form-control" id="username" name="username" placeholder="Tên đăng nhập hoặc Email" required autofocus> 
                     </div>
                 </div>
                 <!-- Input Group for Password -->
                 <div class="mb-3">
                     <label for="password" class="form-label visually-hidden">Mật khẩu</label>
                     <div class="input-group input-group-lg password-input-group">
                         <span class="input-group-text"><i class="fas fa-lock fa-fw"></i></span> 
                         <input type="password" class="form-control" id="password" name="password" placeholder="Mật khẩu" required> 
                         <button class="btn btn-outline-secondary password-toggle-btn" type="button" onclick="togglePasswordVisibility('password', this)" aria-label="Hiện/Ẩn mật khẩu"> 
                             <i class="fas fa-eye"></i> 
                         </button> 
                     </div>
                 </div>
                 <!-- Forgot Password -->
                 <div class="d-flex justify-content-between align-items-center mb-4">
                      <div class="form-check">
                          </div>
                      <div>
                          <a href="?page=forgot_password" class="text-decoration-none text-muted small">Quên mật khẩu?</a>   
                      </div>
                 </div>
                <button class="w-100 btn btn-primary btn-lg" type="submit">
                    <i class="fas fa-sign-in-alt me-2"></i>Đăng Nhập
                </button>
            </form>
            <div class="text-center mt-4 pt-3 border-top">
                <small class="text-muted">Chưa có tài khoản? <a href="?page=register" class="text-decoration-none fw-medium">Đăng ký ngay</a></small>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="/webfinal/public/js/login.js"></script>
</body>
</html>