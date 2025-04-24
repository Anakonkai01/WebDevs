<?php
// Web/app/Views/product_detail.php

// --- START: Include Header ---
$pageTitle = $pageTitle ?? ($product['name'] ?? 'Chi tiết Sản phẩm');
include_once __DIR__ . '/../layout/header.php';
// --- END: Include Header ---


// Đặt giá trị mặc định và kiểm tra biến
$product = $product ?? null;
$reviews = $reviews ?? [];
$relatedProducts = $relatedProducts ?? [];
$isLoggedIn = $isLoggedIn ?? isset($_SESSION['user_id']);
$wishlistedIds = $wishlistedIds ?? [];

// Lấy và xóa flash message từ session (nếu có)
$flashMessage = $_SESSION['flash_message'] ?? null;
if ($flashMessage && is_array($flashMessage)) { // Check if array
    unset($_SESSION['flash_message']); // Xóa khỏi session sau khi lấy
}

// --- Kiểm tra sản phẩm tồn tại ---
if (!$product) {
    echo "<div class='container'><p class='flash-message error'>Lỗi: Sản phẩm không tồn tại hoặc không thể tải thông tin.</p></div>";
    include_once __DIR__ . '/../layout/footer.php'; // Include footer before exiting
    exit;
}

// --- Trích xuất thông tin sản phẩm để dễ sử dụng ---
$productId = (int)$product['id'];
$productName = htmlspecialchars($product['name']);
$productImage = htmlspecialchars($product['image'] ?? 'default.jpg');
$productPrice = number_format($product['price'] ?? 0, 0, ',', '.');
$productDescription = nl2br(htmlspecialchars($product['description'] ?? 'Chưa có mô tả cho sản phẩm này.'));
$productBrand = htmlspecialchars($product['brand'] ?? 'N/A');
$productRating = (float)($product['rating'] ?? 0);
$productStock = (int)($product['stock'] ?? 0);
$reviewCount = count($reviews);
$isWishlisted = $isLoggedIn && is_array($wishlistedIds) && in_array($productId, $wishlistedIds);

// Helper function to generate star rating HTML
function render_stars(float $rating, $maxStars = 5): string {
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
    $emptyStars = $maxStars - $fullStars - $halfStar;
    $html = '';
    for ($i = 0; $i < $fullStars; $i++) $html .= '<i class="fas fa-star"></i>';
    if ($halfStar) $html .= '<i class="fas fa-star-half-alt"></i>';
    for ($i = 0; $i < $emptyStars; $i++) $html .= '<i class="far fa-star"></i>'; // Use far for empty star outline
    return $html;
}

?>
    <style>
        /* --- General Styles (Keep existing or refine) --- */
        .breadcrumb { list-style: none; padding: 10px 0; margin-bottom: 20px; background-color: #e9ecef; border-radius: 4px; }
        .breadcrumb li { display: inline; font-size: 0.9em; }
        .breadcrumb li+li:before { padding: 0 8px; color: #6c757d; content: "/\00a0"; }
        .breadcrumb li a { color: #007bff; }
        .breadcrumb li.active { color: #6c757d; }

        .product-detail-container { display: flex; flex-wrap: wrap; gap: 30px; }
        .product-gallery { flex: 1 1 40%; min-width: 300px; /* Cho phần ảnh */ }
        .product-main-image img { border: 1px solid #dee2e6; padding: 5px; border-radius: 4px; width: 100%; max-height: 450px; object-fit: contain;}
        /* Thêm CSS cho thumbnails nếu có */

        .product-info-main { flex: 1 1 55%; min-width: 300px; /* Cho phần thông tin */ }
        .product-title { font-size: 2em; margin-bottom: 10px; }
        .product-meta { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; color: #6c757d; font-size: 0.9em; flex-wrap: wrap; }
        .product-meta .brand-link { color: #007bff; }
        .product-rating-display { color: #ffc107; /* Màu sao vàng */ }
        .product-rating-display .count { color: #6c757d; margin-left: 5px; }
        .product-price { font-size: 2em; color: #dc3545; font-weight: bold; margin-bottom: 20px; }
        .stock-status { font-weight: bold; margin-bottom: 20px; }
        .stock-status.in-stock { color: #28a745; }
        .stock-status.out-of-stock { color: #dc3545; }

        .product-actions-box { background-color: #f8f9fa; padding: 20px; border-radius: 5px; border: 1px solid #eee; display: flex; align-items: center; gap: 20px; flex-wrap: wrap; margin-bottom: 25px; }
        .wishlist-btn-detail a { color: #adb5bd; text-decoration: none; font-size: 1.8em; transition: color 0.2s ease; }
        .wishlist-btn-detail a:hover { color: #dc3545; }
        .wishlist-btn-detail a.active { color: red; }
        .add-to-cart-form-detail { display:flex; margin: 0; align-items: center; gap: 10px; flex-grow: 1; }
        .add-to-cart-form-detail label { margin-bottom: 0; white-space: nowrap; font-weight: bold; }
        .add-to-cart-form-detail input[type="number"] { width: 65px; padding: 8px; text-align: center; border-radius: 4px; border: 1px solid #ced4da;}
        .add-to-cart-btn-detail { padding: 10px 25px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; border: none; font-size: 1.1em; cursor: pointer; transition: background-color 0.2s ease; }
        .add-to-cart-btn-detail:hover:not(:disabled) { background-color: #0056b3; }
        .add-to-cart-btn-detail:disabled { background-color:#ccc; cursor: not-allowed; opacity: 0.7; }

        .product-extra-info { margin-top: 30px; }
        /* CSS cho Tabs nếu dùng */
        .tabs { display: flex; border-bottom: 1px solid #dee2e6; margin-bottom: 20px; }
        .tab-link { padding: 10px 20px; cursor: pointer; border: 1px solid transparent; border-bottom: none; margin-bottom: -1px; background-color: #f8f9fa; color: #495057; border-radius: 4px 4px 0 0;}
        .tab-link.active { background-color: #fff; border-color: #dee2e6 #dee2e6 #fff; font-weight: bold; color: #007bff;}
        .tab-content { display: none; padding: 20px; border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 4px 4px; }
        .tab-content.active { display: block; }
        #product-description, #product-specs, #product-reviews { line-height: 1.7; }
        #product-specs table { width: 100%; border-collapse: collapse; }
        #product-specs th, #product-specs td { border: 1px solid #eee; padding: 8px; text-align: left; }
        #product-specs th { background-color: #f8f9fa; width: 150px; }

        .reviews-section h3 { font-size: 1.3em; margin-bottom: 15px; }
        .review-item { border-bottom: 1px dashed #eee; padding: 15px 0; }
        .review-item:last-child { border-bottom: none; }
        .review-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; font-size: 0.9em; color: #6c757d; }
        .review-author { font-weight: bold; color: #343a40; }
        .review-content p { margin: 5px 0; white-space: pre-wrap; }
        .add-review-form textarea { width: 100%; min-height: 100px; margin-bottom: 10px; resize: vertical; padding: 10px; border: 1px solid #ced4da; border-radius: 4px;}
        .add-review-form button { background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .add-review-form button:hover { background-color: #218838; }
        .login-prompt { margin-top: 15px; background-color: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6; text-align: center;}

        .related-products-section { margin-top: 40px; padding-top: 30px; border-top: 1px solid #eee; }
        .related-products-section h2 { text-align: center; margin-bottom: 30px; }
        /* Sử dụng lại .products-grid và .product-item từ shop_grid.css hoặc định nghĩa lại nếu cần */

        /* Social Share */
        .social-share { margin-top: 15px; }
        .social-share span { font-weight: bold; margin-right: 10px; }
        .social-share a { margin: 0 5px; font-size: 1.5em; color: #007bff; }
        .social-share a.facebook { color: #3b5998; }
        .social-share a.twitter { color: #1da1f2; }
        /* Thêm các mạng xã hội khác */

    </style>

<?php // Nội dung trang bắt đầu bên trong container của header ?>

<?php // Breadcrumb ?>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?page=home">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="?page=shop_grid">Cửa hàng</a></li>
            <?php // Thêm Danh mục nếu có ?>
            <?php /* <li class="breadcrumb-item"><a href="?page=shop_grid&category=...">Điện thoại</a></li> */ ?>
            <li class="breadcrumb-item active" aria-current="page"><?= $productName ?></li>
        </ol>
    </nav>

<?php // Hiển thị flash message ?>
<?php if ($flashMessage && is_array($flashMessage)): ?>
    <div class="flash-message <?= htmlspecialchars($flashMessage['type'] ?? 'info') ?>">
        <?= htmlspecialchars($flashMessage['message'] ?? '') ?>
    </div>
<?php endif; ?>


    <div class="product-detail-container">
        <div class="product-gallery">
            <div class="product-main-image">
                <img src="/public/img/<?= $productImage ?>" alt="<?= $productName ?>" loading="lazy">
                <?php // Thêm thumbnails ở đây nếu có nhiều ảnh ?>
            </div>
        </div>

        <div class="product-info-main">
            <h1 class="product-title"><?= $productName ?></h1>

            <div class="product-meta">
                <span class="brand">Thương hiệu: <a href="?page=shop_grid&brand=<?= urlencode($product['brand'] ?? '') ?>" class="brand-link"><?= $productBrand ?></a></span>
                <span class="product-rating-display">
                      <?= render_stars($productRating) ?>
                      <span class="count">(<?= $reviewCount ?> đánh giá)</span>
                 </span>
                <?php // Thêm mã SKU nếu có: ?>
                <?php /* <span class="sku">SKU: <?= htmlspecialchars($product['sku'] ?? 'N/A') ?></span> */ ?>
            </div>

            <div class="product-price"><?= $productPrice ?>₫</div>

            <div class="stock-status <?= $productStock > 0 ? 'in-stock' : 'out-of-stock' ?>">
                <i class="fas <?= $productStock > 0 ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                <?= ($productStock > 0) ? "Còn hàng ($productStock sản phẩm)" : "Hết hàng" ?>
            </div>

            <?php // --- Khu vực Add to cart & Wishlist --- ?>
            <div class="product-actions-box">
                <?php // Wishlist Button ?>
                <div class="wishlist-btn-detail">
                    <?php if ($isLoggedIn): ?>
                        <?php if ($isWishlisted): ?>
                            <a href="?page=wishlist_remove&id=<?= $productId ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" title="Xóa khỏi Yêu thích" class="active">❤️</a>
                        <?php else: ?>
                            <a href="?page=wishlist_add&id=<?= $productId ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" title="Thêm vào Yêu thích">♡</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php $redirectUrlDetail = urlencode('?page=product_detail&id='.$productId); ?>
                        <a href="?page=login&redirect=<?= $redirectUrlDetail ?>" title="Đăng nhập để yêu thích">♡</a>
                    <?php endif; ?>
                </div>

                <?php // Add to Cart Form ?>
                <?php if ($productStock > 0): ?>
                    <form action="?page=cart_add" method="GET" class="add-to-cart-form-detail">
                        <input type="hidden" name="page" value="cart_add">
                        <input type="hidden" name="id" value="<?= $productId ?>">
                        <label for="quantity_input_<?= $productId ?>">Số lượng:</label>
                        <input type="number" id="quantity_input_<?= $productId ?>" name="quantity" value="1" min="1" max="<?= $productStock; ?>" aria-label="Số lượng">
                        <button type="submit" class="add-to-cart-btn-detail">Thêm vào giỏ</button>
                    </form>
                <?php else: ?>
                    <button class="add-to-cart-btn-detail" disabled>Hết hàng</button>
                <?php endif; ?>
            </div>
            <?php // --- Hết Khu vực Add to cart & Wishlist --- ?>

            <?php // --- Chia sẻ mạng xã hội --- ?>
            <div class="social-share">
                <span>Chia sẻ:</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>" target="_blank" class="facebook" title="Chia sẻ lên Facebook"><i class="fab fa-facebook-square"></i></a>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($productName) ?>" target="_blank" class="twitter" title="Chia sẻ lên Twitter"><i class="fab fa-twitter-square"></i></a>
                <?php // Thêm Zalo, Pinterest,... nếu muốn ?>
            </div>

        </div> <?php // End product-info-main ?>
    </div> <?php // End product-detail-container ?>


<?php // --- Phần Mô tả, Thông số, Đánh giá (Có thể dùng Tab) --- ?>
    <div class="product-extra-info">
        <?php // --- Sử dụng Tab (Ví dụ đơn giản bằng JS) --- ?>
        <div class="tabs">
            <span class="tab-link active" onclick="openTab(event, 'product-description')">Mô tả</span>
            <span class="tab-link" onclick="openTab(event, 'product-specs')">Thông số kỹ thuật</span>
            <span class="tab-link" onclick="openTab(event, 'product-reviews')">Đánh giá (<?= $reviewCount ?>)</span>
        </div>

        <div id="product-description" class="tab-content active">
            <h2>Mô tả chi tiết</h2>
            <p><?= $productDescription ?></p>
        </div>

        <div id="product-specs" class="tab-content">
            <h2>Thông số kỹ thuật</h2>
            <?php if (
                !empty($product['screen_size']) || !empty($product['screen_tech']) || !empty($product['cpu']) ||
                !empty($product['ram']) || !empty($product['storage']) || !empty($product['rear_camera']) ||
                !empty($product['front_camera']) || !empty($product['battery_capacity']) || !empty($product['os']) ||
                !empty($product['dimensions']) || !empty($product['weight']) || !empty($product['brand']) // Kiểm tra nếu có ít nhất 1 thông số
            ): ?>
                <table>
                    <?php if (!empty($product['screen_size'])): ?>
                        <tr><th>Màn hình</th><td><?= htmlspecialchars($product['screen_size']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($product['screen_tech'])): ?>
                        <tr><th>Công nghệ màn hình</th><td><?= htmlspecialchars($product['screen_tech']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($product['cpu'])): ?>
                        <tr><th>CPU</th><td><?= htmlspecialchars($product['cpu']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($product['ram'])): ?>
                        <tr><th>RAM</th><td><?= htmlspecialchars($product['ram']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($product['storage'])): ?>
                        <tr><th>Bộ nhớ trong</th><td><?= htmlspecialchars($product['storage']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($product['rear_camera'])): ?>
                        <tr><th>Camera sau</th><td><?= htmlspecialchars($product['rear_camera']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($product['front_camera'])): ?>
                        <tr><th>Camera trước</th><td><?= htmlspecialchars($product['front_camera']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($product['battery_capacity'])): ?>
                        <tr><th>Pin & Sạc</th><td><?= htmlspecialchars($product['battery_capacity']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($product['os'])): ?>
                        <tr><th>Hệ điều hành</th><td><?= htmlspecialchars($product['os']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($product['dimensions'])): ?>
                        <tr><th>Kích thước</th><td><?= htmlspecialchars($product['dimensions']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($product['weight'])): ?>
                        <tr><th>Trọng lượng</th><td><?= htmlspecialchars($product['weight']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($product['brand'])): ?>
                        <tr><th>Thương hiệu</th><td><?= htmlspecialchars($product['brand']) ?></td></tr>
                    <?php endif; ?>
                    <?php // Thêm các dòng khác nếu bạn thêm cột khác vào DB ?>
                </table>
            <?php else: ?>
                <p>Thông số kỹ thuật của sản phẩm này đang được cập nhật.</p>
            <?php endif; ?>
        </div>

        <div id="product-reviews" class="tab-content reviews-section">
            <h2>Đánh giá của khách hàng (<?= $reviewCount ?>)</h2>
            <?php if ($reviewCount > 0): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-meta">
                             <span class="review-author">
                                 <i class="fas fa-user"></i>
                                 <?php // Hiển thị username nếu có, nếu không thì là 'Ẩn danh' hoặc bỏ trống ?>
                                 <?= isset($review['username']) ? htmlspecialchars($review['username']) : 'Khách' ?>
                             </span>
                            <span class="review-date">
                                 <i class="far fa-calendar-alt"></i>
                                 <?= htmlspecialchars(date('d/m/Y H:i', strtotime($review['created_at']))) ?>
                             </span>
                        </div>
                        <div class="review-content">
                            <p><?= nl2br(htmlspecialchars($review['content'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên đánh giá!</p>
            <?php endif; ?>

            <hr style="margin: 25px 0;">

            <?php // Form thêm đánh giá ?>
            <?php if ($isLoggedIn): ?>
                <h3>Viết đánh giá của bạn</h3>
                <form action="?page=review_add" method="POST" class="add-review-form">
                    <input type="hidden" name="product_id" value="<?= $productId ?>">
                    <label for="review_content" class="sr-only">Nội dung đánh giá</label>
                    <textarea name="content" id="review_content" required placeholder="Chia sẻ cảm nhận của bạn về sản phẩm..." minlength="10" rows="4"></textarea>
                    <button type="submit">Gửi đánh giá</button>
                </form>
            <?php else: ?>
                <p class="login-prompt">
                    Vui lòng <a href="?page=login&redirect=<?= urlencode('?page=product_detail&id='.$productId) ?>">Đăng nhập</a> để gửi đánh giá của bạn.
                </p>
            <?php endif; ?>
        </div>
    </div>
<?php // --- Hết Phần Mô tả, Thông số, Đánh giá --- ?>


<?php // --- Sản phẩm liên quan --- ?>
<?php if (!empty($relatedProducts)): ?>
    <div class="related-products-section">
        <h2>Sản phẩm liên quan</h2>
        <div class="products-grid"> <?php // Tái sử dụng class grid từ shop_grid ?>
            <?php foreach ($relatedProducts as $relP): ?>
                <div class="product-item"> <?php // Tái sử dụng class item từ shop_grid ?>
                    <a href="?page=product_detail&id=<?= $relP['id'] ?>">
                        <img src="/public/img/<?= htmlspecialchars($relP['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($relP['name']) ?>" loading="lazy">
                    </a>
                    <div class="product-info">
                        <h5><a href="?page=product_detail&id=<?= $relP['id'] ?>"><?= htmlspecialchars($relP['name']) ?></a></h5>
                        <div class="price"><?= number_format($relP['price'], 0, ',', '.') ?>₫</div>
                        <?php // Có thể thêm nút Add to cart/wishlist nhỏ ở đây nếu muốn ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
<?php // --- Hết Sản phẩm liên quan --- ?>

    <div style="margin-top: 30px;">
        <a href="?page=shop_grid">&laquo; Quay lại danh sách sản phẩm</a>
    </div>

<?php // Kết thúc nội dung trang, footer sẽ được include ?>


    <script>
        // JavaScript đơn giản để xử lý Tabs
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tab-link");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
        // Mặc định mở tab đầu tiên (Mô tả)
        // document.addEventListener('DOMContentLoaded', (event) => {
        //    if(document.getElementById('product-description')) { // Check if element exists
        //       document.getElementById('product-description').style.display = "block";
        //    }
        // }); // Không cần thiết nếu đã có class active sẵn trong HTML
    </script>


<?php
// --- START: Include Footer ---
include_once __DIR__ . '/../layout/footer.php';
// --- END: Include Footer ---
?>