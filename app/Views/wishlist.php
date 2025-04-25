<?php
// Web/app/Views/wishlist.php
$wishlistItems = $wishlistItems ?? [];
$flashMessage = $flashMessage ?? null; // Handled by header
$pageTitle = 'Danh sách yêu thích';
include_once __DIR__ . '/../layout/header.php'; // Includes Bootstrap
?>
    <style>
        /* Custom styles for wishlist */
        .wishlist-item-img { width: 70px; height: 70px; object-fit: contain; }
        .product-name-link { text-decoration: none; font-weight: 500; color: #212529;}
        .product-name-link:hover { color: #0d6efd; }
        .action-links .btn-sm i { margin-right: 0.25rem; } /* Icon spacing in buttons */
    </style>

    <div class="container my-4">
        <h1 class="mb-4">Danh sách yêu thích</h1>

        <?php // Flash message display handled by header ?>

        <?php if (empty($wishlistItems)): ?>
            <div class="alert alert-info text-center" role="alert">
                <p class="mb-3 fs-5">Danh sách yêu thích của bạn đang trống.</p>
                <a href="?page=shop_grid" class="btn btn-primary"><i class="fas fa-heart me-2"></i>Khám phá sản phẩm</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 100px;">Ảnh</th>
                        <th scope="col">Sản phẩm</th>
                        <th scope="col" class="text-end">Giá</th>
                        <th scope="col">Tình trạng</th>
                        <th scope="col" class="text-center">Thao tác</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($wishlistItems as $item): ?>
                        <?php
                        $itemId = htmlspecialchars($item['id'] ?? 0);
                        $itemName = htmlspecialchars($item['name'] ?? 'N/A');
                        $itemImage = htmlspecialchars($item['image'] ?? 'default.jpg');
                        $itemPrice = isset($item['price']) ? (float)$item['price'] : 0;
                        $itemStock = isset($item['stock']) ? (int)$item['stock'] : 0;
                        $addedAt = isset($item['added_at']) ? date('d/m/Y', strtotime($item['added_at'])) : 'N/A';
                        ?>
                        <tr>
                            <td>
                                <a href="?page=product_detail&id=<?= $itemId ?>">
                                    <img src="/webfinal/public/img/<?= $itemImage ?>" alt="<?= $itemName ?>" class="img-fluid rounded border wishlist-item-img">
                                </a>
                            </td>
                            <td>
                                <a href="?page=product_detail&id=<?= $itemId ?>" class="product-name-link">
                                    <?= $itemName ?>
                                </a>
                                <small class="d-block text-muted">Đã thêm: <?= $addedAt ?></small>
                            </td>
                            <td class="text-end fw-bold"><?= number_format($itemPrice, 0, ',', '.') ?>₫</td>
                            <td>
                                <?php if ($itemStock > 0): ?>
                                    <span class="badge bg-success">Còn hàng</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Hết hàng</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center action-links">
                                <?php if ($itemStock > 0): ?>
                                    <a href="?page=cart_add&id=<?= $itemId ?>&quantity=1" class="btn btn-sm btn-outline-success me-1" title="Thêm vào giỏ">
                                        <i class="fas fa-cart-plus"></i> <span class="d-none d-md-inline">Thêm vào giỏ</span>
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary me-1" disabled title="Hết hàng">
                                        <i class="fas fa-cart-plus"></i> <span class="d-none d-md-inline">Hết hàng</span>
                                    </button>
                                <?php endif; ?>
                                <a href="?page=wishlist_remove&id=<?= $itemId ?>" class="btn btn-sm btn-outline-danger" title="Xóa khỏi yêu thích" onclick="return confirm('Bạn chắc chắn muốn xóa \"<?= $itemName ?>\" khỏi danh sách yêu thích?')">
                                <i class="fas fa-trash-alt"></i> <span class="d-none d-md-inline">Xóa</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <a href="?page=shop_grid" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Tiếp tục mua sắm</a>
            </div>
        <?php endif; ?>
    </div>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>