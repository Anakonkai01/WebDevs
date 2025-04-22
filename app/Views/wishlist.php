<?php
// Web/app/Views/wishlist.php
$wishlistItems = $wishlistItems ?? [];
$flashMessage = $flashMessage ?? null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách yêu thích</title>
    <style>
        /* Bạn có thể copy và chỉnh sửa style từ cart.php hoặc order_history.php */
        body { font-family: sans-serif; padding: 20px; background-color: #f8f9fa; line-height: 1.6; }
        .container { max-width: 950px; margin: auto; background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 1px 5px rgba(0,0,0,0.1); }
        h1 { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 25px; text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px 15px; text-align: left; vertical-align: middle;}
        th { background-color: #e9ecef; font-weight: bold; white-space: nowrap;}
        td img { max-width: 70px; height: auto; margin-right: 15px; vertical-align: middle; border: 1px solid #eee;}
        .product-name a { text-decoration: none; color: #007bff; font-weight: bold;}
        .product-name a:hover { text-decoration: underline; }
        .action-links a { margin-right: 15px; text-decoration: none; font-weight: 500;}
        .remove-link { color: #dc3545; }
        .add-cart-link { color: #28a745; }
        .no-items { text-align: center; color: #6c757d; padding: 30px; border: 1px dashed #ccc; border-radius: 5px; background-color: #f9f9f9; }
        .no-items p { margin-bottom: 15px; font-size: 1.1em; }
        /* Flash message style */
        .flash-message { padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid transparent; }
        .flash-message.success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .flash-message.error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .flash-message.info { background-color: #d1ecf1; color: #0c5460; border-color: #bee5eb; }
    </style>
</head>
<body>
<div class="container">
    <h1>Danh sách yêu thích</h1>

    <?php if ($flashMessage): ?>
        <div class="flash-message <?= htmlspecialchars($flashMessage['type']) ?>">
            <?= htmlspecialchars($flashMessage['message']) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($wishlistItems)): ?>
        <div class="no-items">
            <p>Danh sách yêu thích của bạn đang trống.</p>
            <a href="?page=shop_grid">Khám phá sản phẩm ngay!</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th colspan="2">Sản phẩm</th>
                <th>Giá</th>
                <th>Tình trạng</th>
                <th>Thao tác</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($wishlistItems as $item): ?>
                <?php
                $itemName = htmlspecialchars($item['name'] ?? 'N/A');
                $itemImage = htmlspecialchars($item['image'] ?? 'default.jpg');
                $itemPrice = isset($item['price']) ? (float)$item['price'] : 0;
                $itemStock = isset($item['stock']) ? (int)$item['stock'] : 0;
                $itemId = htmlspecialchars($item['id']);
                ?>
                <tr>
                    <td style="width: 80px;">
                        <a href="?page=product_detail&id=<?= $itemId ?>">
                            <img src="/public/img/<?= $itemImage ?>" alt="<?= $itemName ?>">
                        </a>
                    </td>
                    <td class="product-name">
                        <a href="?page=product_detail&id=<?= $itemId ?>">
                            <?= $itemName ?>
                        </a>
                        <br><small>Đã thêm: <?= htmlspecialchars(date('d/m/Y', strtotime($item['added_at']))) ?></small>
                    </td>
                    <td><?= number_format($itemPrice, 0, ',', '.') ?>₫</td>
                    <td>
                        <?php if ($itemStock > 0): ?>
                            <span style="color: green;">Còn hàng</span>
                        <?php else: ?>
                            <span style="color: red;">Hết hàng</span>
                        <?php endif; ?>
                    </td>
                    <td class="action-links">
                        <?php // Chỉ hiện nút thêm vào giỏ nếu còn hàng ?>
                        <?php if ($itemStock > 0): ?>
                            <a href="?page=cart_add&id=<?= $itemId ?>&quantity=1" class="add-cart-link">Thêm vào giỏ</a>
                        <?php endif; ?>
                        <a href="?page=wishlist_remove&id=<?= $itemId ?>" class="remove-link" onclick="return confirm('Bạn chắc chắn muốn xóa \"<?= $itemName ?>\" khỏi danh sách yêu thích?')">Xóa</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div style="margin-top: 20px;">
            <a href="?page=shop_grid">&laquo; Tiếp tục mua sắm</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>