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
                // Listener cho nút +/-\
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