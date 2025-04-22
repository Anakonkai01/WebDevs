<?php
// Web/app/Views/product_detail.php

// Đặt giá trị mặc định để tránh lỗi nếu biến chưa được Controller truyền sang
$product = $product ?? null;
$reviews = $reviews ?? [];

// Lấy và xóa flash message từ session (nếu có)
// Nên thực hiện việc này ở đầu view để có thể hiển thị ở bất kỳ đâu
if (session_status() == PHP_SESSION_NONE) { // Đảm bảo session đã chạy
    session_start();
}
$flashMessage = $_SESSION['flash_message'] ?? null;
if ($flashMessage) {
    unset($_SESSION['flash_message']); // Xóa khỏi session sau khi lấy
}


// --- Kiểm tra sản phẩm tồn tại ---
if (!$product) {
    // Hiển thị thông báo và dừng nếu không có sản phẩm
    // (Controller cũng nên xử lý việc này trước khi render view)
    echo "<!DOCTYPE html><html lang='vi'><head><meta charset='UTF-8'><title>Lỗi</title></head><body>";
    echo "<p style='color:red; text-align:center; padding: 20px;'>Lỗi: Sản phẩm không tồn tại hoặc không thể tải thông tin.</p>";
    echo "</body></html>";
    return; // Dừng thực thi view
}

// --- Trích xuất thông tin sản phẩm để dễ sử dụng ---
$productId = htmlspecialchars($product['id']);
$productName = htmlspecialchars($product['name']);
$productImage = htmlspecialchars($product['image'] ?? 'default.jpg'); // Cần có ảnh default.jpg trong public/img
$productPrice = number_format($product['price'] ?? 0, 0, ',', '.');
$productDescription = nl2br(htmlspecialchars($product['description'] ?? 'Chưa có mô tả cho sản phẩm này.')); // nl2br để giữ xuống dòng
$productBrand = htmlspecialchars($product['brand'] ?? 'N/A');
$productRating = number_format($product['rating'] ?? 0, 1);
$productStock = (int)($product['stock'] ?? 0);
$productCreatedAt = htmlspecialchars($product['created_at'] ?? ''); // Có thể dùng để hiển thị ngày đăng



// Nhận biến từ Controller, có giá trị mặc định phòng trường hợp controller quên gửi
$isLoggedIn = $isLoggedIn ?? isset($_SESSION['user_id']);
$wishlistedIds = $wishlistedIds ?? []; // Mặc định là mảng rỗng
// Kiểm tra xem SP hiện tại ($productId) có nằm trong danh sách ID đã thích không
$isWishlisted = $isLoggedIn && is_array($wishlistedIds) && in_array($productId, $wishlistedIds); // Thêm kiểm tra is_array


?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $productName ?> - Chi tiết sản phẩm</title>
    <style>
        /* --- General Styles --- */
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; line-height: 1.6; padding: 20px; background-color: #f8f9fa; color: #212529; }
        .container { max-width: 1140px; margin: 20px auto; background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.07); }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        img { max-width: 100%; height: auto; display: block; }
        hr { border: 0; border-top: 1px solid #eee; margin: 30px 0; }
        h1, h2, h3, h4 { margin-top: 0; margin-bottom: 1rem; color: #343a40; }
        h1 { font-size: 2em; }
        h2 { font-size: 1.6em; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px;}
        h4 { font-size: 1.1em; margin-bottom: 10px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #495057; }
        input[type="number"], textarea, button { padding: 10px; font-size: 1em; border-radius: 4px; border: 1px solid #ced4da; box-sizing: border-box; }
        button, .add-to-cart-btn { cursor: pointer; transition: background-color 0.2s ease; }
        button:disabled, .add-to-cart-btn[disabled] { background-color:#ccc; cursor: not-allowed; opacity: 0.7; }

        /* --- Product Detail Layout --- */
        .product-container { display: flex; flex-wrap: wrap; gap: 40px; }
        .product-image { flex: 1 1 350px; text-align: center; }
        .product-image img { border: 1px solid #dee2e6; padding: 5px; border-radius: 4px; max-height: 450px; object-fit: contain;}
        .product-info { flex: 2 1 500px; }
        .price { font-size: 1.8em; color: #dc3545; font-weight: bold; margin-bottom: 15px; }
        .brand, .rating, .stock { margin-bottom: 10px; color: #6c757d; font-size: 0.95em; }
        .brand strong, .rating strong, .stock strong { color: #495057; }
        .description { margin-top: 25px; padding-top: 25px; border-top: 1px solid #eee; }
        .product-actions { margin-top: 20px; display: flex; align-items: center; gap: 20px; flex-wrap: wrap; } /* Container cho nút */
        .wishlist-btn a { color: #adb5bd; text-decoration: none; font-size: 1.8em; transition: color 0.2s ease; }
        .wishlist-btn a:hover { color: #dc3545; }
        .wishlist-btn a.active { color: red; } /* Màu trái tim đỏ khi đã thích */
        .add-to-cart-form { display:inline-flex; margin: 0; align-items: center; gap: 10px;}
        .add-to-cart-form label { margin-bottom: 0; white-space: nowrap; }
        .add-to-cart-form input[type="number"] { width: 60px; padding: 8px; text-align: center;}
        .add-to-cart-btn {
            display: inline-block; padding: 10px 25px; background-color: #007bff; color: white;
            text-decoration: none; border-radius: 5px; border: none; font-size: 1.1em; vertical-align: middle;
        }
        .add-to-cart-btn:hover:not(:disabled) { background-color: #0056b3; }


        /* --- Reviews Section --- */
        .reviews-section { margin-top: 40px; padding-top: 30px; border-top: 1px solid #eee; }
        .review-item { border-bottom: 1px dashed #eee; padding-bottom: 15px; margin-bottom: 15px; }
        .review-item:last-child { border-bottom: none; margin-bottom: 0; }
        .review-item p { margin: 5px 0; white-space: pre-wrap; /* Giữ định dạng xuống dòng */ }
        .review-item small { color: #888; font-size: 0.85em; }
        .add-review-form textarea { width: 100%; min-height: 100px; margin-bottom: 10px; resize: vertical; }
        .add-review-form button { background-color: #28a745; color: white; padding: 10px 20px; }
        .add-review-form button:hover { background-color: #218838; }
        .login-prompt { margin-top: 15px; background-color: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6; text-align: center;}
        .login-prompt a { font-weight: bold; }

        /* --- Flash Messages --- */
        .flash-message { padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid transparent; text-align: center; }
        .flash-message.success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .flash-message.error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .flash-message.info { background-color: #d1ecf1; color: #0c5460; border-color: #bee5eb; }
    </style>
</head>
<body>
<div class="container">
    <?php // Hiển thị flash message (ví dụ: sau khi gửi review, thêm vào giỏ/wishlist...) ?>
    <?php if ($flashMessage): ?>
        <div class="flash-message <?= htmlspecialchars($flashMessage['type']) ?>">
            <?= htmlspecialchars($flashMessage['message']) ?>
        </div>
    <?php endif; ?>

    <div class="product-container">
        <div class="product-image">
            <img src="/public/img/<?= $productImage ?>" alt="<?= $productName ?>" loading="lazy">
            <?php /* Thêm gallery ảnh nhỏ nếu cần */ ?>
        </div>

        <div class="product-info">
            <h1><?= $productName ?></h1>

            <div class="price"><?= $productPrice ?>₫</div>

            <div class="brand">Thương hiệu: <strong><?= $productBrand ?></strong></div>

            <div class="rating">Đánh giá: <strong><?= $productRating ?> ★</strong> (<?= count($reviews) ?> đánh giá)</div>

            <div class="stock">
                Tình trạng:
                <strong><?= ($productStock > 0) ? "Còn hàng ($productStock sản phẩm)" : "Hết hàng" ?></strong>
            </div>

            <div class="product-actions">
                <?php // Nút Wishlist ?>
                <div class="wishlist-btn">
                    <?php if ($isLoggedIn): // Đã đăng nhập ?>
                        <?php if ($isWishlisted): // Đã thích sản phẩm này ?>
                            <a href="?page=wishlist_remove&id=<?= $productId ?>" title="Xóa khỏi Yêu thích" class="active">❤️</a>
                        <?php else: // Chưa thích sản phẩm này ?>
                            <a href="?page=wishlist_add&id=<?= $productId ?>" title="Thêm vào Yêu thích">♡</a>
                        <?php endif; ?>
                    <?php else: // Chưa đăng nhập ?>
                        <?php $redirectUrl = urlencode('?page=product_detail&id='.$productId); ?>
                        <a href="?page=login&redirect=<?= $redirectUrl ?>" title="Đăng nhập để yêu thích">♡</a>
                    <?php endif; ?>
                </div>

                <?php // Form Add to Cart ?>
                <div> <?php // Bọc form trong div để không bị ảnh hưởng bởi flex gap của .product-actions ?>
                    <?php if ($productStock > 0): ?>
                        <form action="?page=cart_add" method="GET" class="add-to-cart-form">
                            <input type="hidden" name="page" value="cart_add"> <?php // Gửi đúng page nếu action trống ?>
                            <input type="hidden" name="id" value="<?= $productId ?>">
                            <label for="quantity_input_<?= $productId ?>">Số lượng:</label>
                            <input type="number" id="quantity_input_<?= $productId ?>" name="quantity" value="1" min="1" max="<?= $productStock; ?>">
                            <button type="submit" class="add-to-cart-btn">Thêm vào giỏ</button>
                        </form>
                    <?php else: ?>
                        <button class="add-to-cart-btn" disabled>Hết hàng</button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="description">
                <h4>Mô tả chi tiết</h4>
                <p><?= $productDescription ?></p>
            </div>

        </div> </div> <hr>

    <div class="reviews-section">
        <h2>Đánh giá sản phẩm (<?= count($reviews) ?>)</h2>

        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <?php // Thêm tên người đánh giá nếu bảng 'reviews' có user_id và bạn join hoặc lấy thông tin user ?>
                    <?php /*
                    <p><strong>Người dùng ABC</strong> (Nếu có user_id)</p>
                    */ ?>
                    <p><?= nl2br(htmlspecialchars($review['content'])) ?></p>
                    <small>Đăng lúc: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($review['created_at']))) ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên đánh giá!</p>
        <?php endif; ?>

        <hr style="margin: 25px 0;">

        <?php // Chỉ hiển thị form nếu người dùng đã đăng nhập ?>
        <?php if (isset($_SESSION['user_id'])): ?>
            <h4>Viết đánh giá của bạn</h4>
            <form action="?page=review_add" method="POST" class="add-review-form">
                <input type="hidden" name="product_id" value="<?= $productId ?>">
                <label for="review_content">Nội dung (ít nhất 10 ký tự):</label>
                <textarea name="content" id="review_content" required placeholder="Chia sẻ cảm nhận của bạn về sản phẩm..." minlength="10" rows="4"></textarea>
                <button type="submit">Gửi đánh giá</button>
            </form>
        <?php else: ?>
            <p class="login-prompt">
                Vui lòng <a href="?page=login&redirect=<?= urlencode('?page=product_detail&id='.$productId) ?>">Đăng nhập</a> để gửi đánh giá của bạn.
            </p>
        <?php endif; ?>
    </div>

    <div style="margin-top: 30px;">
        <a href="?page=shop_grid">&laquo; Quay lại danh sách sản phẩm</a>
    </div>

</div> <?php // End container ?>
</body>
</html>