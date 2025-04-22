-- Tắt kiểm tra FK để tránh lỗi khi drop
SET FOREIGN_KEY_CHECKS = 0;

-- Xóa bảng cũ nếu tồn tại
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

-- Bảng users
CREATE TABLE users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       username VARCHAR(50) NOT NULL UNIQUE,
                       email VARCHAR(100) NOT NULL,
                       password VARCHAR(255) NOT NULL,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng orders
CREATE TABLE orders (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        total DOUBLE NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng order_items
CREATE TABLE order_items (
                             id INT AUTO_INCREMENT PRIMARY KEY,
                             order_id INT NOT NULL,
                             product_id INT NOT NULL,
                             quantity INT NOT NULL,
                             price DOUBLE NOT NULL,
                             FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                             FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng reviews
CREATE TABLE reviews (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         product_id INT NOT NULL,
                         content TEXT,
                         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                         FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bật lại kiểm tra FK
SET FOREIGN_KEY_CHECKS = 1;

-- ✅ Seed một số dữ liệu mẫu

/* ───────────────────────────────────────────
   0. BẬT/TẮT FK CHECK ĐỂ CHÈN DỮ LIỆU THUẬN TIỆN
   ─────────────────────────────────────────── */
SET FOREIGN_KEY_CHECKS = 0;

/* ───────────────────────────────────────────
   1. USERS – 20 người dùng giả lập
   (mật khẩu “123456” được băm bcrypt để minh họa)
   ─────────────────────────────────────────── */
INSERT INTO users (username, email, password) VALUES
                                                  ('user01', 'user01@example.com', '$2y$10$abcdefghijklmnopqrstuv01'),
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
   2. PRODUCTS – 20 sản phẩm demo
   ─────────────────────────────────────────── */
INSERT INTO products (name, description, price, image, stock, brand, rating) VALUES
                                                                                 ('iPhone 15 Pro 256GB',       'Flagship Apple 2025',           29990000, 'iphone15pro.jpg',  12, 'Apple',   4.8),
                                                                                 ('iPhone 15 128GB',           'Màu Blue Titanium',             24990000, 'iphone15.jpg',     15, 'Apple',   4.6),
                                                                                 ('Galaxy S25 Ultra 512GB',    'Camera 200 MP',                 26990000, 's25ultra.jpg',     10, 'Samsung', 4.7),
                                                                                 ('Galaxy Z Flip 6',           'Màn hình gập Dynamic AMOLED',   25990000, 'zflip6.jpg',        8, 'Samsung', 4.5),
                                                                                 ('Oppo Find X9 Pro',          'Sạc SuperVOOC 150 W',           18990000, 'findx9.jpg',       20, 'Oppo',    4.4),
                                                                                 ('Oppo Reno 12',              'Selfie 32 MP',                   8990000, 'reno12.jpg',       25, 'Oppo',    4.2),
                                                                                 ('Xiaomi 14 Ultra',           'Leica Optics, Snapdragon 8 G4', 19990000, 'mi14u.jpg',        18, 'Xiaomi',  4.6),
                                                                                 ('Xiaomi 14',                 'Flagship killer',               13990000, 'mi14.jpg',         22, 'Xiaomi',  4.4),
                                                                                 ('Realme GT Neo6',            'Màn 144 Hz',                    11990000, 'gtneo6.jpg',       30, 'Realme',  4.3),
                                                                                 ('Realme C67',                'Pin 5000 mAh',                   3790000, 'c67.jpg',          40, 'Realme',  4.0),
                                                                                 ('Vivo V31 Pro',              'Chụp đêm sắc nét',              10990000, 'v31pro.jpg',       17, 'Vivo',    4.2),
                                                                                 ('Vivo Y100',                 'Thiết kế mỏng nhẹ',              6490000, 'y100.jpg',         35, 'Vivo',    4.1),
                                                                                 ('OnePlus 13 Pro',            'OxygenOS 15, 240 W Charge',     17990000, 'op13pro.jpg',      14, 'OnePlus', 4.5),
                                                                                 ('OnePlus Nord 4',            '5G Giá rẻ',                      7490000, 'nord4.jpg',        28, 'OnePlus', 4.1),
                                                                                 ('Google Pixel 9 Pro',        'Góc rộng UltraWide',            21990000, 'pixel9pro.jpg',    11, 'Google',  4.7),
                                                                                 ('Google Pixel 9a',           'Camera AI Magic Eraser',         9990000, 'pixel9a.jpg',      24, 'Google',  4.3),
                                                                                 ('Motorola Edge 50',          'Thuần Android',                  8990000, 'edge50.jpg',       26, 'Motorola',4.0),
                                                                                 ('Asus ROG Phone 8',          '120 Hz AMOLED + AirTrigger',    24990000, 'rog8.jpg',          9, 'Asus',    4.8),
                                                                                 ('Sony Xperia 1 VI',          'Màn 4K HDR OLED',               27990000, 'xperia1vi.jpg',     7, 'Sony',    4.5),
                                                                                 ('Sony Xperia 10 VI',         'Audio Hi‑Res',                   8990000, 'xperia10vi.jpg',   19, 'Sony',    4.2);

/* ───────────────────────────────────────────
   3. ORDERS – 20 đơn hàng (tham chiếu users)
   ─────────────────────────────────────────── */
INSERT INTO orders (user_id, total) VALUES
                                        (1,  29990000),(2, 23990000),(3, 7490000),(4, 17990000),(5, 8990000),
                                        (6,  26990000),(7, 13990000),(8, 21990000),(9, 9990000),(10,3790000),
                                        (11,24990000),(12,18990000),(13,11990000),(14,19990000),(15,10990000),
                                        (16,8990000),(17,27990000),(18,24990000),(19,13990000),(20,7990000);

/* ───────────────────────────────────────────
   4. ORDER_ITEMS – 20 dòng (tham chiếu orders + products)
   ─────────────────────────────────────────── */
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
                                                                    (1,  1 ,1,29990000),(2,  4 ,1,25990000),(3, 14,1,7490000),(4, 13,1,17990000),
                                                                    (5,  6 ,1,8990000 ),(6,  3 ,1,26990000),(7,  8 ,1,13990000),(8, 15,1,21990000),
                                                                    (9,  16,1,9990000 ),(10,10,1,3790000 ),(11,2 ,1,24990000),(12,5 ,1,18990000),
                                                                    (13,9 ,1,11990000),(14,7 ,1,19990000),(15,11,1,10990000),
                                                                    (16,6 ,2,17980000),(17,19,1,27990000),(18,12,1,6490000 ),
                                                                    (19,8 ,1,13990000),(20,18,1,24990000);

/* ───────────────────────────────────────────
   5. REVIEWS – 20 đánh giá (tham chiếu product)
   ─────────────────────────────────────────── */
INSERT INTO reviews (product_id, content) VALUES
                                              (1 ,'Đỉnh cao công nghệ!'),
                                              (1 ,'Sạc nhanh bá đạo'),
                                              (2 ,'Màn hình gập rất sướng'),
                                              (2 ,'Thiết kế sang xịn'),
                                              (3 ,'Camera 200 MP ăn đứt iPhone'),
                                              (4 ,'Cơ chế gập cải tiến'),
                                              (5 ,'Giá hợp lý so với cấu hình'),
                                              (6 ,'Selfie đẹp lắm!'),
                                              (7 ,'Leica hiệu quả thật'),
                                              (7 ,'Pin trâu hơn iPhone'),
                                              (8 ,'Đúng chất flagship killer'),
                                              (9 ,'144 Hz chơi game phê'),
                                              (10,'Máy mượt so với tầm giá'),
                                              (11,'Chụp đêm ngon'),
                                              (12,'Thiết kế ổn mà giá mềm'),
                                              (13,'Sạc 240 W đúng là thần tốc'),
                                              (14,'Màn 120 Hz mượt'),
                                              (15,'Magic Eraser rất hữu ích'),
                                              (18,'AirTrigger chơi game sướng'),
                                              (19,'Màn 4K HDR đỉnh thật');

SET FOREIGN_KEY_CHECKS = 1;




ALTER TABLE `orders`
ADD COLUMN `customer_name` VARCHAR(255) NOT NULL AFTER `user_id`,
ADD COLUMN `customer_address` TEXT NOT NULL AFTER `customer_name`,
ADD COLUMN `customer_phone` VARCHAR(20) NOT NULL AFTER `customer_address`,
ADD COLUMN `customer_email` VARCHAR(255) DEFAULT NULL AFTER `customer_phone`,
ADD COLUMN `notes` TEXT DEFAULT NULL AFTER `customer_email`,
ADD COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'Pending' AFTER `total`;
-- Các trạng thái ví dụ: Pending, Processing, Shipped, Delivered, Cancelled



CREATE TABLE wishlist (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          user_id INT NOT NULL,
                          product_id INT NOT NULL,
                          added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                          FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    -- Đảm bảo mỗi user chỉ thêm 1 sản phẩm vào wishlist 1 lần duy nhất
                          UNIQUE KEY unique_user_product (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;