<?php
// Set default values
$reviews = $reviews ?? [];
$productId = $productId ?? 0;
$isLoggedIn = $isLoggedIn ?? false;
$reviewCount = count($reviews);

// Function to display star ratings
if (!function_exists('render_stars_review_display')) {
    function render_stars_review_display(float $rating, $maxStars = 5): string {
        $rating = max(0, min($maxStars, $rating)); // Limit rating value
        $output = '';
        for ($i = 1; $i <= $maxStars; $i++) {
            $fullStarClass = 'fas fa-star text-warning small';
            $halfStarClass = 'fas fa-star-half-alt text-warning small';
            $emptyStarClass = 'far fa-star text-muted small';

            // Show the type of star based on rating
            if ($rating >= $i) {
                $output .= '<i class="' . $fullStarClass . '"></i>';
            } elseif ($rating >= $i - 0.5) {
                $output .= '<i class="' . $halfStarClass . '"></i>'; // Half star
            } else {
                $output .= '<i class="' . $emptyStarClass . '"></i>'; // Empty star
            }
        }
        return $output;
    }
}

// Get form errors and old data
$formErrors = $_SESSION['form_errors'] ?? [];
$oldData = $_SESSION['form_data'] ?? [];
// Remove from session after getting data
if (!empty($formErrors) || !empty($oldData)) {
    unset($_SESSION['form_errors'], $_SESSION['form_data']);
}
// Get old data
$oldContent = htmlspecialchars($oldData['content'] ?? '');
$oldRatingValue = isset($oldData['rating']) ? (int)$oldData['rating'] : null;

?>
<h2 class="h4 mb-4 mt-3">Đánh giá của khách hàng (<?= $reviewCount ?>)</h2>

<!-- Display Existing Reviews -->
<?php  ?>
<div class="mb-4 existing-reviews">
    <?php if (empty($reviews)): ?>
        <p class="text-muted">Chưa có đánh giá nào cho sản phẩm này.</p>
    <?php else: ?>
        <?php foreach ($reviews as $review): ?>
            <?php
                // Set default reviewer name
                $reviewerName = htmlspecialchars(trim($review['username'] ?? '') ?: 'Ẩn danh');
                // Set review date
                $reviewDate = isset($review['created_at']) ? date('d/m/Y H:i', strtotime($review['created_at'])) : '';
                // Set review content
                $reviewContent = !empty($review['content']) ? nl2br(htmlspecialchars($review['content'])) : '<i class="text-muted">(Không có nội dung)</i>';
                // set review rating
                $reviewRating = isset($review['rating']) ? (int) $review['rating'] : 0;
            ?>
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

<!-- Add Review Form -->
<hr id="add-review-form"> <!-- Anchor target -->
<h3 class="h5 mb-3 mt-4">Gửi đánh giá của bạn</h3>

<!-- Check if the user is logged in -->
<?php if ($isLoggedIn): ?>
    <!-- Review Form -->
    <form action="?page=review_add" method="POST" novalidate>
        <input type="hidden" name="product_id" value="<?= $productId ?>">

        <!-- Star Rating Input -->
        <div class="mb-3">
            <label class="form-label d-block mb-1 fw-medium">Đánh giá của bạn:</label>
            <?php // Add 'is-invalid' class to the container if there's a rating error ?>
            <div class="rating-stars <?= isset($formErrors['rating']) ? 'is-invalid' : '' ?>" role="radiogroup" aria-label="Chọn đánh giá sao">
                <?php // Generate stars from 5 down to 1 for RTL CSS trick ?>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <?php
                        // Check if this star should be checked based on old data
                        $isChecked = ($oldRatingValue !== null && $oldRatingValue === $i);
                    ?>
                    <!-- Radio input for each star -->
                    <input type="radio" id="star<?= $i ?>" name="rating_radio" value="<?= $i ?>" <?= $isChecked ? 'checked' : '' ?> aria-label="<?= $i ?> sao">
                    <!-- Label for each star -->
                    <label for="star<?= $i ?>" title="<?= $i ?> sao"><i class="far fa-star"></i></label> <!-- Always use 'far' initially, CSS handles the fill -->
                <?php endfor; ?>
            </div>
            <!-- Hidden input to store the actual rating value for the form submission -->
            <input type="hidden" name="rating" id="rating" value="<?= $oldRatingValue ?? '' ?>">
            <!-- Display rating error message below the stars -->
            <?php if (isset($formErrors['rating'])): ?>
                <div class="invalid-feedback d-block small mt-1"><?= htmlspecialchars($formErrors['rating']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Review Content Textarea -->
        <div class="mb-3">
            <label for="review_content" class="form-label fw-medium">Nội dung đánh giá: <span class="text-danger">*</span></label>
            <textarea class="form-control <?= isset($formErrors['content']) ? 'is-invalid' : '' ?>" id="review_content" name="content" rows="4" required minlength="10" placeholder="Chia sẻ cảm nhận của bạn về sản phẩm... (ít nhất 10 ký tự)"><?= $oldContent ?></textarea>
             <?php if (isset($formErrors['content'])): ?>
                 <div class="invalid-feedback"><?= htmlspecialchars($formErrors['content']) ?></div>
             <?php else: ?>
                 <small class="form-text text-muted">Ít nhất 10 ký tự.</small>
            <?php endif; ?>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i>Gửi đánh giá</button>
    </form>
<?php else: ?>
    <div class="alert alert-warning small py-2"> <!-- Use warning color -->
        Vui lòng <a href="?page=login&redirect=<?= urlencode('?page=product_detail&id=' . $productId . '#add-review-form') ?>" class="alert-link fw-bold">đăng nhập</a> để gửi đánh giá.
    </div>
<?php endif; ?>