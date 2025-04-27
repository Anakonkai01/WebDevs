// Check if the script has been loaded before
if (typeof window.productDetailScriptLoaded === 'undefined') {
    window.productDetailScriptLoaded = true;

    // Wait for DOM to load
    document.addEventListener('DOMContentLoaded', function() {
        console.log("product_detail.js loaded and initialized.");

        // Get HTML element references
        const addToCartForm = document.getElementById('add-to-cart-form');
        const quantitySelector = document.querySelector('.quantity-selector');
        // get hidden input for quantity
        const formInputForQuantity = addToCartForm ? addToCartForm.querySelector('input[name="quantity"][type="hidden"]') : null;
        const displayInput = quantitySelector ? quantitySelector.querySelector('.quantity-display') : null;
        const decreaseBtn = quantitySelector ? quantitySelector.querySelector('.quantity-decrease') : null;
        const increaseBtn = quantitySelector ? quantitySelector.querySelector('.quantity-increase') : null;
        const addToCartButton = document.getElementById('add-to-cart-btn');
        const messageDiv = document.getElementById('add-to-cart-message');
        
        // get element inside button add to cart
        const buttonText = addToCartButton?.querySelector('.button-text');
        const buttonSpinner = addToCartButton?.querySelector('.spinner-border');
        

        // --- Quantity Controls Logic ---
        if (quantitySelector && displayInput && decreaseBtn && increaseBtn && formInputForQuantity) {
            // console.log("Quantity elements found");

            const minQty = parseInt(displayInput.min, 10) || 1;
            let maxQty = parseInt(displayInput.max, 10);
            if (isNaN(maxQty) || maxQty <= 0) { // Treat invalid or non-positive max as Infinity
               maxQty = Infinity;
            }
           
           
             //function update state of the button

             
            const updateButtons = (currentValue) => {
                decreaseBtn.disabled = (currentValue <= minQty);
                increaseBtn.disabled = (currentValue >= maxQty);
             };

            decreaseBtn.addEventListener('click', () => {
                let currentValue = parseInt(displayInput.value, 10);
                if (isNaN(currentValue)) currentValue = minQty;
                if (currentValue > minQty) {
                    currentValue--;
                    displayInput.value = currentValue;
                    formInputForQuantity.value = currentValue; 
                    updateButtons(currentValue);
                }
            });

            increaseBtn.addEventListener('click', () => {
                let currentValue = parseInt(displayInput.value, 10);
                if (isNaN(currentValue)) currentValue = minQty;
                if (currentValue < maxQty) {
                    currentValue++;
                    displayInput.value = currentValue; 
                    formInputForQuantity.value = currentValue; // Update hidden input
                    updateButtons(currentValue);
                }
            });

            // Initial button state check
            let initialValue = parseInt(displayInput.value, 10);
            if(isNaN(initialValue) || initialValue < minQty) initialValue = minQty; 
            if(initialValue > maxQty) initialValue = maxQty; 
            displayInput.value = initialValue;
            formInputForQuantity.value = initialValue; 
            updateButtons(initialValue);

        } else {
            console.warn("Quantity control initialization failed. Check if elements exist and the form has the hidden quantity input.");
        }

        // Add to cart via ajax
        if (addToCartForm && addToCartButton && formInputForQuantity) {
            addToCartForm.addEventListener('submit', async function(event) {
                // prevent default form
                event.preventDefault();

                // loading when user click
                if (buttonText) buttonText.textContent = 'Loading...';
                if (buttonSpinner) buttonSpinner.classList.remove('d-none'); // show spinner
                addToCartButton.disabled = true;
                if (messageDiv) messageDiv.style.display = 'none';

                const formData = new FormData(addToCartForm);
                // Ensure quantity in FormData is the current value from hidden input
                formData.set('quantity', formInputForQuantity.value);
                // console.log("Submitting quantity:", formData.get('quantity'));

                try {
                    const response = await fetch(addToCartForm.action, {
                        method: 'POST', body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });

                    let data;
                    const contentType = response.headers.get("content-type");
                    if (!contentType || !contentType.includes("application/json")) {
                        const text = await response.text();
                        console.error("Invalid non-JSON response:", text, `Status: ${response.status}`);
                        throw new Error(`Invalid response from server (Code: ${response.status}).`);
                    }
                    try {
                        data = await response.json();
                    } catch (e) { // Catch error JSON
                        console.error("JSON Parse Error:", e);
                        throw new Error(`Lỗi đọc phản hồi từ máy chủ (Code: ${response.status}).`);
                    }

                    if (!response.ok) {
                        throw new Error(data?.message || `Lỗi máy chủ (Code: ${response.status})`);
                    }

                    // handle success
                    if (messageDiv) {
                        messageDiv.textContent = data.message || (data.success ? 'Added to cart successfully!' : 'An error occurred.');
                        messageDiv.className = `alert small mt-3 mb-0 alert-${data.success ? 'success' : 'warning'}`;
                        messageDiv.style.display = 'block';
                        setTimeout(() => { if (messageDiv) messageDiv.style.display = 'none'; }, 5000);
                    }
                    if (data.success && typeof data.cartItemCount !== 'undefined') {
                        const cartCountElement = document.getElementById('header-cart-count');
                        if (cartCountElement) {
                            const newCount = parseInt(data.cartItemCount, 10);
                            cartCountElement.textContent = newCount;
                            cartCountElement.style.display = newCount > 0 ? '' : 'none';
                        }
                    }

                } catch (error) {
                    console.error('Error adding to cart:', error);
                    const errorMessage = error.message || 'Connection or processing error. Please try again.';
                    if (messageDiv) {
                        messageDiv.textContent = errorMessage;
                        messageDiv.className = 'alert alert-danger small mt-3 mb-0';
                        messageDiv.style.display = 'block';
                    } else {
                       alert(errorMessage);
                     }
                } finally {
                    if (buttonText) buttonText.textContent = 'Thêm vào giỏ';
                    if (buttonSpinner) buttonSpinner.classList.add('d-none');
                    if (addToCartButton) addToCartButton.disabled = false;
                }
            });
        } else {
             console.warn("Add to cart functionality cannot be initialized. Missing form, button, or hidden quantity input.");
             if(!addToCartForm) console.warn("Reason: #add-to-cart-form not found.");
             if(!addToCartButton) console.warn("Reason: #add-to-cart-btn not found.");
             if(!formInputForQuantity) console.warn("Reason: Hidden input[name='quantity'] within the form not found.");
        }

        // handle star rating
        const ratingStarsContainer = document.querySelector('.rating-stars');
        const ratingValueInput = document.getElementById('rating'); 
        if (ratingStarsContainer && ratingValueInput) {
            const starRadioInputs = ratingStarsContainer.querySelectorAll('input[type="radio"][name="rating_radio"]');
            starRadioInputs.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        ratingValueInput.value = this.value; 
                    }
                });
            });
            // Initialize based on pre-checked value (if any)
            const initiallyChecked = ratingStarsContainer.querySelector('input[type="radio"][name="rating_radio"]:checked');
            if (initiallyChecked) {
                ratingValueInput.value = initiallyChecked.value;
            }else ratingValueInput.value = ""; 
            
        } else {
            console.warn("Star rating functionality cannot be initialized. Check .rating-stars container and #rating hidden input.");
        }
        
        // handle click change image
         const mainImage = document.getElementById('main-product-image');
        const thumbnailLinks = document.querySelectorAll('.product-thumbnails a'); // Ensure thumbnails exist with this structure
        if (mainImage && thumbnailLinks.length > 0) {
            thumbnailLinks.forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    const newImageSrc = this.dataset.imageSrc; // Expecting <a data-image-src="...">
                    if (newImageSrc && mainImage.src !== newImageSrc) { // change image
                        mainImage.src = newImageSrc;
                        thumbnailLinks.forEach(l => l.classList.remove('active'));
                        this.classList.add('active');
                    }
                });
            });
        }
    }); 

} else {
    console.log("product_detail.js already loaded, skipping initialization.");
}