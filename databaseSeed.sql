-- Tắt kiểm tra FK
SET FOREIGN_KEY_CHECKS = 0;

-- Xóa bảng cũ nếu tồn tại
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS wishlist;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

-- Tạo bảng users với các cột mới
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_email_verified TINYINT(1) DEFAULT 0,                -- Cột mới: Trạng thái xác thực email
    email_verification_code VARCHAR(255) NULL DEFAULT NULL, -- Cột mới: Mã xác thực
    email_verification_expires_at TIMESTAMP NULL DEFAULT NULL, -- Cột mới: Thời gian hết hạn mã
    password_reset_token VARCHAR(255) NULL DEFAULT NULL,     -- Cột mới: Token reset mật khẩu
    password_reset_expires_at TIMESTAMP NULL DEFAULT NULL,   -- Cột mới: Thời gian hết hạn token
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_reset_token (password_reset_token)     -- Tùy chọn: Đảm bảo token là duy nhất
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng products
CREATE TABLE products (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          name VARCHAR(255) NOT NULL,
                          description TEXT,
                          price DOUBLE NOT NULL,
                          image VARCHAR(255),
                          stock INT DEFAULT 0,
                          brand VARCHAR(100),
                          rating DOUBLE DEFAULT 0,
                          screen_size VARCHAR(100) NULL DEFAULT NULL,
                          screen_tech VARCHAR(100) NULL DEFAULT NULL,
                          cpu VARCHAR(100) NULL DEFAULT NULL,
                          ram VARCHAR(50) NULL DEFAULT NULL,
                          storage VARCHAR(100) NULL DEFAULT NULL,
                          rear_camera VARCHAR(255) NULL DEFAULT NULL,
                          front_camera VARCHAR(150) NULL DEFAULT NULL,
                          battery_capacity VARCHAR(100) NULL DEFAULT NULL,
                          os VARCHAR(100) NULL DEFAULT NULL,
                          dimensions VARCHAR(100) NULL DEFAULT NULL,
                          weight VARCHAR(50) NULL DEFAULT NULL,
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng orders
CREATE TABLE orders (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        customer_name VARCHAR(255) NOT NULL,
                        customer_address TEXT NOT NULL,
                        customer_phone VARCHAR(20) NOT NULL,
                        customer_email VARCHAR(255) DEFAULT NULL,
                        notes TEXT DEFAULT NULL,
                        total DOUBLE NOT NULL,
                        status VARCHAR(50) NOT NULL DEFAULT 'Pending',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng order_items
CREATE TABLE order_items (
                             id INT AUTO_INCREMENT PRIMARY KEY,
                             order_id INT NOT NULL,
                             product_id INT NULL,
                             quantity INT NOT NULL,
                             price DOUBLE NOT NULL,
                             FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                             FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng reviews
CREATE TABLE reviews (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         product_id INT NOT NULL,
                         user_id INT NULL,
                         reviewer_name VARCHAR(100) NULL DEFAULT 'Ẩn danh', -- Cột này có thể không cần nếu luôn lấy từ user_id
                         content TEXT,
                         rating TINYINT NULL DEFAULT NULL CHECK (rating >= 1 AND rating <= 5),
                         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                         FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                         FOREIGN KEY (user_id) REFERENCES users(id)
                             ON DELETE SET NULL -- Nếu user bị xóa, review vẫn còn nhưng user_id là NULL
                             ON UPDATE CASCADE -- Nếu user_id thay đổi (ít xảy ra), cập nhật theo
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng wishlist
CREATE TABLE wishlist (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          user_id INT NOT NULL,
                          product_id INT NOT NULL,
                          added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                          FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                          UNIQUE KEY unique_user_product (user_id, product_id) -- Đảm bảo mỗi user chỉ yêu thích 1 sp 1 lần
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bật lại kiểm tra FK
SET FOREIGN_KEY_CHECKS = 1;

/* ───────────────────────────────────────────
   1. USERS - Mật khẩu mẫu, cần được hash đúng cách khi tạo user thực tế
   ─────────────────────────────────────────── */
-- Thay thế mật khẩu bằng hash được tạo từ password_hash() trong PHP
-- Ví dụ: password_hash('password123', PASSWORD_DEFAULT)
INSERT INTO users (username, email, password, is_email_verified, email_verification_code, email_verification_expires_at) VALUES
('user01', 'user01@example.com', '$2y$10$abcdefghijklmnopqrstuv01', 1, NULL, NULL),
('user02', 'user02@example.com', '$2y$10$abcdefghijklmnopqrstuv02', 1, NULL, NULL),
('user03', 'user03@example.com', '$2y$10$abcdefghijklmnopqrstuv03', 0, 'code03', '2023-12-31 23:59:59'),
('user04', 'user04@example.com', '$2y$10$abcdefghijklmnopqrstuv04', 0, 'code04', '2023-12-31 23:59:59'),
('user05', 'user05@example.com', '$2y$10$abcdefghijklmnopqrstuv05', 1, NULL, NULL),
('user06', 'user06@example.com', '$2y$10$abcdefghijklmnopqrstuv06', 0, 'code06', '2023-12-31 23:59:59'),
('user07', 'user07@example.com', '$2y$10$abcdefghijklmnopqrstuv07', 1, NULL, NULL),
('user08', 'user08@example.com', '$2y$10$abcdefghijklmnopqrstuv08', 0, 'code08', '2023-12-31 23:59:59'),
('user09', 'user09@example.com', '$2y$10$abcdefghijklmnopqrstuv09', 1, NULL, NULL),
('user10', 'user10@example.com', '$2y$10$abcdefghijklmnopqrstuv10', 0, 'code10', '2023-12-31 23:59:59'),
('user11', 'user11@example.com', '$2y$10$abcdefghijklmnopqrstuv11', 1, NULL, NULL),
('user12', 'user12@example.com', '$2y$10$abcdefghijklmnopqrstuv12', 0, 'code12', '2023-12-31 23:59:59'),
('user13', 'user13@example.com', '$2y$10$abcdefghijklmnopqrstuv13', 1, NULL, NULL),
('user14', 'user14@example.com', '$2y$10$abcdefghijklmnopqrstuv14', 0, 'code14', '2023-12-31 23:59:59'),
('user15', 'user15@example.com', '$2y$10$abcdefghijklmnopqrstuv15', 1, NULL, NULL),
('user16', 'user16@example.com', '$2y$10$abcdefghijklmnopqrstuv16', 0, 'code16', '2023-12-31 23:59:59'),
('user17', 'user17@example.com', '$2y$10$abcdefghijklmnopqrstuv17', 1, NULL, NULL),
('user18', 'user18@example.com', '$2y$10$abcdefghijklmnopqrstuv18', 0, 'code18', '2023-12-31 23:59:59'),
('user19', 'user19@example.com', '$2y$10$abcdefghijklmnopqrstuv19', 1, NULL, NULL),
('user20', 'user20@example.com', '$2y$10$abcdefghijklmnopqrstuv20', 0, 'code20', '2023-12-31 23:59:59');


/* ───────────────────────────────────────────
   2. PRODUCTS – Cập nhật thêm dữ liệu thông số
   ─────────────────────────────────────────── */
INSERT INTO products (
    name, description, price, image, stock, brand, rating,
    screen_size, screen_tech, cpu, ram, storage, rear_camera, front_camera, battery_capacity, os
) VALUES
('iPhone 15 Pro 256GB', 'Chip A17 Pro mạnh mẽ, khung Titan siêu bền, camera zoom quang 5x.', 29990000, 'iphone15pro.jpg', 12, 'Apple', 4.8,'6.1 inch', 'Super Retina XDR OLED', 'Apple A17 Pro', '8 GB', '256 GB', 'Chính 48 MP & Phụ 12 MP, 12 MP', '12 MP', '3274 mAh', 'iOS 17'),
('iPhone 15 128GB', 'Dynamic Island, camera 48MP, sạc USB-C tiện lợi.', 24990000, 'iphone15.jpg', 15, 'Apple', 4.6,'6.1 inch', 'Super Retina XDR OLED', 'Apple A16 Bionic', '6 GB', '128 GB', 'Chính 48 MP & Phụ 12 MP', '12 MP', '3349 mAh', 'iOS 17'),
('Galaxy S25 Ultra 512GB', 'Camera 200MP siêu zoom, S Pen tích hợp, màn hình sáng nhất.', 26990000, 's25ultra.jpg', 10, 'Samsung', 4.7,'6.8 inch', 'Dynamic AMOLED 2X', 'Snapdragon 8 Gen 4 for Galaxy', '12 GB', '512 GB', 'Chính 200 MP & Phụ 12 MP, 10 MP, 10 MP', '12 MP', '5000 mAh', 'Android 15'),
('Galaxy Z Flip 6', 'Màn hình gập độc đáo, nhỏ gọn, camera FlexCam linh hoạt.', 25990000, 'zflip6.jpg', 8, 'Samsung', 4.5,'Chính 6.7" & Phụ 3.4"', 'Dynamic AMOLED 2X', 'Snapdragon 8 Gen 3 for Galaxy', '8 GB', '256 GB', 'Chính 12 MP & Phụ 12 MP', '10 MP', '3700 mAh', 'Android 14'),
('Oppo Find X9 Pro', 'Sạc nhanh SuperVOOC 150W, camera Hasselblad ấn tượng.', 18990000, 'findx9.jpg', 20, 'Oppo', 4.4,'6.7 inch', 'AMOLED', 'Dimensity 9400', '12 GB', '256 GB', 'Chính 50 MP & Phụ 50 MP, 13 MP', '32 MP', '5000 mAh', 'Android 15'),
('Oppo Reno 12', 'Thiết kế thời trang, camera selfie 32MP, sạc nhanh.', 8990000, 'reno12.jpg', 25, 'Oppo', 4.2,'6.7 inch', 'AMOLED', 'Dimensity 7300', '8 GB', '256 GB', 'Chính 50 MP & Phụ 8 MP, 2 MP', '32 MP', '5000 mAh', 'Android 14'),
('Xiaomi 14 Ultra', 'Hợp tác Leica, cảm biến 1 inch, hiệu năng Snapdragon 8 Gen 4.', 19990000, 'mi14u.jpg', 18, 'Xiaomi', 4.6,'6.73 inch', 'LTPO AMOLED', 'Snapdragon 8 Gen 4', '16 GB', '512 GB', 'Chính 50 MP & Phụ 50 MP, 50 MP, 50 MP', '32 MP', '5300 mAh', 'Android 15'),
('Xiaomi 14', 'Nhỏ gọn mạnh mẽ, màn hình LTPO OLED, sạc nhanh 90W.', 13990000, 'mi14.jpg', 22, 'Xiaomi', 4.4,'6.36 inch', 'LTPO OLED', 'Snapdragon 8 Gen 3', '12 GB', '256 GB', 'Chính 50 MP & Phụ 50 MP, 50 MP', '32 MP', '4610 mAh', 'Android 14'),
('Realme GT Neo6', 'Màn hình 144Hz siêu mượt, chip Snapdragon 8s Gen 3, pin khủng 5500mAh.', 11990000, 'gtneo6.jpg', 30, 'Realme', 4.3,'6.78 inch', 'AMOLED', 'Snapdragon 8s Gen 3', '12 GB', '256 GB', 'Chính 50 MP & Phụ 8 MP', '32 MP', '5500 mAh', 'Android 14'),
('Realme C67', 'Camera 108MP, pin trâu 5000mAh, giá tốt.', 3790000, 'c67.jpg', 40, 'Realme', 4.0,'6.72 inch', 'IPS LCD', 'Snapdragon 685', '8 GB', '128 GB', 'Chính 108 MP & Phụ 2 MP', '8 MP', '5000 mAh', 'Android 14'),
('Vivo V31 Pro', 'Chuyên gia chụp đêm, thiết kế Aura Light độc đáo.', 10990000, 'v31pro.jpg', 17, 'Vivo', 4.2,'6.78 inch', 'AMOLED', 'Dimensity 8200', '12 GB', '256 GB', 'Chính 50 MP & Phụ 12 MP, 8 MP', '50 MP', '4600 mAh', 'Android 14'),
('Vivo Y100', 'Thiết kế mỏng nhẹ thời trang, màn hình AMOLED.', 6490000, 'y100.jpg', 35, 'Vivo', 4.1,'6.67 inch', 'AMOLED', 'Snapdragon 695 5G', '8 GB', '128 GB', 'Chính 64 MP & Phụ 2 MP, 2 MP', '16 MP', '5000 mAh', 'Android 14'),
('OnePlus 13 Pro', 'Màn hình Fluid AMOLED đỉnh cao, sạc siêu nhanh 240W.', 17990000, 'op13pro.jpg', 14, 'OnePlus', 4.5,'6.7 inch', 'Fluid AMOLED', 'Snapdragon 8 Gen 4', '16 GB', '512 GB', 'Chính 50 MP & Phụ 48 MP, 64 MP', '32 MP', '5000 mAh', 'Android 15'),
('OnePlus Nord 4', 'Hiệu năng tốt với Dimensity 1200-AI, màn AMOLED 120Hz.', 7490000, 'nord4.jpg', 28, 'OnePlus', 4.1,'6.74 inch', 'AMOLED', 'Dimensity 1200-AI', '8 GB', '128 GB', 'Chính 50 MP & Phụ 8 MP, 2 MP', '16 MP', '5000 mAh', 'Android 14'),
('Google Pixel 9 Pro', 'Nhiếp ảnh điện toán AI, chip Tensor G4, trải nghiệm Android gốc.', 21990000, 'pixel9pro.jpg', 11, 'Google', 4.7,'6.7 inch', 'LTPO OLED', 'Google Tensor G4', '12 GB', '256 GB', 'Chính 50 MP & Phụ 48 MP, 48 MP', '10.5 MP', '5050 mAh', 'Android 15'),
('Google Pixel 9a', 'Camera AI thông minh, giá hợp lý, cập nhật Android lâu dài.', 9990000, 'pixel9a.jpg', 24, 'Google', 4.3,'6.1 inch', 'OLED', 'Google Tensor G3', '8 GB', '128 GB', 'Chính 64 MP & Phụ 13 MP', '13 MP', '4385 mAh', 'Android 14'),
('Motorola Edge 50', 'Màn hình pOLED cong tràn viền, Android gốc mượt mà.', 8990000, 'edge50.jpg', 26, 'Motorola', 4.0,'6.6 inch', 'pOLED', 'Snapdragon 7 Gen 3', '8 GB', '256 GB', 'Chính 50 MP & Phụ 13 MP', '32 MP', '4500 mAh', 'Android 14'),
('Asus ROG Phone 8', 'Gaming phone đỉnh cao, tản nhiệt hiệu quả, màn 165Hz, AirTrigger.', 24990000, 'rog8.jpg', 9, 'Asus', 4.8,'6.78 inch', 'AMOLED', 'Snapdragon 8 Gen 3', '16 GB', '512 GB', 'Chính 50 MP & Phụ 13 MP, 32 MP', '32 MP', '6000 mAh', 'Android 14'),
('Sony Xperia 1 VI', 'Màn hình 4K HDR OLED tỉ lệ 21:9, camera chuyên nghiệp.', 27990000, 'xperia1vi.jpg', 7, 'Sony', 4.5,'6.5 inch', 'OLED', 'Snapdragon 8 Gen 3', '12 GB', '256 GB', 'Chính 48 MP & Phụ 12 MP, 12 MP', '12 MP', '5000 mAh', 'Android 14'),
('Sony Xperia 10 VI', 'Thiết kế nhỏ gọn, âm thanh Hi-Res Audio, pin trâu.', 8990000, 'xperia10vi.jpg', 19, 'Sony', 4.2,'6.1 inch', 'OLED', 'Snapdragon 6 Gen 1', '6 GB', '128 GB', 'Chính 48 MP & Phụ 8 MP', '8 MP', '5000 mAh', 'Android 14');


/* ───────────────────────────────────────────
   3. ORDERS – Thêm thông tin customer
   ─────────────────────────────────────────── */
INSERT INTO orders (user_id, customer_name, customer_address, customer_phone, total, status) VALUES
(1, 'Nguyễn Văn A', '123 Đường ABC, Q.1, TP.HCM', '0901112233', 29990000, 'Delivered'),
(2, 'Trần Thị B', '456 Đường XYZ, Q.3, TP.HCM', '0904445566', 25990000, 'Shipped'),
(3, 'Lê Văn C', '789 Đường KLM, Q. Bình Thạnh, TP.HCM', '0907778899', 7490000, 'Processing'),
(4, 'Phạm Thị D', '111 Đường DEF, Q.10, TP.HCM', '0909991122', 17990000, 'Pending'),
(5, 'Hoàng Văn E', '222 Đường GHI, Q.7, TP.HCM', '0908887766', 8990000, 'Delivered'),
(6, 'Vũ Thị F', '333 Đường UVW, Q.5, TP.HCM', '0906665544', 26990000, 'Cancelled'),
(7, 'Đặng Văn G', '555 Đường PQR, Q. Tân Bình, TP.HCM', '0905554433', 13990000, 'Delivered'),
(8, 'Bùi Thị H', '666 Đường STU, Q. Phú Nhuận, TP.HCM', '0904443322', 21990000, 'Shipped'),
(9, 'Hồ Văn I', '777 Đường JKL, Q.2, TP.HCM', '0903332211', 9990000, 'Pending'),
(10, 'Ngô Thị K', '888 Đường MNO, Q. Gò Vấp, TP.HCM', '0902221100', 3790000, 'Delivered'),
(11, 'Dương Văn L', '999 Đường QRS, Q.12, TP.HCM', '0901110099', 24990000, 'Processing'),
(12, 'Mai Thị M', '101 Đường TUV, TP. Thủ Đức', '0909998877', 18990000, 'Delivered'),
(13, 'Trịnh Văn N', '202 Đường WXY, H. Bình Chánh', '0908889900', 11990000, 'Shipped'),
(14, 'Đinh Thị P', '303 Đường ZAB, H. Nhà Bè', '0907776655', 19990000, 'Pending'),
(15, 'Lý Văn Q', '404 Đường CDE, Q.4, TP.HCM', '0906667788', 10990000, 'Delivered');


/* ───────────────────────────────────────────
   4. ORDER_ITEMS – Giữ nguyên, tham chiếu tới các order ID ở trên
   ─────────────────────────────────────────── */
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1,  1, 1, 29990000), -- iPhone 15 Pro
(2,  4, 1, 25990000), -- Z Flip 6
(3, 14, 1, 7490000),  -- Nord 4
(4, 13, 1, 17990000), -- OP 13 Pro
(5,  6, 1, 8990000),  -- Reno 12
(6,  3, 1, 26990000), -- S25 Ultra
(7,  8, 1, 13990000), -- Mi 14
(8, 15, 1, 21990000), -- Pixel 9 Pro
(9, 16, 1, 9990000),  -- Pixel 9a
(10,10, 1, 3790000),  -- C67
(11, 2, 1, 24990000), -- iPhone 15
(12, 5, 1, 18990000), -- Find X9 Pro
(13, 9, 1, 11990000), -- GT Neo6
(14, 7, 1, 19990000), -- Mi 14 Ultra
(15,11, 1, 10990000); -- V31 Pro


/* ───────────────────────────────────────────
   5. REVIEWS – Thêm user_id và rating, bỏ reviewer_name
   ─────────────────────────────────────────── */
-- reviewer_name sẽ được lấy bằng cách JOIN với bảng users khi hiển thị
INSERT INTO reviews (product_id, user_id, content, rating) VALUES
(1, 2, 'Đỉnh cao công nghệ! Máy mượt, camera chụp ảnh rất đẹp.', 5),
(1, 5, 'Pin tốt hơn đời trước, sạc USB-C tiện lợi nhưng vẫn hơi chậm.', 4),
(3, 6, 'Camera 200 MP chụp zoom siêu xa, ảnh rất chi tiết. Màn hình sáng rực rỡ.', 5),
(4, 3, 'Màn hình gập rất thích, dùng như điện thoại thường khi mở ra, gập lại nhỏ gọn.', 5),
(4, 7, 'Thiết kế màu Tím Titan này sang trọng thực sự. FlexCam chụp ảnh góc khó dễ dàng.', 4),
(4, 8, 'Cơ chế gập có vẻ chắc chắn hơn. Màn hình phụ lớn hiển thị nhiều thông tin.', 4),
(5, 12, 'Giá này mà có Dimensity 9400 là quá ngon. Sạc nhanh 150W đầy pin trong nháy mắt.', 4),
(6, 10, 'Camera selfie của Oppo chưa bao giờ làm mình thất vọng. Chụp ảnh da mịn tự nhiên.', 5),
(7, 14, 'Chất ảnh Leica đậm đà, màu sắc rất nghệ thuật. Chụp thiếu sáng tốt.', 5),
(7, 1, 'Pin trâu hơn iPhone nhiều, dùng cả ngày thoải mái. Hiệu năng thì khỏi bàn.', 4),
(8, 19, 'Nhỏ gọn mà cấu hình khủng. Giá này đúng là flagship killer.', 4),
(9, 13, 'Màn 144 Hz chơi game bắn súng bao phê. Chip 8s Gen 3 cũng rất mạnh.', 5),
(10, 15, 'Camera 108MP trong tầm giá này là điểm cộng lớn. Máy chạy mượt các tác vụ cơ bản.', 4),
(11, 16, 'Chụp đêm ngon, ảnh sáng rõ, ít nhiễu. Camera selfie 50MP chất lượng.', 4),
(12, 18, 'Thiết kế đẹp, mỏng nhẹ. Màn AMOLED hiển thị màu sắc rực rỡ.', 4),
(13, 4, 'Sạc 240W quá bá đạo, cắm tí là đầy. Màn hình Fluid AMOLED vẫn rất đỉnh.', 5),
(14, 9, '5G ổn định, màn hình 120Hz mượt mà. Pin 5000mAh dùng khá lâu.', 4),
(15, 8, 'Magic Eraser xóa vật thể thừa trong ảnh vi diệu thật. Trải nghiệm Android thuần túy.', 5),
(18, 17, 'AirTrigger bấm rất nhạy, hỗ trợ chơi game tốt. Tản nhiệt hiệu quả, máy không quá nóng.', 5),
(19, 11, 'Màn hình 4K HDR xem phim siêu nét. Camera có nhiều tùy chỉnh chuyên nghiệp.', 5);


/* ───────────────────────────────────────────
    6. WISHLIST - Dữ liệu mẫu
   ─────────────────────────────────────────── */
INSERT INTO wishlist (user_id, product_id) VALUES
(1, 7), (1, 13), (1, 18), -- user01 thích Mi 14 Ultra, OP 13 Pro, ROG 8
(2, 1), (2, 3),          -- user02 thích iPhone 15 Pro, S25 Ultra
(5, 1), (5, 8), (5, 15),  -- user05 thích iPhone 15 Pro, Mi 14, Pixel 9 Pro
(8, 15), (8, 1), (8, 7),  -- user08 thích Pixel 9 Pro, iPhone 15 Pro, Mi 14 Ultra
(10, 4), (10, 6),        -- user10 thích Z Flip 6, Reno 12
(13, 9), (13, 18),       -- user13 thích GT Neo6, ROG 8
(15, 11), (15, 19);      -- user15 thích V31 Pro, Xperia 1 VI


/* ───────────────────────────────────────────
    (Tùy chọn) Cập nhật lại rating trung bình cho sản phẩm
   ─────────────────────────────────────────── */
-- Nên chạy script này sau khi INSERT dữ liệu reviews để cột rating trong products được cập nhật chính xác
UPDATE products p
SET rating = COALESCE((SELECT ROUND(AVG(r.rating), 1) FROM reviews r WHERE r.product_id = p.id AND r.rating IS NOT NULL), 0)
WHERE p.id IN (SELECT DISTINCT product_id FROM reviews WHERE rating IS NOT NULL);

