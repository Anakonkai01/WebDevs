<?php
// Web/app/Views/cart.php
$cartItems = $cartItems ?? [];
$totalPrice = $totalPrice ?? 0;
$flashMessage = $flashMessage ?? null; // Flash message from controller
include_once __DIR__ . '/../layout/header.php'; // Include header
?>

    <style>
        /* Minimal custom styles for cart */
        .quantity-input { max-width: 75px; }
        .cart-summary-box { background-color: #f8f9fa; border: 1px solid #dee2e6;}
        /* Spinner styling */
        .updating-spinner { display: none; /* Initially hidden */ }
    </style>

    <div class="container my-4">
        <h1 class="mb-4">Giỏ hàng của bạn</h1>

        <?php // Flash message display is handled by header.php ?>

        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info text-center" role="alert">
                <p class="mb-3 fs-5">Giỏ hàng của bạn hiện đang trống.</p>
                <a href="?page=shop_grid" class="btn btn-primary"><i class="fas fa-shopping-bag me-2"></i>Bắt đầu mua sắm</a>
            </div>
        <?php else: ?>
            <div class="table-responsive"> <?php // Make table responsive ?>
                <table class="table table-bordered table-hover align-middle" id="cart-table">
                    <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 100px;">Ảnh</th>
                        <th scope="col">Sản phẩm</th>
                        <th scope="col" class="text-end">Giá</th>
                        <th scope="col" class="text-center" style="width: 120px;">Số lượng</th>
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
                        ?>
                        <tr id="cart-item-row-<?= $itemId ?>">
                            <td>
                                <a href="?page=product_detail&id=<?= $itemId ?>">
                                    <img src="/public/img/<?= $itemImage ?>" alt="<?= $itemName ?>" class="img-fluid rounded border cart-item-img">
                                </a>
                            </td>
                            <td class="product-name">
                                <a href="?page=product_detail&id=<?= $itemId ?>" class="text-decoration-none fw-bold">
                                    <?= $itemName ?>
                                </a>
                            </td>
                            <td class="text-end"><?= number_format($itemPrice, 0, ',', '.') ?>₫</td>
                            <td class="text-center">
                                <div class="input-group input-group-sm justify-content-center">
                                    <?php // Added data attributes for AJAX ?>
                                    <input type="number"
                                           name="quantities[<?= $itemId ?>]"
                                           value="<?= $itemQuantity ?>"
                                           min="1"
                                           class="form-control quantity-input"
                                           aria-label="Số lượng cho <?= $itemName ?>"
                                           data-item-id="<?= $itemId ?>"
                                           data-item-price="<?= $itemPrice ?>"
                                           onchange="updateCartItem(this)"> <?php // Call JS function on change ?>
                                    <span class="updating-spinner spinner-border spinner-border-sm ms-1 align-self-center" role="status" aria-hidden="true"></span>
                                </div>
                            </td>
                            <td class="text-end item-subtotal" id="item-subtotal-<?= $itemId ?>">
                                <?= number_format($itemSubtotal, 0, ',', '.') ?>₫
                            </td>
                            <td class="text-center">
                                <?php // Button instead of link for AJAX removal ?>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn"
                                        data-item-id="<?= $itemId ?>"
                                        data-item-name="<?= $itemName ?>"
                                        onclick="removeCartItem(this)">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end mt-4">
                <div class="col-md-5">
                    <div class="card cart-summary-box p-3">
                        <h3 class="card-title mb-3">Tổng cộng</h3>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold fs-5">Tổng tiền hàng:</span>
                            <span class="fw-bold fs-5 text-danger" id="cart-total-price">
                            <?= number_format($totalPrice, 0, ',', '.') ?>₫
                        </span>
                        </div>
                        <a href="?page=checkout" class="btn btn-success btn-lg w-100 mb-2 <?= empty($cartItems) ? 'disabled' : '' ?>">
                            <i class="fas fa-credit-card me-2"></i> Tiến hành thanh toán
                        </a>
                        <a href="?page=shop_grid" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left me-2"></i> Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

<?php // Add JavaScript for AJAX Cart Updates (Place this before the footer include or in a separate JS file) ?>
    <script>
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount).replace(/\s/g, ''); // Remove extra spaces if any
        }

        function showSpinner(inputElement, show = true) {
            const spinner = inputElement.closest('.input-group').querySelector('.updating-spinner');
            if (spinner) {
                spinner.style.display = show ? 'inline-block' : 'none';
            }
        }

        // Function to update quantity via AJAX
        async function updateCartItem(inputElement) {
            const itemId = inputElement.dataset.itemId;
            const newQuantity = parseInt(inputElement.value);
            const itemPrice = parseFloat(inputElement.dataset.itemPrice);

            if (newQuantity <= 0 || isNaN(newQuantity)) {
                // Optionally remove item if quantity is 0 or invalid, or reset to 1
                inputElement.value = 1; // Reset to 1 for simplicity
                updateCartItem(inputElement); // Re-run update
                return;
            }

            showSpinner(inputElement, true); // Show spinner
            inputElement.disabled = true; // Disable input during update

            try {
                const formData = new FormData();
                formData.append('itemId', itemId);
                formData.append('quantity', newQuantity);
                formData.append('ajax', '1'); // Indicate AJAX request

                const response = await fetch('?page=cart_update', { // Assuming cart_update can handle AJAX
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json(); // Expect JSON response

                if (data.success) {
                    // Update subtotal for the item
                    const subtotalElement = document.getElementById(`item-subtotal-${itemId}`);
                    if (subtotalElement) {
                        subtotalElement.textContent = formatCurrency(data.itemSubtotal);
                    }
                    // Update total cart price
                    const totalElement = document.getElementById('cart-total-price');
                    if (totalElement) {
                        totalElement.textContent = formatCurrency(data.totalPrice);
                    }
                    // Update input max if stock check happened on server
                    if (data.newQuantity !== undefined && data.newQuantity !== newQuantity) {
                        inputElement.value = data.newQuantity; // Adjust if server corrected quantity
                        alert(data.message || 'Số lượng đã được điều chỉnh theo tồn kho.'); // Inform user
                    }

                } else {
                    alert('Lỗi cập nhật giỏ hàng: ' + (data.message || 'Vui lòng thử lại.'));
                    // Optional: Revert input value if update failed
                    // inputElement.value = data.oldQuantity || 1; // Need old quantity from server response
                }

            } catch (error) {
                console.error('Error updating cart:', error);
                alert('Đã xảy ra lỗi khi cập nhật giỏ hàng. Vui lòng kiểm tra kết nối và thử lại.');
                // Optional: Revert input value on error
            } finally {
                showSpinner(inputElement, false); // Hide spinner
                inputElement.disabled = false; // Re-enable input
            }
        }

        // Function to remove item via AJAX
        async function removeCartItem(buttonElement) {
            const itemId = buttonElement.dataset.itemId;
            const itemName = buttonElement.dataset.itemName;

            if (!confirm(`Bạn chắc chắn muốn xóa "${itemName}" khỏi giỏ hàng?`)) {
                return;
            }

            buttonElement.disabled = true; // Disable button
            buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>'; // Show spinner

            try {
                const response = await fetch(`?page=cart_remove&id=${itemId}&ajax=1`); // Send AJAX request

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json(); // Expect JSON

                if (data.success) {
                    // Remove the table row
                    const row = document.getElementById(`cart-item-row-${itemId}`);
                    if (row) {
                        row.remove();
                    }
                    // Update total cart price
                    const totalElement = document.getElementById('cart-total-price');
                    if (totalElement) {
                        totalElement.textContent = formatCurrency(data.totalPrice);
                    }
                    // Check if cart is now empty
                    if (data.itemCount === 0) {
                        // Optionally reload the page or display the empty cart message dynamically
                        window.location.reload(); // Simple reload for now
                    }
                } else {
                    alert('Lỗi xóa sản phẩm: ' + (data.message || 'Vui lòng thử lại.'));
                    buttonElement.disabled = false; // Re-enable button
                    buttonElement.innerHTML = '<i class="fas fa-trash-alt"></i>'; // Restore icon
                }

            } catch (error) {
                console.error('Error removing item:', error);
                alert('Đã xảy ra lỗi khi xóa sản phẩm. Vui lòng kiểm tra kết nối và thử lại.');
                buttonElement.disabled = false; // Re-enable button
                buttonElement.innerHTML = '<i class="fas fa-trash-alt"></i>'; // Restore icon
            }
        }

    </script>

<?php
include_once __DIR__ . '/../layout/footer.php'; // Include footer
?>