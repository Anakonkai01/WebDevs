<?php
// Web/app/Views/cart.php
$cartItems = $cartItems ?? [];
$totalPrice = $totalPrice ?? 0; // Tổng tiền ban đầu của tất cả item
$flashMessage = $flashMessage ?? null; // Flash message from controller
include_once __DIR__ . '/../layout/header.php'; // Include header
?>

    <style>
        /* === CSS CẢI TIẾN GIAO DIỆN === */
        .quantity-selector {
            display: inline-flex;
            align-items: center;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            overflow: hidden;
        }
        .quantity-selector .btn {
            border-radius: 0;
            border: none;
            padding: 0.3rem 0.6rem;
            font-size: 0.9rem;
            line-height: 1.5;
            min-width: 30px;
            background-color: #f8f9fa;
            color: #495057;
            transition: background-color 0.15s ease-in-out;
        }
        .quantity-selector .btn:hover:not(:disabled) {
            background-color: #e2e6ea;
        }
        .quantity-selector .btn:focus {
            box-shadow: none;
        }
        .quantity-selector .btn:disabled {
            background-color: #e9ecef;
            opacity: 0.65;
            cursor: not-allowed;
        }
        .quantity-selector .quantity-display {
            border: none;
            border-radius: 0;
            padding: 0.3rem 0.2rem;
            font-size: 0.9rem;
            text-align: center;
            max-width: 40px;
            height: calc(1.5em + 0.6rem + 2px);
            box-shadow: none !important;
            background-color: #fff !important;
            cursor: default;
            -moz-appearance: textfield;
        }
        .quantity-selector .quantity-display::-webkit-outer-spin-button,
        .quantity-selector .quantity-display::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .quantity-selector .updating-spinner {
            /* Spinner vẫn tồn tại trong HTML nhưng không bao giờ được hiển thị bởi JS */
            margin-left: 5px;
            width: 1rem;
            height: 1rem;
            align-self: center;
            display: none; /* Luôn ẩn */
        }

        /* Các style khác giữ nguyên */
        .cart-summary-box { background-color: #f8f9fa; border: 1px solid #dee2e6; position: sticky; top: 80px; }
        .cart-select-checkbox { width: 1.5em; height: 1.5em; vertical-align: middle; cursor: pointer; }
        th.select-col, td.select-col { width: 40px; text-align: center; vertical-align: middle; }
        .cart-item-img { width: 80px; height: 80px; object-fit: contain; }
        tr[data-item-id] { transition: opacity 0.3s ease-in-out; }
        .table td, .table th { vertical-align: middle; }
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

    <script>
        // Hàm định dạng tiền tệ
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount).replace(/\s/g, '');
        }

        // Lấy các phần tử DOM
        const cartTable = document.getElementById('cart-table');
        const selectAllCheckbox = document.getElementById('select-all-items');
        const selectedTotalPriceElement = document.getElementById('selected-total-price');
        const selectedItemsCountElement = document.getElementById('selected-items-count');
        const checkoutButton = document.getElementById('checkout-button');
        const checkoutButtonCountElement = document.getElementById('checkout-button-count');

        // Hàm cập nhật tổng tiền
        function updateSelectedTotal() {
            let total = 0;
            let selectedCount = 0;
            const selectedIds = [];
            const itemCheckboxes = cartTable ? cartTable.querySelectorAll('.cart-item-select') : [];

            itemCheckboxes.forEach(checkbox => {
                const row = checkbox.closest('tr');
                if (!row) return;
                const price = parseFloat(row.dataset.itemPrice || 0);
                const quantity = parseInt(row.dataset.itemQuantity || 0);
                const itemId = row.dataset.itemId;

                if (checkbox.checked) {
                    total += price * quantity;
                    selectedCount++;
                    if (itemId) {
                        selectedIds.push(itemId);
                    }
                    row.style.opacity = 1;
                } else {
                    row.style.opacity = 0.6;
                }
            });

            if (selectedTotalPriceElement) selectedTotalPriceElement.textContent = formatCurrency(total);
            if (selectedItemsCountElement) selectedItemsCountElement.textContent = selectedCount;
            if (checkoutButtonCountElement) checkoutButtonCountElement.textContent = selectedCount;

            if (checkoutButton) {
                if (selectedCount > 0) {
                    checkoutButton.href = `?page=checkout&selected_ids=${selectedIds.join(',')}`;
                    checkoutButton.classList.remove('disabled');
                    checkoutButton.removeAttribute('aria-disabled');
                } else {
                    checkoutButton.href = '#';
                    checkoutButton.classList.add('disabled');
                    checkoutButton.setAttribute('aria-disabled', 'true');
                }
            }

            if (selectAllCheckbox) {
                const totalItems = itemCheckboxes.length;
                selectAllCheckbox.checked = selectedCount === totalItems && totalItems > 0;
                selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < totalItems;
            }
        }

        // Hàm xử lý thay đổi số lượng
        async function handleQuantityChange(buttonElement, delta) {
            const selectorDiv = buttonElement.closest('.quantity-selector');
            if (!selectorDiv) return;

            const displayInput = selectorDiv.querySelector('.quantity-display');
            const decreaseBtn = selectorDiv.querySelector('.quantity-decrease');
            const increaseBtn = selectorDiv.querySelector('.quantity-increase');
            const row = selectorDiv.closest('tr');
            // const spinner = row.querySelector('.updating-spinner'); // Lấy spinner từ TR (ĐÃ LOẠI BỎ LOGIC)
            const itemId = selectorDiv.dataset.itemId;

            if (!displayInput || !itemId || !row) {
                console.error("Quantity update: Missing required elements.");
                return;
            }

            const currentQuantity = parseInt(displayInput.value);
            let newQuantity = currentQuantity + delta;
            const stockLimit = parseInt(row.dataset.itemStock || 999);

            if (newQuantity < 1) newQuantity = 1;
            if (newQuantity > stockLimit) newQuantity = stockLimit;

            if (newQuantity === currentQuantity && delta !== 0) {
                if (delta > 0) alert(`Số lượng tối đa cho sản phẩm này là ${stockLimit}.`);
                if (increaseBtn) increaseBtn.disabled = true; // Đảm bảo nút tăng disable đúng
                return;
            }

            // Vô hiệu hóa nút (KHÔNG CẦN HIỂN THỊ SPINNER)
            if (decreaseBtn) decreaseBtn.disabled = true;
            if (increaseBtn) increaseBtn.disabled = true;
            // if (spinner) spinner.style.display = 'inline-block'; // <<< DÒNG NÀY ĐÃ BỊ XÓA/COMMENT

            try {
                const formData = new FormData();
                formData.append('itemId', itemId);
                formData.append('quantity', newQuantity);
                formData.append('ajax', '1');

                const response = await fetch('?page=cart_update', { method: 'POST', body: formData });
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();

                if (data.success) {
                    const finalQuantity = data.newQuantity !== null ? parseInt(data.newQuantity) : newQuantity;
                    displayInput.value = finalQuantity;
                    row.dataset.itemQuantity = finalQuantity;

                    const subtotalElement = row.querySelector('.item-subtotal');
                    if (subtotalElement) subtotalElement.textContent = formatCurrency(data.itemSubtotal);

                    updateSelectedTotal();

                    if (data.newQuantity !== null && data.newQuantity != newQuantity) {
                        alert(data.message || `Số lượng được cập nhật thành ${finalQuantity} do giới hạn tồn kho.`);
                    }
                } else {
                    alert('Lỗi cập nhật: ' + (data.message || 'Vui lòng thử lại.'));
                    displayInput.value = row.dataset.itemQuantity;
                }
            } catch (error) {
                console.error('Error updating cart quantity:', error);
                alert('Lỗi kết nối hoặc xử lý. Vui lòng thử lại.');
                displayInput.value = row.dataset.itemQuantity;
            } finally {
                // Kích hoạt lại nút và kiểm tra giới hạn
                const finalQuantity = parseInt(displayInput.value);
                if (decreaseBtn) decreaseBtn.disabled = (finalQuantity <= 1);
                if (increaseBtn) increaseBtn.disabled = (finalQuantity >= stockLimit);
                // if (spinner) spinner.style.display = 'none'; // <<< DÒNG NÀY ĐÃ BỊ XÓA/COMMENT
            }
        }

        // Hàm xóa sản phẩm
        async function removeCartItem(buttonElement) {
            const row = buttonElement.closest('tr');
            if(!row) return;
            const itemId = row.dataset.itemId;
            const itemName = buttonElement.dataset.itemName;

            if (!confirm(`Bạn chắc chắn muốn xóa "${itemName}" khỏi giỏ hàng?`)) return;

            buttonElement.disabled = true;
            // Không cần hiển thị spinner khi xóa, hoặc có thể thay bằng icon khác
            buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; // Ví dụ: thay bằng spinner

            try {
                const response = await fetch(`?page=cart_remove&id=${itemId}&ajax=1`);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();

                if (data.success) {
                    row.remove();
                    updateSelectedTotal();
                    if (cartTable && cartTable.querySelectorAll('tbody tr').length === 0) {
                        document.querySelector('.table-responsive').innerHTML = `<div class="alert alert-info text-center" role="alert"><p class="mb-3 fs-5">Giỏ hàng của bạn hiện đang trống.</p><a href="?page=shop_grid" class="btn btn-primary"><i class="fas fa-shopping-bag me-2"></i>Bắt đầu mua sắm</a></div>`;
                        const summaryBox = document.querySelector('.row.justify-content-end.mt-4');
                        if (summaryBox) summaryBox.style.display = 'none';
                        if(selectAllCheckbox) selectAllCheckbox.disabled = true;
                    }
                } else {
                    alert('Lỗi xóa: ' + (data.message || 'Vui lòng thử lại.'));
                    buttonElement.disabled = false;
                    buttonElement.innerHTML = '<i class="fas fa-trash-alt"></i>'; // Khôi phục icon gốc
                }
            } catch (error) {
                console.error('Error removing item:', error);
                alert('Lỗi kết nối hoặc xử lý khi xóa. Vui lòng thử lại.');
                buttonElement.disabled = false;
                buttonElement.innerHTML = '<i class="fas fa-trash-alt"></i>'; // Khôi phục icon gốc
            }
        }

        // Gắn Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            if (cartTable) {
                // Listener cho nút +/-
                cartTable.addEventListener('click', function(event) {
                    const target = event.target;
                    if (target.classList.contains('quantity-decrease')) {
                        handleQuantityChange(target, -1);
                    } else if (target.classList.contains('quantity-increase')) {
                        handleQuantityChange(target, 1);
                    }
                });

                // Listener cho checkbox "Chọn tất cả"
                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', function() {
                        const itemCheckboxes = cartTable.querySelectorAll('.cart-item-select');
                        itemCheckboxes.forEach(checkbox => checkbox.checked = selectAllCheckbox.checked);
                        updateSelectedTotal();
                    });
                }

                // Listener cho từng checkbox sản phẩm
                cartTable.addEventListener('change', function(event) {
                    if (event.target.classList.contains('cart-item-select')) {
                        updateSelectedTotal();
                    }
                });
            }

            // Gọi lần đầu để tính toán và cập nhật
            if (cartTable && cartTable.querySelector('tbody tr')) {
                updateSelectedTotal();
            } else if (checkoutButton){
                checkoutButton.classList.add('disabled');
                checkoutButton.setAttribute('aria-disabled', 'true');
                if (checkoutButtonCountElement) checkoutButtonCountElement.textContent = 0;
                if (selectedItemsCountElement) selectedItemsCountElement.textContent = 0;
                if (selectedTotalPriceElement) selectedTotalPriceElement.textContent = formatCurrency(0);
                if (selectAllCheckbox) selectAllCheckbox.disabled = true;
            }
        });

    </script>

<?php
include_once __DIR__ . '/../layout/footer.php'; // Include footer
?>