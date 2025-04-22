<?php
// Web/app/Views/contact.php
$pageTitle = $pageTitle ?? 'Liên hệ'; // Lấy pageTitle hoặc đặt mặc định

// Include layout header
include_once __DIR__ . '/../layout/header.php';
?>

    <style>
        /* CSS riêng cho trang contact */
        .contact-section { padding: 30px 0; }
        .contact-header { text-align: center; margin-bottom: 40px; }
        .contact-header i { font-size: 2em; color: #007bff; margin-bottom: 10px; }
        .contact-content { display: flex; flex-wrap: wrap; gap: 40px; justify-content: center; }
        .contact-info, .contact-map { flex: 1 1 450px; /* Cho phép co giãn nhưng có kích thước cơ bản */ min-width: 300px; }
        .contact-info h2, .contact-map h2 { margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; font-size: 1.5em; color: #343a40;}
        .contact-info p { margin-bottom: 15px; font-size: 1.05em; line-height: 1.7; display: flex; align-items: flex-start; }
        .contact-info i.fas { margin-right: 12px; color: #007bff; width: 20px; text-align: center; margin-top: 4px;} /* FontAwesome icon */
        .contact-info a { color: #0056b3; }
        .map-container {
            position: relative;
            padding-bottom: 65%; /* Điều chỉnh tỷ lệ nếu cần */
            height: 0;
            overflow: hidden;
            max-width: 100%;
            background: #e9ecef;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .map-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
        /* CSS cho Contact Form (nếu thêm sau) */
        .contact-form { margin-top: 40px; background-color: #f8f9fa; padding: 30px; border-radius: 5px; border: 1px solid #eee;}
        .contact-form h2 { margin-top: 0; }
        .contact-form .form-group { margin-bottom: 20px; }
        .contact-form label { display: block; margin-bottom: 6px; font-weight: bold; color: #555; }
        .contact-form input[type="text"],
        .contact-form input[type="email"],
        .contact-form textarea {
            width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 1em;
        }
        .contact-form textarea { min-height: 120px; resize: vertical; }
        .contact-form button { background-color: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 4px; font-size: 1.05em; cursor: pointer;}
        .contact-form button:hover { background-color: #0056b3; }
    </style>

    <section class="contact-section">
        <div class="contact-header">
            <i class="fas fa-headset"></i> <?php // Icon FontAwesome ?>
            <h1>Liên hệ với Chúng tôi</h1>
            <p>Chúng tôi luôn sẵn lòng lắng nghe và hỗ trợ bạn!</p>
        </div>

        <div class="contact-content">

            <div class="contact-info" style="flex: 1 1 400px;">
                <h2>Thông tin liên hệ</h2>
                <p><i class="fas fa-map-marker-alt"></i><strong>Địa chỉ:</strong> 123 Đường ABC, Phường XYZ, Quận Bình Thạnh, TP. Hồ Chí Minh, Việt Nam</p> <?php // Các <br> trong địa chỉ nhiều dòng vẫn giữ lại nếu cần ?>
                <p><i class="fas fa-phone-alt"></i><strong>Điện thoại:</strong> <a href="tel:0987654321">0987 654 321</a> (Hỗ trợ 24/7)</p>
                <p><i class="fas fa-envelope"></i><strong>Email:</strong> <a href="mailto:contact@myshop.com">contact@myshop.com</a></p>
                <p><i class="fas fa-clock"></i><strong>Giờ làm việc:</strong> Thứ 2 - Thứ 7: 08:00 - 18:00<br>Chủ Nhật: Nghỉ</p> <?php // Giữ lại <br> nếu muốn Chủ Nhật xuống dòng riêng ?>
            </div>

            <div class="contact-map">
                <h2>Vị trí trên bản đồ</h2>
                <div class="map-container">
                    <?php // Thay src bằng mã nhúng Google Map của địa chỉ cửa hàng bạn ?>
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15677.18007699556!2d106.6990116786119!3d10.790357730878312!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317528d4a645895b%3A0x2c7280ffb3dc75f1!2zQuG6o28gdMOgbmcgQ2hp4bq_biBkeG7i2NoIFZp4buHdCBOYW0!5e0!3m2!1svi!2s!4v1713779634596!5m2!1svi!2s"
                        width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

        </div>

        <?php // --- Khu vực Form liên hệ (Tùy chọn - Có thể thêm sau) --- ?>
        <?php /*
     <div class="contact-form">
         <h2>Gửi tin nhắn cho chúng tôi</h2>
         <form action="?page=handle_contact" method="POST">
             <div class="form-group">
                 <label for="contact_name">Họ và tên:</label>
                 <input type="text" name="name" id="contact_name" required>
             </div>
             <div class="form-group">
                  <label for="contact_email">Email:</label>
                  <input type="email" name="email" id="contact_email" required>
              </div>
             <div class="form-group">
                  <label for="contact_subject">Chủ đề:</label>
                  <input type="text" name="subject" id="contact_subject">
              </div>
             <div class="form-group">
                 <label for="contact_message">Nội dung tin nhắn:</label>
                 <textarea name="message" id="contact_message" rows="6" required></textarea>
             </div>
             <button type="submit">Gửi tin nhắn</button>
         </form>
     </div>
     */ ?>
        <?php // --- Kết thúc Form liên hệ --- ?>

    </section>

<?php
// Include layout footer
include_once __DIR__ . '/../layout/footer.php';
?>