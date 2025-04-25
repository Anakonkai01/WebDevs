<?php
// webfinal/app/Views/partials/product_grid_items.php
// Các biến $products, $isLoggedIn, $wishlistedIds được truyền từ Controller qua extract()
?>
<?php if (!empty($products)): ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        <?php foreach ($products as $p): ?>
            <?php
            $pId = (int)($p['id'] ?? 0);
            $stock = (int)($p['stock'] ?? 0);
            // Đảm bảo $wishlistedIds là mảng trước khi dùng in_array
            $isProductWishlisted = $isLoggedIn && is_array($wishlistedIds) && in_array($pId, $wishlistedIds);
            ?>
            <div class="col">
                <div class="card h-100 shadow-sm product-card">
                    <a href="?page=product_detail&id=<?= $pId ?>" class="text-center d-block p-2">
                        <img src="/webfinal/public/img/<?= htmlspecialchars($p['image'] ?? 'default.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name'] ?? '') ?>" loading="lazy">
                    </a>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-1 fs-6">
                            <a href="?page=product_detail&id=<?= $pId ?>" class="text-dark text-decoration-none stretched-link product-name">
                                <?= htmlspecialchars($p['name'] ?? 'N/A') ?>
                            </a>
                        </h5>
                         <p class="card-text text-muted small mb-2 flex-grow-1">
                            <?= htmlspecialchars($p['brand'] ?? 'N/A') ?> | <?= number_format($p['rating'] ?? 0, 1) ?> ★
                        </p>
                        <p class="card-text price fw-bold fs-5 mb-0 mt-auto"><?= number_format($p['price'] ?? 0, 0, ',', '.') ?>₫</p>
                    </div>
                    <div class="card-footer bg-transparent border-top-0 pb-3 pt-2">
                        <div class="actions d-flex justify-content-between align-items-center">
                            <?php // Nút Wishlist - Cần JS để xử lý AJAX (trong shop_grid.js hoặc footer.php) ?>
                            <button type="button"
         class="btn btn-link btn-wishlist p-0 <?= $isProductWishlisted ? 'active' : '' ?> <?= !$isLoggedIn ? 'disabled' : '' ?>"
         data-product-id="<?= $pId ?>"
         data-is-wishlisted="<?= $isProductWishlisted ? '1' : '0' ?>"
         title="<?= !$isLoggedIn ? 'Đăng nhập để yêu thích' : ($isProductWishlisted ? 'Xóa khỏi Yêu thích' : 'Thêm vào Yêu thích') ?>">
     <i class="fas fa-heart fs-4"></i>
 </button>
                             <?php // Nút Cart ?>
                            <a href="?page=product_detail&id=<?= $pId ?>"
                               class="btn btn-link btn-cart p-0 <?= $stock <= 0 ? 'disabled text-secondary' : '' ?>"
                               title="<?= $stock > 0 ? 'Xem chi tiết' : 'Hết hàng' ?>"
                                <?php if($stock <= 0): ?> onclick="event.preventDefault();" aria-disabled="true" <?php endif; ?>>
                                <i class="fas fa-cart-plus fs-4"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-warning" role="alert">
        Không tìm thấy sản phẩm nào khớp với bộ lọc.
    </div>
<?php endif; ?>