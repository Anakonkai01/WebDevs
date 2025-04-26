<?php
// Web/app/Views/contact.php
$pageTitle = $pageTitle ?? 'Liên hệ';
// Header bao gồm Bootstrap CSS/JS và các biến toàn cục
include_once __DIR__ . '/../layout/header.php';
// Nhúng file CSS riêng cho trang contact (nếu cần tùy chỉnh thêm)
echo '<link rel="stylesheet" href="/webfinal/public/css/contact.css">';
?>

<section class="contact-page-section py-5">
    <div class="container">
        <?php // ----- Phần Tiêu đề ----- ?>
        <div class="text-center mb-5">
            <i class="fas fa-headset fa-3x text-primary mb-3"></i>
            <h1 class="display-5 fw-bold">Liên Hệ Chúng Tôi</h1>
            <p class="lead text-muted">Chúng tôi luôn sẵn lòng lắng nghe và hỗ trợ bạn!</p>
        </div>

        <?php // ----- Phần Nội dung chính (Thông tin & Bản đồ) ----- ?>
        <div class="row g-4 g-lg-5">

            <?php // --- Cột Thông tin liên hệ --- ?>
            <div class="col-lg-5">
                <div class="card shadow-sm h-100 contact-info-card">
                    <div class="card-header bg-primary text-white"> <?php // Header nổi bật hơn ?>
                        <h2 class="h5 mb-0 py-1"><i class="fas fa-info-circle me-2"></i>Thông Tin Liên Hệ</h2>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled contact-details-list">
                            <li class="d-flex align-items-start mb-3 pb-3 border-bottom">
                                <i class="fas fa-map-marker-alt fa-fw me-3 text-primary mt-1 fs-5"></i>
                                <div>
                                    <strong class="d-block mb-1">Địa chỉ:</strong>
                                    <?php // Đảm bảo địa chỉ này chính xác ?>
                                    <span class="text-muted">19 Đ. Nguyễn Hữu Thọ, Tân Hưng, Quận 7, Thành phố Hồ Chí Minh</span>
                                </div>
                            </li>
                            <li class="d-flex align-items-start mb-3 pb-3 border-bottom">
                                <i class="fas fa-phone-alt fa-fw me-3 text-primary mt-1 fs-5"></i>
                                <div>
                                    <strong class="d-block mb-1">Điện thoại:</strong>
                                    <a href="tel:0987654321" class="text-decoration-none text-dark d-block">0987 654 321</a>
                                    <small class="text-muted">(Hỗ trợ 24/7)</small>
                                </div>
                            </li>
                            <li class="d-flex align-items-start mb-3 pb-3 border-bottom">
                                <i class="fas fa-envelope fa-fw me-3 text-primary mt-1 fs-5"></i>
                                <div>
                                    <strong class="d-block mb-1">Email:</strong>
                                    <a href="mailto:contact@myshop.com" class="text-decoration-none text-dark">contact@myshop.com</a>
                                </div>
                            </li>
                            <li class="d-flex align-items-start">
                                <i class="fas fa-clock fa-fw me-3 text-primary mt-1 fs-5"></i>
                                <div>
                                    <strong class="d-block mb-1">Giờ làm việc:</strong>
                                    <span class="text-muted d-block">Thứ 2 - Thứ 7: 08:00 - 18:00</span>
                                    <span class="text-muted d-block">Chủ Nhật: Nghỉ</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                     <div class="card-footer bg-light text-center"> <?php // Thêm footer với social links ?>
                        <strong class="small d-block mb-2">Kết nối với chúng tôi:</strong>
                        <a href="#" class="text-secondary me-3 fs-5"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-secondary me-3 fs-5"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-secondary fs-5"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>

            <?php // --- Cột Bản đồ --- ?>
            <div class="col-lg-7">
                <div class="card shadow-sm h-100 map-card">
                    <div class="card-header bg-light">
                        <h2 class="h5 mb-0 py-1"><i class="fas fa-map-marked-alt text-primary me-2"></i>Vị Trí Trên Bản Đồ</h2>
                    </div>
                    <div class="card-body p-0 map-container"> <?php // p-0 để iframe chiếm toàn bộ card-body ?>
                        <?php // --- Đảm bảo iframe này là mã nhúng MỚI NHẤT từ Google Maps --- ?>
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3920.060863560093!2d106.69718137488021!3d10.729591089417238!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752fbea5fe3db1%3A0xFA121984240b5141!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBUw7RuIMSQ4bupYyBUaOG6r25n!5e0!3m2!1svi!2s!4v1714121350591!5m2!1svi!2s"
                            width="100%"
                            height="100%"
                            style="border:0; min-height: 450px;" <?php // Thêm min-height ?>
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                        <?php // --- KẾT THÚC IFRAME --- ?>
                    </div>
                </div>
            </div>

        </div> <?php // Kết thúc .row ?>

    </div> <?php // Kết thúc .container ?>
</section>

<?php
// Footer bao gồm các file JS cần thiết
include_once __DIR__ . '/../layout/footer.php';
?>