<h2>Thông tin chi tiết sản phẩm</h2>
<ul>
<!--    <li><strong>ID:</strong> --><?php //= $product['id'] ?><!--</li>-->
    <li><strong>Tên:</strong> <?= $product['name'] ?></li>
    <li><strong>Mô tả:</strong> <?= $product['description'] ?></li>
    <li><strong>Giá:</strong> <?= number_format($product['price'], 0, ',', '.') ?>₫</li>
    <li><strong>Số lượng còn:</strong> <?= $product['stock'] ?></li>
    <img src="/public/img/<?= htmlspecialchars($product['image']) ?>" width="250" alt="<?= htmlspecialchars($product['name']) ?>">

    <li><strong>Ngày tạo:</strong> <?= $product['created_at'] ?></li>
</ul>
