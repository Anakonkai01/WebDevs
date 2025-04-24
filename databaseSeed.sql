-- Tắt kiểm tra FK để tránh lỗi khi drop/create
SET FOREIGN_KEY_CHECKS = 0;

-- Xóa bảng cũ nếu tồn tại
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS wishlist; -- Thêm wishlist vào drop list
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

-- Bảng users (Giữ nguyên)
CREATE TABLE users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       username VARCHAR(50) NOT NULL UNIQUE,
                       email VARCHAR(100) NOT NULL UNIQUE, -- Thêm UNIQUE cho email
                       password VARCHAR(255) NOT NULL,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng products *** CẬP NHẬT THÊM CỘT THÔNG SỐ ***
CREATE TABLE products (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          name VARCHAR(255) NOT NULL,
                          description TEXT,
                          price DOUBLE NOT NULL,
                          image VARCHAR(255),
                          stock INT DEFAULT 0,
                          brand VARCHAR(100),
                          rating DOUBLE DEFAULT 0,
    -- *** BẮT ĐẦU THÊM CỘT THÔNG SỐ KỸ THUẬT ***
                          screen_size VARCHAR(100) NULL DEFAULT NULL,
                          screen_tech VARCHAR(100) NULL DEFAULT NULL, -- Công nghệ màn hình
                          cpu VARCHAR(100) NULL DEFAULT NULL,         -- Chip xử lý
                          ram VARCHAR(50) NULL DEFAULT NULL,          -- Dung lượng RAM
                          storage VARCHAR(100) NULL DEFAULT NULL,     -- Bộ nhớ trong
                          rear_camera VARCHAR(255) NULL DEFAULT NULL, -- Camera sau
                          front_camera VARCHAR(150) NULL DEFAULT NULL,-- Camera trước
                          battery_capacity VARCHAR(100) NULL DEFAULT NULL,-- Dung lượng pin
                          os VARCHAR(100) NULL DEFAULT NULL,           -- Hệ điều hành
                          dimensions VARCHAR(100) NULL DEFAULT NULL,   -- Kích thước
                          weight VARCHAR(50) NULL DEFAULT NULL,        -- Trọng lượng
    -- *** KẾT THÚC THÊM CỘT THÔNG SỐ KỸ THUẬT ***
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng orders (Giữ nguyên cấu trúc đã ALTER)
CREATE TABLE orders (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        customer_name VARCHAR(255) NOT NULL, -- Thêm từ ALTER
                        customer_address TEXT NOT NULL,      -- Thêm từ ALTER
                        customer_phone VARCHAR(20) NOT NULL, -- Thêm từ ALTER
                        customer_email VARCHAR(255) DEFAULT NULL, -- Thêm từ ALTER
                        notes TEXT DEFAULT NULL,              -- Thêm từ ALTER
                        total DOUBLE NOT NULL,
                        status VARCHAR(50) NOT NULL DEFAULT 'Pending', -- Thêm từ ALTER
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng order_items (Giữ nguyên)
CREATE TABLE order_items (
                             id INT AUTO_INCREMENT PRIMARY KEY,
                             order_id INT NOT NULL,
                             product_id INT NOT NULL,
                             quantity INT NOT NULL,
                             price DOUBLE NOT NULL,
                             FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                             FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng reviews *** CẬP NHẬT ***
CREATE TABLE reviews (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         product_id INT NOT NULL,
                         user_id INT NULL, -- *** THÊM CỘT user_id, cho phép NULL ***
                         content TEXT,
                         rating TINYINT NULL DEFAULT NULL, -- *** (TÙY CHỌN) Thêm cột rating 1-5 sao ***
                         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                         FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    -- *** THÊM KHÓA NGOẠI cho user_id ***
                         FOREIGN KEY (user_id) REFERENCES users(id)
                             ON DELETE SET NULL -- Nếu user bị xóa, giữ lại review nhưng user_id thành NULL
                             ON UPDATE CASCADE  -- Nếu user_id thay đổi (hiếm), cập nhật ở đây
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng wishlist (Giữ nguyên)
CREATE TABLE wishlist (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          user_id INT NOT NULL,
                          product_id INT NOT NULL,
                          added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                          FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                          UNIQUE KEY unique_user_product (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Bật lại kiểm tra FK
SET FOREIGN_KEY_CHECKS = 1;

-- ✅ Seed một số dữ liệu mẫu

/* ───────────────────────────────────────────
   1. USERS – Dữ liệu mẫu (Giữ nguyên)
   ─────────────────────────────────────────── */
INSERT INTO users (username, email, password) VALUES
                                                  ('user01', 'user01@example.com', '$2y$10$abcdefghijklmnopqrstuv01'), -- Mật khẩu mẫu, cần đăng ký lại để dùng
                                                  ('user02', 'user02@example.com', '$2y$10$abcdefghijklmnopqrstuv02'),
                                                  ('user03', 'user03@example.com', '$2y$10$abcdefghijklmnopqrstuv03'),
                                                  ('user04', 'user04@example.com', '$2y$10$abcdefghijklmnopqrstuv04'),
                                                  ('user05', 'user05@example.com', '$2y$10$abcdefghijklmnopqrstuv05'),
                                                  ('user06', 'user06@example.com', '$2y$10$abcdefghijklmnopqrstuv06'),
                                                  ('user07', 'user07@example.com', '$2y$10$abcdefghijklmnopqrstuv07'),
                                                  ('user08', 'user08@example.com', '$2y$10$abcdefghijklmnopqrstuv08'),
                                                  ('user09', 'user09@example.com', '$2y$10$abcdefghijklmnopqrstuv09'),
                                                  ('user10', 'user10@example.com', '$2y$10$abcdefghijklmnopqrstuv10'),
                                                  ('user11', 'user11@example.com', '$2y$10$abcdefghijklmnopqrstuv11'),
                                                  ('user12', 'user12@example.com', '$2y$10$abcdefghijklmnopqrstuv12'),
                                                  ('user13', 'user13@example.com', '$2y$10$abcdefghijklmnopqrstuv13'),
                                                  ('user14', 'user14@example.com', '$2y$10$abcdefghijklmnopqrstuv14'),
                                                  ('user15', 'user15@example.com', '$2y$10$abcdefghijklmnopqrstuv15'),
                                                  ('user16', 'user16@example.com', '$2y$10$abcdefghijklmnopqrstuv16'),
                                                  ('user17', 'user17@example.com', '$2y$10$abcdefghijklmnopqrstuv17'),
                                                  ('user18', 'user18@example.com', '$2y$10$abcdefghijklmnopqrstuv18'),
                                                  ('user19', 'user19@example.com', '$2y$10$abcdefghijklmnopqrstuv19'),
                                                  ('user20', 'user20@example.com', '$2y$10$abcdefghijklmnopqrstuv20');


/* ───────────────────────────────────────────
   2. PRODUCTS – *** CẬP NHẬT THÊM DỮ LIỆU THÔNG SỐ ***
   ─────────────────────────────────────────── */
-- Thêm các cột mới vào danh sách và thêm dữ liệu mẫu tương ứng
INSERT INTO products (
    name, description, price, image, stock, brand, rating,
    screen_size, screen_tech, cpu, ram, storage, rear_camera, front_camera, battery_capacity, os
) VALUES
      (
          'iPhone 15 Pro 256GB', 'Flagship Apple 2025', 29990000, 'iphone15pro.jpg', 12, 'Apple', 4.8,
          '6.1 inch', 'Super Retina XDR OLED', 'Apple A17 Pro', '8 GB', '256 GB', 'Chính 48 MP & Phụ 12 MP, 12 MP', '12 MP', '3274 mAh', 'iOS 17'
      ),
      (
          'iPhone 15 128GB', 'Màu Blue Titanium', 24990000, 'iphone15.jpg', 15, 'Apple', 4.6,
          '6.1 inch', 'Super Retina XDR OLED', 'Apple A16 Bionic', '6 GB', '128 GB', 'Chính 48 MP & Phụ 12 MP', '12 MP', '3349 mAh', 'iOS 17'
      ),
      (
          'Galaxy S25 Ultra 512GB', 'Camera 200 MP', 26990000, 's25ultra.jpg', 10, 'Samsung', 4.7,
          '6.8 inch', 'Dynamic AMOLED 2X', 'Snapdragon 8 Gen 4 for Galaxy', '12 GB', '512 GB', 'Chính 200 MP & Phụ 12 MP, 10 MP, 10 MP', '12 MP', '5000 mAh', 'Android 15'
      ),
      (
          'Galaxy Z Flip 6', 'Màn hình gập Dynamic AMOLED', 25990000, 'zflip6.jpg', 8, 'Samsung', 4.5,
          'Chính 6.7" & Phụ 3.4"', 'Dynamic AMOLED 2X', 'Snapdragon 8 Gen 3 for Galaxy', '8 GB', '256 GB', 'Chính 12 MP & Phụ 12 MP', '10 MP', '3700 mAh', 'Android 14'
      ),
      (
          'Oppo Find X9 Pro', 'Sạc SuperVOOC 150 W', 18990000, 'findx9.jpg', 20, 'Oppo', 4.4,
          '6.7 inch', 'AMOLED', 'Dimensity 9400', '12 GB', '256 GB', 'Chính 50 MP & Phụ 50 MP, 13 MP', '32 MP', '5000 mAh', 'Android 15'
      ),
      (
          'Oppo Reno 12', 'Selfie 32 MP', 8990000, 'reno12.jpg', 25, 'Oppo', 4.2,
          '6.7 inch', 'AMOLED', 'Dimensity 7300', '8 GB', '256 GB', 'Chính 50 MP & Phụ 8 MP, 2 MP', '32 MP', '5000 mAh', 'Android 14'
      ),
      (
          'Xiaomi 14 Ultra', 'Leica Optics, Snapdragon 8 G4', 19990000, 'mi14u.jpg', 18, 'Xiaomi', 4.6,
          '6.73 inch', 'LTPO AMOLED', 'Snapdragon 8 Gen 4', '16 GB', '512 GB', 'Chính 50 MP & Phụ 50 MP, 50 MP, 50 MP', '32 MP', '5300 mAh', 'Android 15'
      ),
      (
          'Xiaomi 14', 'Flagship killer', 13990000, 'mi14.jpg', 22, 'Xiaomi', 4.4,
          '6.36 inch', 'LTPO OLED', 'Snapdragon 8 Gen 3', '12 GB', '256 GB', 'Chính 50 MP & Phụ 50 MP, 50 MP', '32 MP', '4610 mAh', 'Android 14'
      ),
      (
          'Realme GT Neo6', 'Màn 144 Hz', 11990000, 'gtneo6.jpg', 30, 'Realme', 4.3,
          '6.78 inch', 'AMOLED', 'Snapdragon 8s Gen 3', '12 GB', '256 GB', 'Chính 50 MP & Phụ 8 MP', '32 MP', '5500 mAh', 'Android 14'
      ),
      (
          'Realme C67', 'Pin 5000 mAh', 3790000, 'c67.jpg', 40, 'Realme', 4.0,
          '6.72 inch', 'IPS LCD', 'Snapdragon 685', '8 GB', '128 GB', 'Chính 108 MP & Phụ 2 MP', '8 MP', '5000 mAh', 'Android 14'
      ),
      (
          'Vivo V31 Pro', 'Chụp đêm sắc nét', 10990000, 'v31pro.jpg', 17, 'Vivo', 4.2,
          '6.78 inch', 'AMOLED', 'Dimensity 8200', '12 GB', '256 GB', 'Chính 50 MP & Phụ 12 MP, 8 MP', '50 MP', '4600 mAh', 'Android 14'
      ),
      (
          'Vivo Y100', 'Thiết kế mỏng nhẹ', 6490000, 'y100.jpg', 35, 'Vivo', 4.1,
          '6.67 inch', 'AMOLED', 'Snapdragon 695 5G', '8 GB', '128 GB', 'Chính 64 MP & Phụ 2 MP, 2 MP', '16 MP', '5000 mAh', 'Android 14'
      ),
      (
          'OnePlus 13 Pro', 'OxygenOS 15, 240 W Charge', 17990000, 'op13pro.jpg', 14, 'OnePlus', 4.5,
          '6.7 inch', 'Fluid AMOLED', 'Snapdragon 8 Gen 4', '16 GB', '512 GB', 'Chính 50 MP & Phụ 48 MP, 64 MP', '32 MP', '5000 mAh', 'Android 15'
      ),
      (
          'OnePlus Nord 4', '5G Giá rẻ', 7490000, 'nord4.jpg', 28, 'OnePlus', 4.1,
          '6.74 inch', 'AMOLED', 'Dimensity 1200-AI', '8 GB', '128 GB', 'Chính 50 MP & Phụ 8 MP, 2 MP', '16 MP', '5000 mAh', 'Android 14'
      ),
      (
          'Google Pixel 9 Pro', 'Góc rộng UltraWide', 21990000, 'pixel9pro.jpg', 11, 'Google', 4.7,
          '6.7 inch', 'LTPO OLED', 'Google Tensor G4', '12 GB', '256 GB', 'Chính 50 MP & Phụ 48 MP, 48 MP', '10.5 MP', '5050 mAh', 'Android 15'
      ),
      (
          'Google Pixel 9a', 'Camera AI Magic Eraser', 9990000, 'pixel9a.jpg', 24, 'Google', 4.3,
          '6.1 inch', 'OLED', 'Google Tensor G3', '8 GB', '128 GB', 'Chính 64 MP & Phụ 13 MP', '13 MP', '4385 mAh', 'Android 14'
      ),
      (
          'Motorola Edge 50', 'Thuần Android', 8990000, 'edge50.jpg', 26, 'Motorola', 4.0,
          '6.6 inch', 'pOLED', 'Snapdragon 7 Gen 3', '8 GB', '256 GB', 'Chính 50 MP & Phụ 13 MP', '32 MP', '4500 mAh', 'Android 14'
      ),
      (
          'Asus ROG Phone 8', '120 Hz AMOLED + AirTrigger', 24990000, 'rog8.jpg', 9, 'Asus', 4.8,
          '6.78 inch', 'AMOLED', 'Snapdragon 8 Gen 3', '16 GB', '512 GB', 'Chính 50 MP & Phụ 13 MP, 32 MP', '32 MP', '6000 mAh', 'Android 14'
      ),
      (
          'Sony Xperia 1 VI', 'Màn 4K HDR OLED', 27990000, 'xperia1vi.jpg', 7, 'Sony', 4.5,
          '6.5 inch', 'OLED', 'Snapdragon 8 Gen 3', '12 GB', '256 GB', 'Chính 48 MP & Phụ 12 MP, 12 MP', '12 MP', '5000 mAh', 'Android 14'
      ),
      (
          'Sony Xperia 10 VI', 'Audio Hi‑Res', 8990000, 'xperia10vi.jpg', 19, 'Sony', 4.2,
          '6.1 inch', 'OLED', 'Snapdragon 6 Gen 1', '6 GB', '128 GB', 'Chính 48 MP & Phụ 8 MP', '8 MP', '5000 mAh', 'Android 14'
      );

/* ───────────────────────────────────────────
   3. ORDERS – Dữ liệu mẫu (Thêm thông tin customer)
   ─────────────────────────────────────────── */
-- Thêm dữ liệu customer mẫu cho các đơn hàng
INSERT INTO orders (user_id, customer_name, customer_address, customer_phone, total, status) VALUES
                                                                                                 (1, 'Nguyễn Văn A', '123 Đường ABC, Q.1, TP.HCM', '0901112233', 29990000, 'Delivered'),
                                                                                                 (2, 'Trần Thị B', '456 Đường XYZ, Q.3, TP.HCM', '0904445566', 23990000, 'Shipped'),
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
-- Thêm các đơn hàng khác nếu muốn

/* ───────────────────────────────────────────
   4. ORDER_ITEMS – Dữ liệu mẫu (Giữ nguyên, tham chiếu tới các order ID ở trên)
   ─────────────────────────────────────────── */
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
                                                                    (1,  1 ,1,29990000), -- Order của user 1
                                                                    (2,  4 ,1,25990000), -- Order của user 2
                                                                    (3, 14,1,7490000),  -- Order của user 3
                                                                    (4, 13,1,17990000), -- Order của user 4
                                                                    (5,  6 ,1,8990000 ), -- Order của user 5
                                                                    (6,  3 ,1,26990000), -- Order của user 6
                                                                    (7,  8 ,1,13990000), -- Order của user 7
                                                                    (8, 15,1,21990000), -- Order của user 8
                                                                    (9,  16,1,9990000 ), -- Order của user 9
                                                                    (10,10,1,3790000 ), -- Order của user 10
                                                                    (11, 2,1,24990000), -- Order của user 11
                                                                    (12, 5,1,18990000), -- Order của user 12
                                                                    (13, 9,1,11990000), -- Order của user 13
                                                                    (14, 7,1,19990000), -- Order của user 14
                                                                    (15, 11,1,10990000); -- Order của user 15
-- Có thể thêm các item khác cho các đơn hàng trên hoặc đơn hàng mới

/* ───────────────────────────────────────────
   5. REVIEWS – Dữ liệu mẫu *** CẬP NHẬT THÊM user_id ***
   ─────────────────────────────────────────── */
-- Thêm user_id và rating (ví dụ) cho các đánh giá mẫu
INSERT INTO reviews (product_id, user_id, content, rating) VALUES
                                                               (1, 2, 'Đỉnh cao công nghệ!', 5),
                                                               (1, 5, 'Sạc nhanh bá đạo', 4),
                                                               (4, 3, 'Màn hình gập rất sướng', 5), -- Sản phẩm ID 4 (Z Flip 6)
                                                               (4, 7, 'Thiết kế sang xịn', 4),     -- Sản phẩm ID 4 (Z Flip 6)
                                                               (3, 6, 'Camera 200 MP ăn đứt iPhone', 5), -- Sản phẩm ID 3 (S25 Ultra)
                                                               (4, 8, 'Cơ chế gập cải tiến', 4),     -- Sản phẩm ID 4 (Z Flip 6)
                                                               (5, 12, 'Giá hợp lý so với cấu hình', 4),
                                                               (6, 10, 'Selfie đẹp lắm!', 5),
                                                               (7, 14, 'Leica hiệu quả thật', 5),
                                                               (7, 1, 'Pin trâu hơn iPhone', 4),
                                                               (8, 19, 'Đúng chất flagship killer', 4),
                                                               (9, 13, '144 Hz chơi game phê', 5),
                                                               (10, 15, 'Máy mượt so với tầm giá', 4),
                                                               (11, 16, 'Chụp đêm ngon', 4),
                                                               (12, 18, 'Thiết kế ổn mà giá mềm', 4),
                                                               (13, 4, 'Sạc 240 W đúng là thần tốc', 5),
                                                               (14, 9, 'Màn 120 Hz mượt', 4),
                                                               (15, 8, 'Magic Eraser rất hữu ích', 5),
                                                               (18, 17, 'AirTrigger chơi game sướng', 5),
                                                               (19, 11, 'Màn 4K HDR đỉnh thật', 5);

/* ───────────────────────────────────────────
    6. WISHLIST - Dữ liệu mẫu (Tùy chọn)
   ─────────────────────────────────────────── */
INSERT INTO wishlist (user_id, product_id) VALUES
                                               (1, 7), (1, 13), (1, 18), -- User 1 thích 3 sản phẩm
                                               (2, 1), (2, 3),           -- User 2 thích 2 sản phẩm
                                               (5, 1), (5, 8), (5, 15),
                                               (8, 15), (8, 1), (8, 7);

-- Lưu ý: Các lệnh ALTER TABLE đã được tích hợp vào CREATE TABLE ở trên, không cần chạy lại trừ khi bạn đã tạo bảng theo cấu trúc cũ.
-- Ví dụ ALTER TABLE nếu bảng reviews đã tồn tại mà chưa có user_id:
-- ALTER TABLE reviews ADD COLUMN user_id INT NULL AFTER product_id;
-- ALTER TABLE reviews ADD CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;
-- ALTER TABLE reviews ADD COLUMN rating TINYINT NULL DEFAULT NULL AFTER content;























update product set image = ""