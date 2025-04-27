<?php
// Initialize variables
$cartItems = $cartItems ?? [];
$totalPrice = $totalPrice ?? 0;
$flashMessage = $flashMessage ?? null;

// Include header
include_once __DIR__ . '/layout/header.php';
// Include cart css file
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
            <div class="table-responsive shadow-sm rounded border">
                <table class="table table-bordered table-hover align-middle mb-0" id="cart-table">
                <!-- Table header -->
                    <thead class="table-light">
                        <tr>
                        <th scope="col" class="select-col"><input class="form-check-input cart-select-checkbox" type="checkbox" id="select-all-items" title="Chọn/Bỏ chọn tất cả"></th>
                        <th scope="col" style="width: 80px;">Ảnh</th>
                        <th scope="col">Sản phẩm</th>
                        <th scope="col" class="text-end">Đơn giá</th>
                        <th scope="col" class="text-center" style="width: 120px;">Số lượng</th>
                        <th scope="col" class="text-end">Thành tiền</th>
                        <th scope="col" class="text-center" style="width: 60px;">Xóa</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cartItems as $itemId => $item): ?>
                     <?php
                        $itemName = htmlspecialchars($item['name'] ?? 'N/A');
                        $itemImage = htmlspecialchars($item['image'] ?? 'default.jpg');
                        $itemPrice = isset($item['price']) ? (float)$item['price'] : 0;
                        $itemQuantity = (int)($item['quantity'] ?? 0);
                        $itemSubtotal = $itemPrice * $itemQuantity;
                        // get stock information
                        $productInfoForStock = App\Models\Product::find($itemId);
                        $itemStock = $productInfoForStock ? (int)$productInfoForStock['stock'] : 0; 
                        ?>
                        <tr id="cart-item-row-<?= $itemId ?>" data-item-id="<?= $itemId ?>" data-item-price="<?= $itemPrice ?>" data-item-quantity="<?= $itemQuantity ?>" data-item-stock="<?= $itemStock ?>">
                            <?php // Checkbox for selecting items. ?>
                            <td class="select-col"><input class="form-check-input cart-item-select cart-select-checkbox" type="checkbox" value="<?= $itemId ?>" checked></td>
                            <?php // Item image. ?>
                            <td><a href="?page=product_detail&id=<?= $itemId ?>"><img src="/webfinal/public/img/<?= $itemImage ?>" alt="<?= $itemName ?>" class="img-fluid rounded border cart-item-img"></a></td>
                            <?php // Item name. ?>
                            <td><a href="?page=product_detail&id=<?= $itemId ?>" class="text-decoration-none fw-bold text-dark product-name-link"><?= $itemName ?></a></td>
                            <?php // Item price. ?>
                            <td class="text-end item-price"><?= number_format($itemPrice, 0, ',', '.') ?>₫</td>
                            <?php // Item quantity control. ?>
                            <td class="text-center">
                                <div class="quantity-selector mx-auto" data-item-id="<?= $itemId ?>">
                                    <?php // Button to decrease quantity. ?>
                                    <button class="btn btn-sm quantity-decrease" type="button" aria-label="Giảm số lượng" <?= $itemQuantity <= 1 ? 'disabled' : '' ?>>&minus;</button>
                                    <?php // Display for the current quantity. ?>
                                    <input type="number" class="quantity-display" value="<?= $itemQuantity ?>" min="1" max="<?= $itemStock ?>" readonly aria-live="polite" aria-label="Số lượng sản phẩm <?= $itemName ?>">
                                    <?php // Button to increase quantity. ?>
                                    <button class="btn btn-sm quantity-increase" type="button" aria-label="Tăng số lượng" <?= $itemQuantity >= $itemStock ? 'disabled' : '' ?>>&plus;</button>
                                </div>
                                <?php // Spinner to indicate AJAX update. ?>
                                <span class="updating-spinner spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true" style="display: none; vertical-align: middle;"></span>
                            </td>
                            <?php // Item subtotal. ?>
                            <td class="text-end item-subtotal" id="item-subtotal-<?= $itemId ?>"><?= number_format($itemSubtotal, 0, ',', '.') ?>₫</td>
                            <?php // Remove item button. ?>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" data-item-id="<?= $itemId ?>" data-item-name="<?= $itemName ?>" title="Xóa <?= $itemName ?>"><i class="fas fa-trash-alt"></i></button></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php // Cart summary section. ?>
            <div class="row justify-content-end mt-4">
                <div class="col-md-6 col-lg-5 col-xl-4">
                    <div class="card cart-summary-box p-3 shadow-sm">
                        <?php // Summary title. ?>
                        <h3 class="card-title h5 mb-3">Tổng cộng (<span id="selected-items-count">0</span> sản phẩm)</h3>
                        <?php // Total price of the selected items. ?>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-medium">Tổng tiền hàng:</span>
                            <span class="fw-bold fs-5 text-danger" id="selected-total-price">0₫</span>
                        </div>
                        <?php // Checkout button. ?>
                        <a href="#" id="checkout-button" class="btn btn-success btn-lg w-100 mb-2 disabled" aria-disabled="true">
                            <i class="fas fa-credit-card me-2"></i> Thanh toán (<span id="checkout-button-count">0</span>)
                        </a>
                        <?php // Button to continue shopping. ?>
                        <a href="?page=shop_grid" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left me-2"></i> Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="/webfinal/public/js/cart.js"></script> <?php // Load cart specific JS ?>

<?php
include_once __DIR__ . '/layout/footer.php'; // Include the footer.
?>