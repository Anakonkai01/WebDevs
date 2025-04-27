// Function to format currency
function formatCurrency(amount) {
    const numAmount = Number(amount);
    if (isNaN(numAmount)) {
        return '0₫';
    }
    // Format to VND
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
    }).format(numAmount).replace(/\s/g, '');
}

document.addEventListener('DOMContentLoaded', function () {
    // Get references to key elements on the page
    const cartTable = document.getElementById('cart-table');
    const selectAllCheckbox = document.getElementById('select-all-items');
    const selectedTotalPriceElement = document.getElementById('selected-total-price');
    const selectedItemsCountElement = document.getElementById('selected-items-count');
    const checkoutButton = document.getElementById('checkout-button'); // Get checkout button element
    const checkoutButtonCountElement = document.getElementById('checkout-button-count');// Get checkout button count element

    // Function to update total price of selected items
    function updateSelectedTotal() {
        let total = 0; let selectedCount = 0; const selectedIds = [];
         const itemCheckboxes = cartTable?.querySelectorAll('tbody .cart-item-select');

         if (!itemCheckboxes || itemCheckboxes.length === 0) {
            if (selectedTotalPriceElement) selectedTotalPriceElement.textContent = formatCurrency(0);
            if (selectedItemsCountElement) selectedItemsCountElement.textContent = 0;
            if (checkoutButtonCountElement) checkoutButtonCountElement.textContent = 0;
            if (checkoutButton) { checkoutButton.href = '#'; checkoutButton.classList.add('disabled'); checkoutButton.setAttribute('aria-disabled', 'true'); }
            if (selectAllCheckbox) { selectAllCheckbox.checked = false; selectAllCheckbox.indeterminate = false; selectAllCheckbox.disabled = true; }
            return; // Exit early if no items
         }
        // Loop to each cart
         itemCheckboxes.forEach(checkbox => {
            const row = checkbox.closest('tr'); if (!row) return;
            const price = parseFloat(row.dataset.itemPrice || 0); // Get price from attribute data
            const quantityDisplay = row.querySelector('.quantity-display');
            const quantity = parseInt(quantityDisplay?.value || 0); // Use the displayed quantity
            const itemId = row.dataset.itemId;

            if (checkbox.checked && itemId) {
                // Add to total and selected IDs if checked
                total += price * quantity;
                selectedCount++;
                selectedIds.push(itemId);
                // Remove deselected class if it exists
                row.classList.remove('item-deselected');
            } else {
                row.classList.add('item-deselected');
            }
         });
        // Display for total price
         if (selectedTotalPriceElement) selectedTotalPriceElement.textContent = formatCurrency(total);
         if (selectedItemsCountElement) selectedItemsCountElement.textContent = selectedCount;
        // Display for count
         if (checkoutButtonCountElement) checkoutButtonCountElement.textContent = selectedCount;
         // Update checkout button's state and URL
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
        // Update "Select All" checkbox state
         if (selectAllCheckbox) {
            const totalItems = itemCheckboxes.length; selectAllCheckbox.disabled = false;
            selectAllCheckbox.checked = selectedCount === totalItems && totalItems > 0;
            selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < totalItems;
         }
    }// End updateSelectedTotal function
    // Function to handle quantity change
    async function handleQuantityChange(buttonElement, delta) {
        // Get references to related elements
        const selectorDiv = buttonElement.closest('.quantity-selector');
        if (!selectorDiv) return;
        // Get references to input and button element
        const displayInput = selectorDiv.querySelector('.quantity-display');
        const decreaseBtn = selectorDiv.querySelector('.quantity-decrease');
        const increaseBtn = selectorDiv.querySelector('.quantity-increase');
        const row = selectorDiv.closest('tr');
        const spinner = row.querySelector('.updating-spinner');
        const itemId = selectorDiv.dataset.itemId;

        // Validate existence of key elements
        if (!displayInput || !itemId || !row || !decreaseBtn || !increaseBtn) {
            console.error("Cart Quantity update: Missing required elements."); return;

        // Get current quantity and calculate new quantity
        const currentQuantity = parseInt(displayInput.value, 10);
        let newQuantity = currentQuantity + delta;
        // Get stock from data attributes
        const stockLimit = parseInt(row.dataset.itemStock || 999);
        const minLimit = 1;

        // Clamp quantity to min and max limits
        newQuantity = Math.max(minLimit, Math.min(newQuantity, stockLimit));

        // Only proceed if the quantity will actually change
        if (newQuantity === currentQuantity) {
            console.warn("Quantity limit reached or no change required.");
            // Ensure buttons reflect the state correctly even if no AJAX call is made
            decreaseBtn.disabled = (currentQuantity <= minLimit);
            increaseBtn.disabled = (currentQuantity >= stockLimit);
            return;
        }
        // --- Update state of buttons and display loading ---
        decreaseBtn.disabled = true;
        increaseBtn.disabled = true;
        if (spinner) spinner.style.display = 'inline-block';
        // Send request to update quantity
        try {
            const formData = new FormData();
            // Append data to send to server
            formData.append('itemId', itemId);
            formData.append('quantity', newQuantity); // Send the *intended* new quantity
            formData.append('ajax', '1');

            const response = await fetch('?page=cart_update', { method: 'POST', body: formData });

            let data;
            try {
                data = await response.json();
            } catch (e) {
                const text = await response.text();
                console.error("Cart JSON parse error:", text);
                throw new Error("Phản hồi từ máy chủ không hợp lệ.");
            }

            if (!response.ok) {
                throw new Error(data.message || `Lỗi HTTP ${response.status}`);
            }
            // Update if success
            if (data.success) {
                // Use the quantity returned by the server (may have been adjusted due to stock)
                const finalQuantity = data.newQuantity !== null ? parseInt(data.newQuantity) : newQuantity;
                // Update value of display input
                displayInput.value = finalQuantity;
                // Update data attributes of row
                row.dataset.itemQuantity = finalQuantity;

                const subtotalElement = row.querySelector('.item-subtotal');
                if (subtotalElement) {
                    subtotalElement.textContent = formatCurrency(data.itemSubtotal);
                } // Update subtotal
                // Update selected total
                updateSelectedTotal();
                // Update the button states based on final quantity
                decreaseBtn.disabled = (finalQuantity <= minLimit);
                increaseBtn.disabled = (finalQuantity >= stockLimit);
                // Display warning message if quantity was adjusted
                if (data.newQuantity !== null && data.newQuantity != newQuantity) {
                    console.warn(data.message || `Số lượng được cập nhật thành ${finalQuantity}.`);
                }
            } else {
                // Show error message
                alert('Lỗi cập nhật: ' + (data.message || 'Vui lòng thử lại.'));
                // Revert to previous state
                displayInput.value = currentQuantity;
                decreaseBtn.disabled = (currentQuantity <= minLimit);
                increaseBtn.disabled = (currentQuantity >= stockLimit);
            }
        } catch (error) {
            // Log error
            console.error('Error updating cart quantity:', error);
            alert('Lỗi: ' + error.message);
            // Revert to previous state
            displayInput.value = currentQuantity;
            decreaseBtn.disabled = (currentQuantity <= minLimit);
            increaseBtn.disabled = (currentQuantity >= stockLimit);
        } finally {
            // Hide spinner
            if (spinner) spinner.style.display = 'none';
        }
    }
    // Function to remove item
    async function removeCartItem(buttonElement) {
         const row = buttonElement.closest('tr'); if (!row) return; // Check if row exist
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
    // Function to check if cart empty
    function checkEmptyCart() {
          if (cartTable && cartTable.querySelectorAll('tbody tr').length === 0) {
             const tableResponsiveDiv = document.querySelector('.table-responsive');
             const summaryBox = document.querySelector('.row.justify-content-end.mt-4');
             if (tableResponsiveDiv) { tableResponsiveDiv.innerHTML = `<div class="alert alert-info text-center" role="alert"><p class="mb-3 fs-5">Giỏ hàng của bạn hiện đang trống.</p><a href="?page=shop_grid" class="btn btn-primary"><i class="fas fa-shopping-bag me-2"></i>Bắt đầu mua sắm</a></div>`; }
             if (summaryBox) summaryBox.style.display = 'none';
             if (selectAllCheckbox) { selectAllCheckbox.checked = false; selectAllCheckbox.indeterminate = false; selectAllCheckbox.disabled = true; }
         }
     }
    // Add event listener when the cart is loaded
    if (cartTable) {
        // Listener for +/- buttons and remove button using event delegation
        cartTable.addEventListener('click', function(event) {
            const target = event.target;
            // Check for closest button to handle clicks on icons inside buttons.
            // It helps in case user click in icon
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

        // Listener for checkbox changes
         cartTable.addEventListener('change', function (event) {
             if (event.target.type === 'checkbox') {
                 if (event.target.id === 'select-all-items') {
                     const itemCheckboxes = cartTable.querySelectorAll('tbody .cart-item-select');
                     itemCheckboxes.forEach(checkbox => checkbox.checked = event.target.checked);
                 }
                 updateSelectedTotal();
             }
         });
    }

    // Initial set up
    updateSelectedTotal();
    checkEmptyCart();

}); // End DOMContentLoaded

// Add CSS for deselected items
const style = document.createElement('style');
style.textContent = `
    #cart-table tbody tr.item-deselected { opacity: 0.6; background-color: #f8f9fa; transition: opacity 0.3s ease, background-color 0.3s ease; } // Add class deselected
    #cart-table tbody tr { transition: opacity 0.3s ease, background-color 0.3s ease; } // Add tranistion to cart
`;
document.head.appendChild(style);