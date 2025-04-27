<?php
// Web/app/Views/product_detail.php

// --- Ensure data exists or provide defaults ---
$product = $product ?? null;
$reviews = $reviews ?? [];
$relatedProducts = $relatedProducts ?? [];
$isLoggedIn = $isLoggedIn ?? false; // Comes from BaseController::getGlobalViewData
$wishlistedIds = $wishlistedIds ?? []; // Comes from BaseController::getGlobalViewData

// Attempt to set page title safely
$pageTitle = isset($product['name']) ? htmlspecialchars($product['name']) : 'Chi tiết Sản phẩm';
// Include header only once at the beginning
include_once __DIR__ . '//layout/header.php';

// --- Check if product exists ---
if (!$product || !isset($product['id'])) {
    echo "<div class='container my-4'><div class='alert alert-danger'>Lỗi: Sản phẩm không tồn tại hoặc không thể tải thông tin.</div></div>";
    // Include footer before exiting
    include_once __DIR__ . '//layout/footer.php';
    exit;
}

// --- Extract product data safely ---
$productId = (int)$product['id'];
$productName = htmlspecialchars($product['name'] ?? 'N/A');
$productImage = htmlspecialchars($product['image'] ?? 'default.jpg');
$productPrice = (float)($product['price'] ?? 0);
$productDescription = !empty($product['description']) ? nl2br(htmlspecialchars($product['description'])) : 'Chưa có mô tả.';
$productBrand = htmlspecialchars($product['brand'] ?? 'N/A');
$productRating = (float)($product['rating'] ?? 0);
$productStock = (int)($product['stock'] ?? 0);
$reviewCount = count($reviews); // Recalculate here based on fetched reviews

// --- Wishlist Check ---
$isWishlisted = $isLoggedIn && is_array($wishlistedIds) && in_array($productId, $wishlistedIds);
$wishlistTitle = !$isLoggedIn ? 'Đăng nhập để yêu thích' : ($isWishlisted ? 'Xóa khỏi Yêu thích' : 'Thêm vào Yêu thích');
$wishlistBtnClasses = "btn btn-outline-danger btn-lg btn-wishlist flex-shrink-0"; // Use outline style for consistency
$wishlistBtnClasses .= $isWishlisted ? ' active' : ''; // 'active' class can be used by JS/CSS
$wishlistDisabled = !$isLoggedIn ? 'disabled' : '';

// --- Star Rating Helper Function ---
if (!function_exists('render_stars_pd')) {
    function render_stars_pd(float $rating, $maxStars = 5): string {
        $rating = max(0, min($maxStars, $rating)); // Clamp rating
        $output = '';
        for ($i = 1; $i <= $maxStars; $i++) {
            if ($rating >= $i) {
                $output .= '<i class="fas fa-star text-warning"></i>'; // Full star
            } elseif ($rating >= $i - 0.5) {
                $output .= '<i class="fas fa-star-half-alt text-warning"></i>'; // Half star
            } else {
                $output .= '<i class="far fa-star text-warning"></i>'; // Empty star (outline)
            }
        }
        return $output;
    }
}

// Define the path to the partials directory
$partialsPath = BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR;

?>
<link rel="stylesheet" href="/webfinal/public/css/product_detail.css">

<div class="container my-4 product-detail-container">

    <?php // Breadcrumb ?>
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-light px-3 py-2 rounded-pill small shadow-sm">
            <li class="breadcrumb-item"><a href="?page=home">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="?page=shop_grid">Cửa hàng</a></li>
            <?php if ($productBrand != 'N/A'): ?>
                <li class="breadcrumb-item"><a href="?page=shop_grid&brand=<?= urlencode($productBrand) ?>"><?= $productBrand ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?= $productName ?></li>
        </ol>
    </nav>

    <?php // Flash message display is handled by header.php ?>

    <div class="row g-lg-5 g-4">
        <?php // Product Gallery Column ?>
        <div class="col-lg-6">
            <div class="product-gallery sticky-lg-top bg-white border rounded p-3 text-center shadow-sm" style="top: 80px;">
                <img src="/webfinal/public/img/<?= $productImage ?>" alt="<?= $productName ?>" class="img-fluid product-detail-img mb-3" loading="lazy" id="main-product-image">
                <?php // Optional: Add thumbnail logic later if needed ?>
            </div>
        </div>

        <?php // Product Info Column ?>
        <div class="col-lg-6">
            <div class="product-info-main">
                <h1 class="product-title h2 mb-2 fw-bold"><?= $productName ?></h1>
                <div class="d-flex align-items-center gap-3 mb-3 text-muted flex-wrap small">
                    <span>Thương hiệu: <a href="?page=shop_grid&brand=<?= urlencode($productBrand) ?>" class="text-decoration-none fw-medium"><?= $productBrand ?></a></span>
                    <span class="vr mx-1 d-none d-sm-inline-block"></span>
                    <span class="product-rating-display d-flex align-items-center gap-1" title="<?= sprintf('%.1f', $productRating) ?> sao">
                        <?= render_stars_pd($productRating) ?>
                        <a href="#reviews-content" class="ms-1 text-decoration-none text-muted">(<?= $reviewCount ?> đánh giá)</a>
                    </span>
                </div>
                <div class="product-price display-6 fw-bold text-danger mb-3"><?= number_format($productPrice, 0, ',', '.') ?>₫</div>
                <div class="stock-status fw-medium mb-4 <?= $productStock > 0 ? 'text-success' : 'text-danger' ?>">
                    <i class="fas <?= $productStock > 0 ? 'fa-check-circle' : 'fa-times-circle' ?> me-1"></i>
                    <?= ($productStock > 0) ? "Còn hàng ($productStock sản phẩm)" : "Hết hàng" ?>
                </div>

                <?php // Short Description ?>
                <?php
                   $shortDesc = '';
                   if(!empty($product['description'])) {
                       $sentences = explode('.', strip_tags($product['description']), 2); // Strip tags before exploding
                       $shortDesc = htmlspecialchars(trim($sentences[0] . (isset($sentences[1]) ? '.' : '')));
                   }
                 ?>
                 <?php if($shortDesc): ?>
                 <p class="product-short-description text-muted mb-4">
                     <?= $shortDesc ?> <a href="#desc-content" class="text-decoration-none small ms-1">Xem thêm</a>
                 </p>
                 <?php endif; ?>


                <?php // --- Actions Box --- ?>
                <div class="product-actions-box border rounded p-3 p-md-4 mb-4 bg-light shadow-sm">
                    <form action="?page=cart_add" method="POST" id="add-to-cart-form">
                        <input type="hidden" name="id" value="<?= $productId ?>">
                        <input type="hidden" name="ajax" value="1">
                        <input type="hidden" name="quantity" value="1" id="form-quantity-input">

                        <div class="d-flex flex-wrap flex-sm-nowrap align-items-end gap-3"> <?php // Sử dụng flexbox, căn chỉnh cuối hàng, cho phép wrap trên màn hình nhỏ ?>

                            <?php if ($productStock > 0): ?>
                                <?php // Nhóm Số lượng ?>
                                <div class="quantity-section flex-shrink-0">
                                    <label for="quantity-display-input" class="form-label small mb-1 d-block fw-medium text-nowrap">Số lượng:</label>
                                    <div class="quantity-selector">
                                        <button class="btn btn-light quantity-decrease" type="button" aria-label="Giảm số lượng" disabled>&minus;</button>
                                        <input type="text" id="quantity-display-input" class="quantity-display" value="1" min="1" max="<?= $productStock; ?>" readonly aria-live="polite">
                                        <button class="btn btn-light quantity-increase" type="button" aria-label="Tăng số lượng" <?= ($productStock <= 1) ? 'disabled' : '' ?>>&plus;</button>
                                    </div>
                                </div>

                                <?php // Nhóm Nút Hành động ?>
                                <div class="button-group flex-grow-1">
                                    <?php // Label cho nút (chỉ hiển thị trên trình đọc màn hình) ?>
                                    <label class="form-label small mb-1 d-block fw-medium" style="visibility: hidden;">&nbsp;</label> <?php // Placeholder để căn hàng ?>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg flex-grow-1" id="add-to-cart-btn">
                                            <i class="fas fa-cart-plus me-1 me-sm-2"></i><span class="button-text">Thêm vào giỏ</span>
                                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                                        </button>
                                        <button type="button"
                                                class="<?= $wishlistBtnClasses ?>"
                                                data-product-id="<?= $productId ?>"
                                                data-is-wishlisted="<?= $isWishlisted ? '1' : '0' ?>"
                                                title="<?= $wishlistTitle ?>"
                                                <?= $wishlistDisabled ?>
                                                aria-label="Yêu thích">
                                            <i class="<?= $isWishlisted ? 'fas' : 'far' ?> fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php else: // Product Out of Stock ?>
                                <div class="button-group flex-grow-1">
                                     <label class="form-label small mb-1 d-block fw-medium" style="visibility: hidden;">&nbsp;</label> <?php // Placeholder để căn hàng ?>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-secondary btn-lg flex-grow-1" disabled>
                                            <i class="fas fa-times-circle me-2"></i>Hết hàng
                                        </button>
                                        <button type="button"
                                                class="<?= $wishlistBtnClasses ?>"
                                                data-product-id="<?= $productId ?>"
                                                data-is-wishlisted="<?= $isWishlisted ? '1' : '0' ?>"
                                                title="<?= $wishlistTitle ?>"
                                                <?= $wishlistDisabled ?>
                                                aria-label="Yêu thích">
                                             <i class="<?= $isWishlisted ? 'fas' : 'far' ?> fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                    <div id="add-to-cart-message" class="alert small mt-3 mb-0" role="alert" style="display: none;"></div>
                </div>
                <?php // --- End Actions Box --- ?>

                <?php // --- Social Share --- ?>
                <div class="social-share mt-4 pt-3 border-top small">
                    <span class="fw-medium me-2 text-muted">Chia sẻ:</span>
                    <?php
                        $currentUrl = urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                        $productImageUrl = urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/webfinal/public/img/".$productImage);
                    ?>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $currentUrl ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none me-2 social-icon facebook" title="Chia sẻ lên Facebook"><i class="fab fa-facebook-f fs-5"></i></a>
                    <a href="https://twitter.com/intent/tweet?url=<?= $currentUrl ?>&text=<?= urlencode($productName) ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none me-2 social-icon twitter" title="Chia sẻ lên Twitter"><i class="fab fa-twitter fs-5"></i></a>
                    <a href="mailto:?subject=<?= rawurlencode('Xem sản phẩm này: ' . $productName) ?>&body=<?= rawurlencode('Mình nghĩ bạn sẽ thích sản phẩm này: ' . urldecode($currentUrl)) ?>" class="text-decoration-none me-2 social-icon email" title="Gửi qua Email"><i class="fas fa-envelope fs-5"></i></a>
                 </div>
                <?php // --- End Social Share --- ?>

            </div> <?php // End .product-info-main ?>
        </div> <?php // End Product Info Column ?>
    </div> <?php // End Top Row ?>

    <?php // --- Description, Specs, Reviews Tabs Section --- ?>
    <div class="product-extra-info mt-5 pt-lg-3">
        <nav>
            <div class="nav nav-tabs nav-fill mb-0 border-bottom-0" id="productTab" role="tablist">
                <button class="nav-link active" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc-content" type="button" role="tab" aria-controls="desc-content" aria-selected="true">
                    <i class="fas fa-align-left me-2 d-none d-sm-inline"></i>Mô tả
                </button>
                <button class="nav-link" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs-content" type="button" role="tab" aria-controls="specs-content" aria-selected="false">
                    <i class="fas fa-list-ul me-2 d-none d-sm-inline"></i>Thông số
                </button>
                <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews-content" type="button" role="tab" aria-controls="reviews-content" aria-selected="false">
                    <i class="fas fa-comments me-2 d-none d-sm-inline"></i>Đánh giá (<?= $reviewCount ?>)
                </button>
            </div>
        </nav>
        <div class="tab-content border border-top-0 rounded-bottom p-4 bg-white shadow-sm" id="productTabContent">
            <?php // Description Tab Content ?>
            <div class="tab-pane fade show active" id="desc-content" role="tabpanel" aria-labelledby="desc-tab">
                <h3 class="h5 mb-3 tab-section-title">Mô tả chi tiết sản phẩm</h3>
                <div class="lh-lg description-content text-break"><?= $productDescription ?></div>
            </div>
            <?php // Specs Tab Content ?>
            <div class="tab-pane fade" id="specs-content" role="tabpanel" aria-labelledby="specs-tab">
                <h3 class="h5 mb-3 tab-section-title">Thông số kỹ thuật</h3>
                <?php
                    $specsTablePath = $partialsPath . 'product_specs_table.php';
                    if (is_readable($specsTablePath)) {
                        // Make $product available to the partial
                        include $specsTablePath;
                    } else {
                        echo "<p class='text-muted'>Thông tin thông số kỹ thuật chưa được cập nhật.</p>";
                        error_log("Specs table partial not found or not readable: " . $specsTablePath);
                    }
                ?>
            </div>
             <?php // Reviews Tab Content ?>
            <div class="tab-pane fade reviews-section" id="reviews-content" role="tabpanel" aria-labelledby="reviews-tab">
                 <?php
                     $reviewsSectionPath = $partialsPath . 'product_reviews_section.php';
                     if (is_readable($reviewsSectionPath)) {
                         // Pass necessary variables to the partial
                         // These should be available in the current scope: $reviews, $productId, $isLoggedIn
                         include $reviewsSectionPath;
                     } else {
                         echo "<p class='text-danger'>Lỗi tải phần đánh giá.</p>";
                         error_log("Reviews section partial not found or not readable: " . $reviewsSectionPath);
                     }
                 ?>
            </div>
        </div> <?php // End Tab Content ?>
    </div> <?php // --- End Tabs Section --- ?>

    <?php // --- Related Products Section --- ?>
    <?php if (!empty($relatedProducts)): ?>
        <div class="related-products-section mt-5 pt-5 bg-light py-5 px-3 rounded">
            <h2 class="text-center mb-4 section-title"><span>Sản phẩm liên quan</span></h2>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
                <?php foreach ($relatedProducts as $relP): ?>
                    <?php
                        $relatedCardPath = $partialsPath . 'product_cart_simple.php';
                        if (is_readable($relatedCardPath)) {
                             // Make $relP available to the partial
                            include $relatedCardPath;
                        } else {
                            echo "<div class='col'><p class='text-danger small'>Lỗi: Thiếu partial view.</p></div>";
                            error_log("Failed to include related product card. Path not found: " . $relatedCardPath);
                        }
                    ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    <?php // --- End Related Products --- ?>

    <?php // Back button ?>
    <div class="mt-4 text-center text-md-start">
        <a href="?page=shop_grid" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Quay lại Cửa hàng</a>
    </div>

</div> <?php // End Container ?>

<?php
// Footer includes page-specific JS loader
include_once __DIR__ . '//layout/footer.php';
?>
