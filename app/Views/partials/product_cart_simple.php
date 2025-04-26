<?php
// webfinal/app/Views/partials/product_cart_simple.php
// Expects $relP variable from the loop in product_detail.php

if (!isset($relP)) return; // Exit if no product data

$relPId = (int)($relP['id'] ?? 0);
if ($relPId <= 0) return; // Skip if ID is invalid

$relPName = htmlspecialchars($relP['name'] ?? 'N/A');
$relPImage = htmlspecialchars($relP['image'] ?? 'default.jpg');
$relPPrice = (float)($relP['price'] ?? 0);
$relPRating = (float)($relP['rating'] ?? 0);
$relPStock = (int)($relP['stock'] ?? 0);

// Re-use the star rendering function if needed, or define it locally
if (!function_exists('render_stars_simple_related')) { // Use different name to avoid conflict
    function render_stars_simple_related(float $rating, $maxStars = 5): string {
        $rating = max(0, min($maxStars, $rating));
        $output = '';
        for ($i = 1; $i <= $maxStars; $i++) {
            if ($rating >= $i) $output .= '<i class="fas fa-star"></i>';
            elseif ($rating >= $i - 0.5) $output .= '<i class="fas fa-star-half-alt"></i>';
            else $output .= '<i class="far fa-star"></i>';
        }
        return $output;
    }
}
?>
<div class="col">
    <div class="card h-100 shadow-sm product-card-related overflow-hidden">
        <a href="?page=product_detail&id=<?= $relPId ?>" class="d-block text-center p-2 bg-light related-product-img-container">
            <img src="/webfinal/public/img/<?= $relPImage ?>" class="related-product-img" alt="<?= $relPName ?>" loading="lazy">
        </a>
        <div class="card-body d-flex flex-column p-2">
            <h6 class="card-title mb-1 flex-grow-1">
                <a href="?page=product_detail&id=<?= $relPId ?>" class="text-dark text-decoration-none stretched-link product-name-related" title="<?= $relPName ?>">
                    <?= $relPName ?>
                </a>
            </h6>
            <div class="d-flex justify-content-between align-items-center mt-auto mb-1">
                 <span class="price small fw-bold text-danger"><?= number_format($relPPrice, 0, ',', '.') ?>₫</span>
                 <span class="star-rating small text-warning" title="<?= sprintf('%.1f', $relPRating) ?> sao">
                     <?= render_stars_simple_related($relPRating) ?>
                     <span class="visually-hidden"><?= sprintf('%.1f', $relPRating) ?> sao</span>
                 </span>
            </div>
             <?php /* Optional: Stock Status
             <small class="text-muted small <?= $relPStock > 0 ? 'text-success' : 'text-danger' ?>">
                 <?= $relPStock > 0 ? 'Còn hàng' : 'Hết hàng' ?>
             </small>
             */ ?>
        </div>
         <?php /* Optional: Footer Button
         <div class="card-footer bg-white border-top-0 p-2 text-center">
             <a href="?page=product_detail&id=<?= $relPId ?>" class="btn btn-sm btn-outline-primary w-100 stretched-link">Xem chi tiết</a>
         </div>
          */ ?>
    </div>
</div>