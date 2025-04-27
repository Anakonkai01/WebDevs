<?php
// webfinal/app/Views/partials/product_grid_items.php

// Sử dụng ?? để cung cấp giá trị mặc định an toàn
$isLoggedIn = $isLoggedIn ?? false;
$wishlistedIds = (isset($wishlistedIds) && is_array($wishlistedIds)) ? $wishlistedIds : [];

?>
<?php if (!empty($products) && is_array($products)): ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4" id="product-list"> <?php // Thêm ID để dễ dàng target hơn nếu cần ?>
        <?php foreach ($products as $p): ?>
            <?php
                // Lấy dữ liệu sản phẩm an toàn
                $pId = isset($p['id']) ? (int)$p['id'] : 0;
                // Bỏ qua sản phẩm nếu ID không hợp lệ
                if ($pId <= 0) continue;

                $pName = htmlspecialchars($p['name'] ?? 'N/A');
                $pBrand = htmlspecialchars($p['brand'] ?? 'N/A');
                $pRating = isset($p['rating']) ? number_format((float)$p['rating'], 1) : '0.0';
                $pPrice = isset($p['price']) ? number_format((float)$p['price'], 0, ',', '.') : '0';
                $pImage = htmlspecialchars($p['image'] ?? 'default.jpg');
                $pStock = isset($p['stock']) ? (int)$p['stock'] : 0;

                // Xác định trạng thái wishlist và thuộc tính nút
                $isProductWishlisted = $isLoggedIn && in_array($pId, $wishlistedIds);
                $wishlistTitle = !$isLoggedIn ? 'Đăng nhập để thêm vào yêu thích' : ($isProductWishlisted ? 'Xóa khỏi Yêu thích' : 'Thêm vào Yêu thích');
                // Gán class cho nút wishlist
                $wishlistBtnClasses = "btn btn-link btn-wishlist p-0 ";
                if ($isLoggedIn) {
                    $wishlistBtnClasses .= $isProductWishlisted ? 'active text-danger' : 'text-secondary'; // active class quan trọng
                } else {
                    $wishlistBtnClasses .= 'disabled text-secondary'; // Disable nếu chưa đăng nhập
                }
                $wishlistDisabledAttr = !$isLoggedIn ? 'disabled' : ''; // Thuộc tính disabled

                // Thuộc tính nút giỏ hàng
                $cartBtnClasses = "btn btn-link btn-cart p-0 ";
                $cartBtnClasses .= $pStock <= 0 ? 'disabled text-muted' : 'text-success';
                $cartTitle = $pStock > 0 ? 'Xem chi tiết' : 'Hết hàng';
                $cartLink = "?page=product_detail&id={$pId}";
                $cartOnClick = $pStock <= 0 ? 'event.preventDefault();' : ''; // Ngăn click nếu hết hàng
                $cartDisabledAttr = $pStock <= 0 ? 'aria-disabled="true"' : '';
            ?>
            <div class="col">
                <div class="card h-100 shadow-sm product-card">
                    <a href="<?= $cartLink ?>" class="text-center d-block p-2 product-image-link">
                        <img src="/webfinal/public/img/<?= $pImage ?>" class="card-img-top" alt="<?= $pName ?>" loading="lazy" style="max-height: 200px; object-fit: contain;">
                    </a>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fs-6 mb-2" style="min-height: 3em;">
                            <a href="<?= $cartLink ?>" class="text-dark text-decoration-none product-name">
                                <?= $pName ?>
                            </a>
                        </h5>
                         <p class="card-text text-muted small mb-2 flex-grow-1">
                            <?= $pBrand ?> | <?= $pRating ?> <i class="fas fa-star fa-xs text-warning"></i>
                        </p>
                        <p class="card-text price fw-bold fs-5 mb-0 mt-auto text-danger"><?= $pPrice ?>₫</p>
                    </div>
                    <div class="card-footer bg-transparent border-top-0 pb-3 pt-2">
                        <div class="actions d-flex justify-content-between align-items-center">
                            <button type="button"
                                    class="<?= $wishlistBtnClasses ?>"
                                    data-product-id="<?= $pId ?>"
                                    data-is-wishlisted="<?= $isProductWishlisted ? '1' : '0' ?>" <?php // Quan trọng cho JS ?>
                                    title="<?= $wishlistTitle ?>"
                                    <?= $wishlistDisabledAttr ?>
                                    aria-label="<?= $wishlistTitle ?>">
                                <?php // Sử dụng fas cho filled heart, far cho outline ?>
                                <i class="<?= $isLoggedIn && $isProductWishlisted ? 'fas' : 'far' ?> fa-heart fs-4"></i>
                            </button>
                            <a href="<?= $cartLink ?>"
                               class="<?= $cartBtnClasses ?>"
                               title="<?= $cartTitle ?>"
                               aria-label="<?= $cartTitle ?>"
                               <?= $cartDisabledAttr ?>
                               onclick="<?= $cartOnClick ?>">
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
