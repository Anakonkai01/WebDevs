<?php
// Web/app/Views/product_detail.php
$product = $product ?? null;
// Attempt to set page title safely
$pageTitle = isset($product['name']) ? htmlspecialchars($product['name']) : 'Chi tiết Sản phẩm';
include_once __DIR__ . '/../layout/header.php';

$reviews = $reviews ?? [];
$relatedProducts = $relatedProducts ?? [];
$isLoggedIn = $isLoggedIn ?? isset($_SESSION['user_id']); // Default check if not passed
$wishlistedIds = $wishlistedIds ?? [];

// --- Check if product exists ---
if (!$product || !isset($product['id'])) { // Added check for product ID
    echo "<div class='container my-4'><div class='alert alert-danger'>Lỗi: Sản phẩm không tồn tại hoặc không thể tải thông tin.</div></div>";
    include_once __DIR__ . '/../layout/footer.php';
    exit;
}

// --- Extract product data safely ---
$productId = (int)$product['id']; // Already checked existence, so ID should be there
$productName = htmlspecialchars($product['name'] ?? 'N/A');
$productImage = htmlspecialchars($product['image'] ?? 'default.jpg');
$productPrice = (float)($product['price'] ?? 0);
$productDescription = nl2br(htmlspecialchars($product['description'] ?? 'Chưa có mô tả.'));
$productBrand = htmlspecialchars($product['brand'] ?? 'N/A');
$productRating = (float)($product['rating'] ?? 0);
$productStock = (int)($product['stock'] ?? 0);
$reviewCount = count($reviews);

// --- Wishlist Check (ensure $wishlistedIds is an array) ---
$isWishlisted = false;
if ($isLoggedIn && is_array($wishlistedIds)) { // Check if it's an array before using in_array
    $isWishlisted = in_array($productId, $wishlistedIds);
}

// Helper function to generate star rating HTML
function render_stars_pd(float $rating, $maxStars = 5): string {
    $rating = max(0, min($maxStars, $rating)); // Clamp rating between 0 and maxStars
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
    $emptyStars = $maxStars - $fullStars - $halfStar;
    $html = str_repeat('<i class="fas fa-star text-warning"></i>', $fullStars);
    if ($halfStar) $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
    $html .= str_repeat('<i class="far fa-star text-warning"></i>', $emptyStars);
    return $html;
}
?>
<link rel="stylesheet" href="/webfinal/public/css/product_detail.css">



<?php // Breadcrumb ?>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light px-3 py-2 rounded-3">
            <li class="breadcrumb-item"><a href="?page=home">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="?page=shop_grid">Cửa hàng</a></li>
            <?php if ($productBrand != 'N/A'): ?>
                <li class="breadcrumb-item"><a href="?page=shop_grid&brand=<?= urlencode($productBrand) ?>"><?= $productBrand ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?= $productName ?></li>
        </ol>
    </nav>

<?php // Flash message display is handled by header.php ?>

    <div class="row g-4">
        <?php // Product Gallery Column ?>
        <div class="col-lg-5">
            <div class="product-gallery border rounded p-3 text-center shadow-sm">
                <img src="/webfinal/public/img/<?= $productImage ?>" alt="<?= $productName ?>" class="img-fluid" loading="lazy">
                <?php // Add thumbnail gallery here if needed ?>
            </div>
        </div>

        <?php // Product Info Column ?>
        <div class="col-lg-7">
            <h1><?= $productName ?></h1>

            <div class="d-flex align-items-center gap-3 mb-3 text-muted flex-wrap">
                <span>Thương hiệu: <a href="?page=shop_grid&brand=<?= urlencode($productBrand) ?>" class="text-decoration-none"><?= $productBrand ?></a></span>
                <span class="vr"></span>
                <span class="product-rating-display">
                  <?= render_stars_pd($productRating) ?>
                  <a href="#reviews-content" class="ms-1 text-decoration-none">(<?= $reviewCount ?> đánh giá)</a>
             </span>
            </div>

            <div class="fs-2 fw-bold text-danger mb-3"><?= number_format($productPrice, 0, ',', '.') ?>₫</div>

            <div class="stock-status fw-bold mb-3 <?= $productStock > 0 ? 'in-stock' : 'out-of-stock' ?>">
                <i class="fas <?= $productStock > 0 ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                <?= ($productStock > 0) ? "Còn hàng ($productStock sản phẩm)" : "Hết hàng" ?>
            </div>

            <?php // Actions Box ?>
            <div class="product-actions-box border rounded p-3 mb-4">
                <?php // --- FORM ADD TO CART (AJAX) --- ?>
                <form action="?page=cart_add" method="POST" id="add-to-cart-form" class="d-flex align-items-center gap-3">
                    <input type="hidden" name="page" value="cart_add"> <?php // For routing ?>
                    <input type="hidden" name="id" value="<?= $productId ?>">
                    <input type="hidden" name="ajax" value="1"> <?php // Indicate AJAX request ?>

                    <?php // Wishlist Button - BỎ ONCLICK ?>
                    <button type="button"
                            class="btn btn-link btn-wishlist p-0 <?= $isWishlisted ? 'active' : '' ?> <?= !$isLoggedIn ? 'disabled' : '' ?>"
                            data-product-id="<?= $productId ?>"
                            data-is-wishlisted="<?= $isWishlisted ? '1' : '0' ?>"
                            title="<?= !$isLoggedIn ? 'Đăng nhập để yêu thích' : ($isWishlisted ? 'Xóa khỏi Yêu thích' : 'Thêm vào Yêu thích') ?>"
                    >
                        <i class="fas fa-heart"></i>
                    </button>

                    <?php // Quantity and Add to Cart Button ?>
                    <?php if ($productStock > 0): ?>
                        <div class="flex-grow-1 d-flex align-items-center gap-2">
                            <label for="quantity_input_<?= $productId ?>" class="form-label mb-0 fw-bold small text-nowrap">Số lượng:</label>
                            <input type="number" id="quantity_input_<?= $productId ?>" name="quantity" value="1" min="1" max="<?= $productStock; ?>" class="form-control form-control-sm" style="width: 70px;" aria-label="Số lượng">
                            <button type="submit" class="btn btn-primary btn-lg flex-grow-1" id="add-to-cart-btn">
                                <i class="fas fa-cart-plus me-2"></i><span class="button-text">Thêm vào giỏ</span>
                                <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span> <?php // Spinner ?>
                            </button>
                        </div>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary btn-lg flex-grow-1" disabled>
                            <i class="fas fa-times-circle me-2"></i>Hết hàng
                        </button>
                    <?php endif; ?>
                </form>
                <?php // --- END FORM --- ?>
                <div id="add-to-cart-message" class="alert small mt-3" role="alert" style="display: none;"></div> <?php // Message div ?>
            </div>
            <?php // --- End Actions Box --- ?>

            <?php // --- Social Share --- ?>
            <div class="social-share mt-3">
                <span class="fw-bold me-2">Chia sẻ:</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>" target="_blank" class="text-decoration-none fs-4 me-2" style="color: #3b5998;" title="Chia sẻ lên Facebook"><i class="fab fa-facebook-square"></i></a>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($productName) ?>" target="_blank" class="text-decoration-none fs-4 me-2" style="color: #1da1f2;" title="Chia sẻ lên Twitter"><i class="fab fa-twitter-square"></i></a>
            </div>

        </div> <?php // End Product Info Column ?>
    </div> <?php // End Row ?>

<?php // --- Description, Specs, Reviews Tabs --- ?>
    <div class="product-extra-info mt-5">
        <nav>
            <div class="nav nav-tabs" id="productTab" role="tablist">
                <button class="nav-link active" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc-content" type="button" role="tab" aria-controls="desc-content" aria-selected="true">Mô tả</button>
                <button class="nav-link" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs-content" type="button" role="tab" aria-controls="specs-content" aria-selected="false">Thông số kỹ thuật</button>
                <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews-content" type="button" role="tab" aria-controls="reviews-content" aria-selected="false">Đánh giá (<?= $reviewCount ?>)</button>
            </div>
        </nav>
        <div class="tab-content p-4" id="productTabContent">
            <div class="tab-pane fade show active" id="desc-content" role="tabpanel" aria-labelledby="desc-tab">
                <h2 class="h4">Mô tả chi tiết</h2>
                <div class="lh-lg"><?= $productDescription ?></div>
            </div>
            <div class="tab-pane fade" id="specs-content" role="tabpanel" aria-labelledby="specs-tab">
                <h2 class="h4">Thông số kỹ thuật</h2>
                <?php
                $specs = array_filter([ 'Màn hình' => $product['screen_size'] ?? null, 'Công nghệ màn hình' => $product['screen_tech'] ?? null, 'CPU' => $product['cpu'] ?? null, 'RAM' => $product['ram'] ?? null, 'Bộ nhớ trong' => $product['storage'] ?? null, 'Camera sau' => $product['rear_camera'] ?? null, 'Camera trước' => $product['front_camera'] ?? null, 'Pin & Sạc' => $product['battery_capacity'] ?? null, 'Hệ điều hành' => $product['os'] ?? null, 'Kích thước' => $product['dimensions'] ?? null, 'Trọng lượng' => $product['weight'] ?? null, 'Thương hiệu' => $product['brand'] ?? null, ]);
                ?>
                <?php if (!empty($specs)): ?> <table class="table table-striped table-bordered" id="product-specs"><tbody> <?php foreach ($specs as $key => $value): ?> <tr><th scope="row"><?= htmlspecialchars($key) ?></th><td><?= htmlspecialchars($value) ?></td></tr> <?php endforeach; ?> </tbody></table> <?php else: ?> <p class="text-muted">Thông số kỹ thuật của sản phẩm này đang được cập nhật.</p> <?php endif; ?>
            </div>
            <div class="tab-pane fade reviews-section" id="reviews-content" role="tabpanel" aria-labelledby="reviews-tab">
                <h2 class="h4 mb-3">Đánh giá của khách hàng (<?= $reviewCount ?>)</h2>
                <?php if ($reviewCount > 0): foreach ($reviews as $review): ?><div class="review-item py-3"><div class="d-flex justify-content-between align-items-center mb-1"><span class="review-author fw-bold"><i class="fas fa-user me-1 text-secondary"></i> <?= isset($review['username']) ? htmlspecialchars($review['username']) : 'Khách' ?></span><span class="review-date text-muted small"><i class="far fa-calendar-alt me-1"></i> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($review['created_at']))) ?></span></div><?php if (isset($review['rating']) && $review['rating'] > 0): ?><div class="mb-2"><?= render_stars_pd($review['rating']) ?></div><?php endif; ?><div class="review-content"><p class="mb-0"><?= nl2br(htmlspecialchars($review['content'])) ?></p></div></div><?php endforeach; else: ?> <p class="text-muted">Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên đánh giá!</p> <?php endif; ?>
                <hr class="my-4">
                <?php // Add Review Form ?>
                <?php if ($isLoggedIn): ?><h3 class="h5 mb-3">Viết đánh giá của bạn</h3><form action="?page=review_add" method="POST" class="add-review-form"><input type="hidden" name="product_id" value="<?= $productId ?>"><div class="mb-3"><label class="form-label">Đánh giá:</label><div><?php for ($i = 5; $i >= 1; $i--): ?><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="rating" id="rating<?= $i ?>" value="<?= $i ?>"><label class="form-check-label" for="rating<?= $i ?>"><?= $i ?> <i class="fas fa-star text-warning"></i></label></div><?php endfor; ?></div></div><div class="mb-3"><label for="review_content" class="form-label">Nội dung đánh giá <span class="text-danger">*</span></label><textarea name="content" id="review_content" class="form-control" required placeholder="Chia sẻ cảm nhận của bạn..." minlength="10" rows="4"></textarea></div><button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-2"></i>Gửi đánh giá</button></form><?php else: ?><div class="alert alert-secondary text-center" role="alert"> Vui lòng <a href="?page=login&redirect=<?= urlencode('?page=product_detail&id='.$productId.'#reviews-content') ?>" class="alert-link">Đăng nhập</a> để gửi đánh giá của bạn.</div><?php endif; ?>
            </div>
        </div>
    </div>
<?php // --- End Tabs --- ?>


<?php // --- Related Products --- ?>
<?php if (!empty($relatedProducts)): ?><div class="related-products-section mt-5 pt-4 border-top"><h2 class="text-center mb-4">Sản phẩm liên quan</h2><div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4"><?php foreach ($relatedProducts as $relP): ?><div class="col"><div class="card h-100 shadow-sm"><a href="?page=product_detail&id=<?= $relP['id'] ?>"><img src="/webfinal/public/img/<?= htmlspecialchars($relP['image'] ?? 'default.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($relP['name']) ?>" loading="lazy"></a><div class="card-body d-flex flex-column"><h5 class="card-title small flex-grow-1"><a href="?page=product_detail&id=<?= $relP['id'] ?>" class="text-dark text-decoration-none stretched-link"><?= htmlspecialchars($relP['name']) ?></a></h5><p class="card-text price fw-bold mt-auto"><?= number_format($relP['price'], 0, ',', '.') ?>₫</p></div></div></div><?php endforeach; ?></div></div><?php endif; ?>
<?php // --- End Related Products --- ?>

    <div class="mt-4">
        <a href="?page=shop_grid" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Quay lại Cửa hàng</a>
    </div>

    <script src="/webfinal/public/js/product_detail.js"></script>

<?php
include_once __DIR__ . '/../layout/footer.php'; // Footer sẽ chứa event listener mới
?>