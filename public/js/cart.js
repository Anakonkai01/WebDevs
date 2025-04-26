/**
 * cart.js
 * Handles interactions on the shopping cart page, including item selection,
 * quantity updates (AJAX), item removal (AJAX), and updating totals.
 */

// Hàm định dạng tiền tệ (Giữ nguyên)
function formatCurrency(amount) {
    // ... (code formatCurrency giữ nguyên) ...
     const numAmount = Number(amount);
    if (isNaN(numAmount)) { return '0₫'; }
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(numAmount).replace(/\s/g, '');
}

document.addEventListener('DOMContentLoaded', function() {
    const cartTable = document.getElementById('cart-table');
    const selectAllCheckbox = document.getElementById('select-all-items');
    const selectedTotalPriceElement = document.getElementById('selected-total-price');
    const selectedItemsCountElement = document.getElementById('selected-items-count');
    const checkoutButton = document.getElementById('checkout-button');
    const checkoutButtonCountElement = document.getElementById('checkout-button-count');

    // Hàm cập nhật tổng tiền và nút checkout (Giữ nguyên)
    function updateSelectedTotal() {
        // ... (code updateSelectedTotal giữ nguyên) ...
         let total = 0; let selectedCount = 0; const selectedIds = [];
         const itemCheckboxes = cartTable?.querySelectorAll('tbody .cart-item-select');

         if (!itemCheckboxes || itemCheckboxes.length === 0) {
            if (selectedTotalPriceElement) selectedTotalPriceElement.textContent = formatCurrency(0);
            if (selectedItemsCountElement) selectedItemsCountElement.textContent = 0;
            if (checkoutButtonCountElement) checkoutButtonCountElement.textContent = 0;
            if (checkoutButton) { checkoutButton.href = '#'; checkoutButton.classList.add('disabled'); checkoutButton.setAttribute('aria-disabled', 'true'); }
            if (selectAllCheckbox) { selectAllCheckbox.checked = false; selectAllCheckbox.indeterminate = false; selectAllCheckbox.disabled = true; }
            return;
         }

         itemCheckboxes.forEach(checkbox => {
            const row = checkbox.closest('tr'); if (!row) return;
            const price = parseFloat(row.dataset.itemPrice || 0);
            const quantityDisplay = row.querySelector('.quantity-display');
            const quantity = parseInt(quantityDisplay?.value || 0); // Use display input value
            const itemId = row.dataset.itemId;

            if (checkbox.checked && itemId) {
                total += price * quantity; selectedCount++; selectedIds.push(itemId);
                row.classList.remove('item-deselected');
            } else { row.classList.add('item-deselected'); }
         });

         if (selectedTotalPriceElement) selectedTotalPriceElement.textContent = formatCurrency(total);
         if (selectedItemsCountElement) selectedItemsCountElement.textContent = selectedCount;
         if (checkoutButtonCountElement) checkoutButtonCountElement.textContent = selectedCount;
         if (checkoutButton) {
            if (selectedCount > 0) { checkoutButton.href = `?page=checkout&selected_ids=${selectedIds.join(',')}`; checkoutButton.classList.remove('disabled'); checkoutButton.removeAttribute('aria-disabled'); }
            else { checkoutButton.href = '#'; checkoutButton.classList.add('disabled'); checkoutButton.setAttribute('aria-disabled', 'true'); }
         }
         if (selectAllCheckbox) {
            const totalItems = itemCheckboxes.length; selectAllCheckbox.disabled = false;
            selectAllCheckbox.checked = selectedCount === totalItems && totalItems > 0;
            selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < totalItems;
         }
    }

    // Hàm xử lý thay đổi số lượng (AJAX) - Đã kiểm tra lại logic
    async function handleQuantityChange(buttonElement, delta) {
        const selectorDiv = buttonElement.closest('.quantity-selector');
        if (!selectorDiv) return;

        const displayInput = selectorDiv.querySelector('.quantity-display'); // Input hiển thị
        const decreaseBtn = selectorDiv.querySelector('.quantity-decrease');
        const increaseBtn = selectorDiv.querySelector('.quantity-increase');
        const row = selectorDiv.closest('tr');
        const spinner = row.querySelector('.updating-spinner');
        const itemId = selectorDiv.dataset.itemId;

        if (!displayInput || !itemId || !row || !decreaseBtn || !increaseBtn) {
            console.error("Cart Quantity update: Missing required elements."); return;
        }

        const currentQuantity = parseInt(displayInput.value, 10);
        let newQuantity = currentQuantity + delta;
        const stockLimit = parseInt(row.dataset.itemStock || 999); // Lấy stock từ data attribute
        const minLimit = 1;

        // Clamp quantity immediately
        newQuantity = Math.max(minLimit, Math.min(newQuantity, stockLimit));

        // Only proceed if the quantity will actually change
        if (newQuantity === currentQuantity) {
            console.warn("Quantity limit reached or no change required.");
            // Ensure buttons reflect the state correctly even if no AJAX call is made
            decreaseBtn.disabled = (currentQuantity <= minLimit);
            increaseBtn.disabled = (currentQuantity >= stockLimit);
            return;
        }

        // --- Show Loading State ---
        decreaseBtn.disabled = true;
        increaseBtn.disabled = true;
        if (spinner) spinner.style.display = 'inline-block';

        try {
            const formData = new FormData();
            formData.append('itemId', itemId);
            formData.append('quantity', newQuantity); // Send the *intended* new quantity
            formData.append('ajax', '1');

            const response = await fetch('?page=cart_update', { method: 'POST', body: formData });

            let data;
            try { data = await response.json(); }
            catch (e) { const text = await response.text(); console.error("Cart JSON parse error:", text); throw new Error("Phản hồi từ máy chủ không hợp lệ."); }

            if (!response.ok) throw new Error(data.message || `Lỗi HTTP ${response.status}`);

            if (data.success) {
                // Use the quantity returned by the server (could be adjusted due to stock)
                const finalQuantity = data.newQuantity !== null ? parseInt(data.newQuantity) : newQuantity;

                displayInput.value = finalQuantity;
                row.dataset.itemQuantity = finalQuantity; // Update row data for total calculation

                const subtotalElement = row.querySelector('.item-subtotal');
                if (subtotalElement) subtotalElement.textContent = formatCurrency(data.itemSubtotal);

                updateSelectedTotal();

                // Update button states based on the FINAL quantity
                decreaseBtn.disabled = (finalQuantity <= minLimit);
                increaseBtn.disabled = (finalQuantity >= stockLimit);

                if (data.newQuantity !== null && data.newQuantity != newQuantity) {
                    console.warn(data.message || `Số lượng được cập nhật thành ${finalQuantity}.`);
                }
            } else {
                alert('Lỗi cập nhật: ' + (data.message || 'Vui lòng thử lại.'));
                // Revert display to original quantity if update failed
                displayInput.value = currentQuantity;
                decreaseBtn.disabled = (currentQuantity <= minLimit);
                increaseBtn.disabled = (currentQuantity >= stockLimit);
            }
        } catch (error) {
            console.error('Error updating cart quantity:', error);
            alert('Lỗi: ' + error.message);
            // Revert display and buttons on fetch error
            displayInput.value = currentQuantity;
            decreaseBtn.disabled = (currentQuantity <= minLimit);
            increaseBtn.disabled = (currentQuantity >= stockLimit);
        } finally {
            if (spinner) spinner.style.display = 'none';
            // Buttons are re-enabled/disabled based on the final quantity inside try/catch
        }
    }

    // Hàm xóa sản phẩm (AJAX) (Giữ nguyên)
    async function removeCartItem(buttonElement) {
        // ... (code removeCartItem giữ nguyên) ...
         const row = buttonElement.closest('tr'); if (!row) return;
         const itemId = row.dataset.itemId;
         const itemName = buttonElement.dataset.itemName || 'sản phẩm';
         if (!confirm(`Bạn chắc chắn muốn xóa "${itemName}" khỏi giỏ hàng?`)) return;
         buttonElement.disabled = true;
         buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

         try {
            const response = await fetch(`?page=cart_remove&id=${itemId}&ajax=1`);
             let data;
             try { data = await response.json(); }
             catch (e) { const text = await response.text(); console.error("Remove JSON error:", text); throw new Error("Phản hồi từ máy chủ không hợp lệ khi xóa."); }
            if (!response.ok) throw new Error(data.message || `Lỗi HTTP ${response.status}`);
            if (data.success) {
                 row.style.transition = 'opacity 0.5s ease-out'; row.style.opacity = '0';
                 setTimeout(() => { row.remove(); updateSelectedTotal(); checkEmptyCart(); }, 500);
            } else {
                alert('Lỗi xóa: ' + (data.message || 'Vui lòng thử lại.'));
                 buttonElement.disabled = false; buttonElement.innerHTML = '<i class="fas fa-trash-alt"></i>';
            }
         } catch (error) {
            console.error('Error removing item:', error); alert('Lỗi: ' + error.message);
             buttonElement.disabled = false; buttonElement.innerHTML = '<i class="fas fa-trash-alt"></i>';
         }
    }

     // Hàm kiểm tra giỏ hàng rỗng (Giữ nguyên)
     function checkEmptyCart() {
         // ... (code checkEmptyCart giữ nguyên) ...
          if (cartTable && cartTable.querySelectorAll('tbody tr').length === 0) {
             const tableResponsiveDiv = document.querySelector('.table-responsive');
             const summaryBox = document.querySelector('.row.justify-content-end.mt-4');
             if (tableResponsiveDiv) { tableResponsiveDiv.innerHTML = `<div class="alert alert-info text-center" role="alert"><p class="mb-3 fs-5">Giỏ hàng của bạn hiện đang trống.</p><a href="?page=shop_grid" class="btn btn-primary"><i class="fas fa-shopping-bag me-2"></i>Bắt đầu mua sắm</a></div>`; }
             if (summaryBox) summaryBox.style.display = 'none';
             if (selectAllCheckbox) { selectAllCheckbox.checked = false; selectAllCheckbox.indeterminate = false; selectAllCheckbox.disabled = true; }
         }
     }

    // Gắn Event Listeners (Đã kiểm tra lại)
    if (cartTable) {
        // Listener for +/- buttons and remove button using Event Delegation
        cartTable.addEventListener('click', function(event) {
            const target = event.target;
            // Check for closest button to handle clicks on icons inside buttons
            const decreaseButton = target.closest('.quantity-decrease');
            const increaseButton = target.closest('.quantity-increase');
            const removeButton = target.closest('.remove-item-btn');

            if (decreaseButton) {
                handleQuantityChange(decreaseButton, -1);
            } else if (increaseButton) {
                handleQuantityChange(increaseButton, 1);
            } else if (removeButton) {
                 removeCartItem(removeButton);
            }
        });

        // Listener for checkbox changes (Giữ nguyên)
         cartTable.addEventListener('change', function(event) {
             if (event.target.type === 'checkbox') {
                 if (event.target.id === 'select-all-items') {
                     const itemCheckboxes = cartTable.querySelectorAll('tbody .cart-item-select');
                     itemCheckboxes.forEach(checkbox => checkbox.checked = event.target.checked);
                 }
                 updateSelectedTotal();
             }
         });
    }

    // Initial setup
    updateSelectedTotal();
    checkEmptyCart();

}); // End DOMContentLoaded

// Optional: Add CSS for deselected items (Giữ nguyên)
const style = document.createElement('style');
style.textContent = `
    #cart-table tbody tr.item-deselected { opacity: 0.6; background-color: #f8f9fa; transition: opacity 0.3s ease, background-color 0.3s ease; }
    #cart-table tbody tr { transition: opacity 0.3s ease, background-color 0.3s ease; }
`;
document.head.appendChild(style);