<?php
// Web/app/Views/contact.php
$pageTitle = $pageTitle ?? 'Liên hệ';
include_once __DIR__ . '/../layout/header.php'; // Includes Bootstrap
?>
    <style>
        .map-container iframe { border:0; border-radius: 0.375rem; /* Match Bootstrap's rounded */}
        .contact-info-item i { width: 20px; text-align: center; }
    </style>

    <section class="contact-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                <h1 class="display-5">Liên hệ với Chúng tôi</h1>
                <p class="lead text-muted">Chúng tôi luôn sẵn lòng lắng nghe và hỗ trợ bạn!</p>
            </div>

            <div class="row g-4 g-lg-5 justify-content-center">

                <div class="col-lg-6 col-md-7">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h2 class="h4 mb-0">Thông tin liên hệ</h2></div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li class="d-flex mb-3 contact-info-item">
                                    <i class="fas fa-map-marker-alt mt-1 me-3 text-primary"></i>
                                    <div><strong>Địa chỉ:</strong><br>123 Đường ABC, Phường XYZ, Quận Bình Thạnh, TP. Hồ Chí Minh</div>
                                </li>
                                <li class="d-flex mb-3 contact-info-item">
                                    <i class="fas fa-phone-alt mt-1 me-3 text-primary"></i>
                                    <div><strong>Điện thoại:</strong><br><a href="tel:0987654321" class="text-decoration-none">0987 654 321</a> (Hỗ trợ 24/7)</div>
                                </li>
                                <li class="d-flex mb-3 contact-info-item">
                                    <i class="fas fa-envelope mt-1 me-3 text-primary"></i>
                                    <div><strong>Email:</strong><br><a href="mailto:contact@myshop.com" class="text-decoration-none">contact@myshop.com</a></div>
                                </li>
                                <li class="d-flex mb-0 contact-info-item">
                                    <i class="fas fa-clock mt-1 me-3 text-primary"></i>
                                    <div><strong>Giờ làm việc:</strong><br>Thứ 2 - Thứ 7: 08:00 - 18:00<br>Chủ Nhật: Nghỉ</div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-md-5">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h2 class="h4 mb-0">Vị trí trên bản đồ</h2></div>
                        <div class="card-body p-0">
                            <div class="map-container" style="padding-bottom: 100%; height: 100%; min-height: 300px;"> <?php // Adjust ratio/height ?>
                                <iframe
                                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15677.18007699556!2d106.6990116786119!3d10.790357730878312!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317528d4a645895b%3A0x2c7280ffb3dc75f1!2zQuG6o28gdMOgbmcgQ2hp4bq_biBkeG7i2NoIFZp4buHdCBOYW0!5e0!3m2!1svi!2s!4v1713779634596!5m2!1svi!2s" <?php // Replace with your map embed URL ?>
                                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php // --- Contact Form (Optional - Uncomment and style if needed) --- ?>
            <?php /*
         <div class="row justify-content-center mt-5">
             <div class="col-lg-8">
                 <div class="card shadow-sm contact-form">
                     <div class="card-body p-4">
                        <h2 class="h4 text-center mb-4">Gửi tin nhắn cho chúng tôi</h2>
                        <form action="?page=handle_contact" method="POST">
                            <div class="mb-3">
                                <label for="contact_name" class="form-label">Họ và tên:</label>
                                <input type="text" name="name" id="contact_name" class="form-control" required>
                            </div>
                             <div class="mb-3">
                                 <label for="contact_email" class="form-label">Email:</label>
                                 <input type="email" name="email" id="contact_email" class="form-control" required>
                             </div>
                            <div class="mb-3">
                                <label for="contact_subject" class="form-label">Chủ đề:</label>
                                <input type="text" name="subject" id="contact_subject" class="form-control">
                            </div>
                             <div class="mb-3">
                                <label for="contact_message" class="form-label">Nội dung tin nhắn:</label>
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
         */ ?>

        </div>
    </section>

<?php
include_once __DIR__ . '/../layout/footer.php';
?>