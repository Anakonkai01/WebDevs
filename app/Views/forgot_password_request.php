<?php
// Web/app/Views/forgot_password_request.php
$pageTitle = $pageTitle ?? 'Quên Mật Khẩu';
include_once __DIR__ . '/../layout/header.php'; // Hoặc dùng cấu trúc trang riêng
?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <h1 class="h3 mb-4 text-center">Quên Mật Khẩu</h1>
                        <p class="text-center text-muted mb-4">Nhập địa chỉ email của bạn. Nếu tài khoản tồn tại và đã xác thực, chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu.</p>

                        <?php // Flash message đã xử lý trong header ?>

                        <form action="?page=handle_forgot_password" method="POST">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                                <label for="email">Địa chỉ Email</label>
                            </div>
                            <button class="w-100 btn btn-lg btn-primary" type="submit">Gửi yêu cầu</button>
                        </form>

                        <div class="text-center mt-4">
                            <small><a href="?page=login" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Quay lại Đăng nhập</a></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include_once __DIR__ . '/../layout/footer.php'; // Hoặc dùng cấu trúc trang riêng ?>