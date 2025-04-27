<?php
// Web/app/Views/partials/product_reviews_section.php

// Sử dụng ?? để gán giá trị mặc định an toàn
$reviews = $reviews ?? [];
$productId = $productId ?? 0;
$isLoggedIn = $isLoggedIn ?? false;
$reviewCount = count($reviews);

// Helper function for displaying stars in existing reviews
if (!function_exists('render_stars_review_display')) {
    function render_stars_review_display(float $rating, $maxStars = 5): string {
        $rating = max(0, min($maxStars, $rating)); // Clamp rating
        $output = '';
        for ($i = 1; $i <= $maxStars; $i++) {
            $fullStarClass = 'fas fa-star text-warning small';
            $halfStarClass = 'fas fa-star-half-alt text-warning small';
            $emptyStarClass = 'far fa-star text-muted small'; // Muted outline for empty

            if ($rating >= $i) {
                $output .= '<i class="' . $fullStarClass . '"></i>'; // Full star
            } elseif ($rating >= $i - 0.5) {
                $output .= '<i class="' . $halfStarClass . '"></i>'; // Half star
            } else {
                $output .= '<i class="' . $emptyStarClass . '"></i>'; // Empty star
            }
        }
        return $output;
    }
}

// Lấy lỗi và dữ liệu cũ từ session nếu có (ví dụ sau khi submit lỗi)
$formErrors = $_SESSION['form_errors'] ?? [];
$oldData = $_SESSION['form_data'] ?? [];
// Xóa khỏi session sau khi đọc để không hiển thị lại ở lần tải trang sau
if (!empty($formErrors) || !empty($oldData)) {
    unset($_SESSION['form_errors'], $_SESSION['form_data']);
}
$oldContent = htmlspecialchars($oldData['content'] ?? ''); // Escape output
// Lấy giá trị rating cũ từ input ẩn 'rating'
$oldRatingValue = isset($oldData['rating']) ? (int)$oldData['rating'] : null;

?>
<h2 class="h4 mb-4 mt-3">Đánh giá của khách hàng (<?= $reviewCount ?>)</h2>

<?php // --- Existing Reviews --- ?>
<div class="mb-4 existing-reviews">
    <?php if (empty($reviews)): ?>
        <p class="text-muted">Chưa có đánh giá nào cho sản phẩm này.</p>
    <?php else: ?>
        <?php foreach ($reviews as $review): ?>
            <?php
                // Default reviewer name to 'Ẩn danh' if username is null/empty
                $reviewerName = htmlspecialchars(trim($review['username'] ?? '') ?: 'Ẩn danh');
                $reviewDate = isset($review['created_at']) ? date('d/m/Y H:i', strtotime($review['created_at'])) : '';
                $reviewContent = !empty($review['content']) ? nl2br(htmlspecialchars($review['content'])) : '<i class="text-muted">(Không có nội dung)</i>';
                $reviewRating = isset($review['rating']) ? (int)$review['rating'] : 0;
            ?>
            <div class="review-item">
                <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap">
                    <span class="fw-bold me-2"><?= $reviewerName ?></span>
                    <small class="text-muted text-nowrap"><?= $reviewDate ?></small>
                </div>
                <?php if ($reviewRating > 0): ?>
                    <div class="mb-2 review-item-rating" title="<?= $reviewRating ?> sao">
                        <?= render_stars_review_display($reviewRating) ?>
                        <span class="visually-hidden"><?= $reviewRating ?> trên 5 sao</span>
                    </div>
                <?php endif; ?>
                <p class="mb-0 review-content text-break"><?= $reviewContent ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php // --- Add Review Form --- ?>
<hr id="add-review-form"> <?php // Anchor target ?>
<h3 class="h5 mb-3 mt-4">Gửi đánh giá của bạn</h3>

<?php // Display general form error (like database error if saved in flash)
    // Note: Flash message is usually handled by header.php now.
    // This check might be redundant if BaseController clears flash messages properly.
    // if (isset($_SESSION['flash_message']) && $_SESSION['flash_message']['type'] === 'error' && isset($oldData)) {
    //     echo '<div class="alert alert-danger small" role="alert">' . htmlspecialchars($_SESSION['flash_message']['message']) . '</div>';
    //     unset($_SESSION['flash_message']); // Clear if displayed here
    // }
?>

<?php if ($isLoggedIn): ?>
    <form action="?page=review_add" method="POST" novalidate>
        <input type="hidden" name="product_id" value="<?= $productId ?>">

        <?php // Star Rating Input - structure remains the same ?>
        <div class="mb-3">
            <label class="form-label d-block mb-1 fw-medium">Đánh giá của bạn:</label>
            <?php // Add 'is-invalid' class to the container if there's a rating error ?>
            <div class="rating-stars <?= isset($formErrors['rating']) ? 'is-invalid' : '' ?>" role="radiogroup" aria-label="Chọn đánh giá sao">
                <?php // Generate stars from 5 down to 1 for RTL CSS trick ?>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <?php
                       $isChecked = ($oldRatingValue !== null && $oldRatingValue === $i);
                    ?>
                    <input type="radio" id="star<?= $i ?>" name="rating_radio" value="<?= $i ?>" <?= $isChecked ? 'checked' : '' ?> aria-label="<?= $i ?> sao">
                    <label for="star<?= $i ?>" title="<?= $i ?> sao"><i class="far fa-star"></i></label> <?php // Always use 'far' initially, CSS handles the fill ?>
                <?php endfor; ?>
            </div>
             <?php // Hidden input to store the actual rating value for the form submission ?>
             <input type="hidden" name="rating" id="rating" value="<?= $oldRatingValue ?? '' ?>">
             <?php // Display rating error message BELOW the stars ?>
             <?php if (isset($formErrors['rating'])): ?>
                 <div class="invalid-feedback d-block small mt-1"><?= htmlspecialchars($formErrors['rating']) ?></div>
             <?php endif; ?>
        </div>

        <?php // Review Content Textarea ?>
        <div class="mb-3">
            <label for="review_content" class="form-label fw-medium">Nội dung đánh giá: <span class="text-danger">*</span></label>
            <textarea class="form-control <?= isset($formErrors['content']) ? 'is-invalid' : '' ?>" id="review_content" name="content" rows="4" required minlength="10" placeholder="Chia sẻ cảm nhận của bạn về sản phẩm... (ít nhất 10 ký tự)"><?= $oldContent ?></textarea>
             <?php if (isset($formErrors['content'])): ?>
                 <div class="invalid-feedback"><?= htmlspecialchars($formErrors['content']) ?></div>
             <?php else: ?>
                 <small class="form-text text-muted">Ít nhất 10 ký tự.</small>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i>Gửi đánh giá</button>
    </form>
<?php else: ?>
    <div class="alert alert-warning small py-2"> <?php // Use warning color ?>
        Vui lòng <a href="?page=login&redirect=<?= urlencode('?page=product_detail&id=' . $productId . '#add-review-form') ?>" class="alert-link fw-bold">đăng nhập</a> để gửi đánh giá.
    </div>
<?php endif; ?>
