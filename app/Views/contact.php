<?php
// Web/app/Views/contact.php
$pageTitle = $pageTitle ?? 'Liên hệ';
include_once __DIR__ . '/../layout/header.php'; // Includes Bootstrap
?>
    <style>
        .map-container iframe {
            border: 0;
            border-radius: 0.375rem; /* Match Bootstrap's rounded */
            width: 100%; /* Đảm bảo iframe chiếm đủ chiều rộng */
            height: 400px; /* Đặt chiều cao cố định hoặc theo tỷ lệ */
        }
        .contact-info-item i {
            width: 25px; /* Tăng nhẹ chiều rộng icon */
            text-align: center;
            font-size: 1.1rem; /* Tăng nhẹ kích thước icon */
            margin-top: 0.1rem; /* Căn chỉnh icon với text */
        }
        .contact-info-item div {
            line-height: 1.6;
        }
        .contact-form-card {
            border-top: 3px solid var(--bs-primary); /* Thêm viền màu nhấn nhá */
        }
    </style>

    <section class="contact-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                <h1 class="display-5">Liên hệ với Chúng tôi</h1>
                <p class="lead text-muted">Chúng tôi luôn sẵn lòng lắng nghe và hỗ trợ bạn!</p>
            </div>

            <div class="row g-4 g-lg-5 mb-5">

                <?php // --- Cột Thông tin liên hệ (Tăng độ rộng) --- ?>
                <div class="col-lg-5 col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-light">
                            <h2 class="h5 mb-0 py-1"><i class="fas fa-info-circle text-primary me-2"></i>Thông tin liên hệ</h2>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <ul class="list-unstyled mb-0">
                                <li class="d-flex mb-3 contact-info-item">
                                    <i class="fas fa-map-marker-alt me-3 text-primary"></i>
                                    <div>
                                        <strong class="d-block">Địa chỉ:</strong>
                                        <?php // Cập nhật địa chỉ nếu cần ?>
                                        19 Đ. Nguyễn Hữu Thọ, Tân Hưng, Quận 7, Thành phố Hồ Chí Minh
                                    </div>
                                </li>
                                <li class="d-flex mb-3 contact-info-item">
                                    <i class="fas fa-phone-alt me-3 text-primary"></i>
                                    <div>
                                        <strong class="d-block">Điện thoại:</strong>
                                        <a href="tel:0987654321" class="text-decoration-none text-dark">0987 654 321</a> (Hỗ trợ 24/7)
                                    </div>
                                </li>
                                <li class="d-flex mb-3 contact-info-item">
                                    <i class="fas fa-envelope me-3 text-primary"></i>
                                    <div>
                                        <strong class="d-block">Email:</strong>
                                        <a href="mailto:contact@myshop.com" class="text-decoration-none text-dark">contact@myshop.com</a>
                                    </div>
                                </li>
                                <li class="d-flex contact-info-item">
                                    <i class="fas fa-clock me-3 text-primary"></i>
                                    <div>
                                        <strong class="d-block">Giờ làm việc:</strong>
                                        Thứ 2 - Thứ 7: 08:00 - 18:00<br>Chủ Nhật: Nghỉ
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <?php // --- Cột Bản đồ (Giảm độ rộng) --- ?>
                <div class="col-lg-7 col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-light">
                            <h2 class="h5 mb-0 py-1"><i class="fas fa-map-marked-alt text-primary me-2"></i>Vị trí trên bản đồ</h2>
                        </div>
                        <div class="card-body p-0">
                            <div class="map-container">
                                <?php // --- DÁN IFRAME MỚI CỦA BẠN VÀO ĐÂY --- ?>
                                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3920.0250880197937!2d106.6967698251674!3d10.732548339413558!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317528b2747a81a3%3A0x33c1813055acb613!2zxJDhuqFpIGjhu41jIFTDtG4gxJDhu6ljIFRo4bqvbmc!5e0!3m2!1svi!2s!4v1745562027491!5m2!1svi!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                                <?php // --- KẾT THÚC IFRAME --- ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php // --- Form Liên hệ (ĐÃ BỎ COMMENT) --- ?>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-sm contact-form-card">
                        <div class="card-body p-4 p-lg-5">
                            <h2 class="h4 text-center mb-4">Gửi tin nhắn cho chúng tôi</h2>
                            <?php // **LƯU Ý**: action của form cần trỏ đến route xử lý bạn sẽ tạo ?>
                            <form action="?page=handle_contact" method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="contact_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" id="contact_email" class="form-control" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="contact_subject" class="form-label">Chủ đề</label>
                                    <input type="text" name="subject" id="contact_subject" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="contact_message" class="form-label">Nội dung tin nhắn <span class="text-danger">*</span></label>
                                    <textarea name="message" id="contact_message" rows="5" class="form-control" required></textarea>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-paper-plane me-2"></i>Gửi tin nhắn</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php // --- KẾT THÚC FORM --- ?>

        </div>
    </section>

<?php
include_once __DIR__ . '/../layout/footer.php';
?>