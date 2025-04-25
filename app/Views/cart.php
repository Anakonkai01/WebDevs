<?php
// Web/app/Views/cart.php
$cartItems = $cartItems ?? [];
$totalPrice = $totalPrice ?? 0; // Tổng tiền ban đầu của tất cả item
$flashMessage = $flashMessage ?? null; // Flash message from controller
include_once __DIR__ . '/../layout/header.php'; // Include header
echo '<link rel="stylesheet" href="/webfinal/public/css/cart.css">';
?>

   

    <div class="container my-4">
        <h1 class="mb-4">Giỏ hàng của bạn</h1>

        <?php // Flash message display is handled by header.php ?>

        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info text-center" role="alert">
                <p class="mb-3 fs-5">Giỏ hàng của bạn hiện đang trống.</p>
                <a href="?page=shop_grid" class="btn btn-primary"><i class="fas fa-shopping-bag me-2"></i>Bắt đầu mua sắm</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" id="cart-table">
                    <thead class="table-light">
                    <tr>
                        <th scope="col" class="select-col"><input class="form-check-input cart-select-checkbox" type="checkbox" id="select-all-items" title="Chọn/Bỏ chọn tất cả"></th>
                        <th scope="col" style="width: 100px;">Ảnh</th>
                        <th scope="col">Sản phẩm</th>
                        <th scope="col" class="text-end">Giá</th>
                        <th scope="col" class="text-center" style="width: 130px;">Số lượng</th>
                        <th scope="col" class="text-end">Thành tiền</th>
                        <th scope="col" class="text-center">Xóa</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cartItems as $itemId => $item): ?>
                        <?php
                        $itemName = htmlspecialchars($item['name'] ?? 'N/A');
                        $itemImage = htmlspecialchars($item['image'] ?? 'default.jpg');
                        $itemPrice = isset($item['price']) ? (float)$item['price'] : 0;
                        $itemQuantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
                        $itemSubtotal = $itemPrice * $itemQuantity;
                        $itemStock = (int)($item['stock'] ?? 999);
                        ?>
                        <tr id="cart-item-row-<?= $itemId ?>" data-item-id="<?= $itemId ?>" data-item-price="<?= $itemPrice ?>" data-item-quantity="<?= $itemQuantity ?>" data-item-stock="<?= $itemStock ?>">
                            <td class="select-col"><input class="form-check-input cart-item-select cart-select-checkbox" type="checkbox" value="<?= $itemId ?>" checked></td>
                            <td><a href="?page=product_detail&id=<?= $itemId ?>"><img src="/webfinal/public/img/<?= $itemImage ?>" alt="<?= $itemName ?>" class="img-fluid rounded border cart-item-img"></a></td>
                            <td><a href="?page=product_detail&id=<?= $itemId ?>" class="text-decoration-none fw-bold"><?= $itemName ?></a></td>
                            <td class="text-end"><?= number_format($itemPrice, 0, ',', '.') ?>₫</td>
                            <td class="text-center">
                                <div class="quantity-selector" data-item-id="<?= $itemId ?>" data-item-price="<?= $itemPrice ?>">
                                    <button class="btn quantity-decrease" type="button" <?= $itemQuantity <= 1 ? 'disabled' : '' ?> aria-label="Giảm số lượng">&minus;</button>
                                    <input type="text" class="form-control quantity-display" value="<?= $itemQuantity ?>" readonly aria-live="polite" style="background-color: #fff; cursor: default;">
                                    <button class="btn quantity-increase" type="button" <?= $itemQuantity >= $itemStock ? 'disabled' : '' ?> aria-label="Tăng số lượng">&plus;</button>
                                    <?php // Spinner vẫn còn trong HTML nhưng sẽ không được hiển thị bởi JS ?>
                                    <span class="updating-spinner spinner-border" role="status" aria-hidden="true" style="display: none; width: 1rem; height: 1rem; margin-left: 5px; vertical-align: middle;"></span>
                                </div>
                            </td>
                            <td class="text-end item-subtotal" id="item-subtotal-<?= $itemId ?>"><?= number_format($itemSubtotal, 0, ',', '.') ?>₫</td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" data-item-id="<?= $itemId ?>" data-item-name="<?= $itemName ?>" onclick="removeCartItem(this)"><i class="fas fa-trash-alt"></i></button></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end mt-4">
                <div class="col-md-5">
                    <div class="card cart-summary-box p-3 shadow-sm">
                        <h3 class="card-title h5 mb-3">Tổng cộng (<span id="selected-items-count">0</span> sản phẩm đã chọn)</h3>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold fs-5">Tổng tiền hàng:</span>
                            <span class="fw-bold fs-5 text-danger" id="selected-total-price">0₫</span>
                        </div>
                        <a href="#" id="checkout-button" class="btn btn-success btn-lg w-100 mb-2 disabled" aria-disabled="true">
                            <i class="fas fa-credit-card me-2"></i> Tiến hành thanh toán (<span id="checkout-button-count">0</span>)
                        </a>
                        <a href="?page=shop_grid" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left me-2"></i> Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

   <script src="/webfinal/public/js/cart.js"></script>

<?php
include_once __DIR__ . '/../layout/footer.php'; // Include footer
?>