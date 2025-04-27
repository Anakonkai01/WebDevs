<?php

?>

    </main> <?php // Close main tag ?>

    <?php // Footer Section ?>
    <footer class="site-footer mt-auto bg-dark text-white pt-5 pb-4">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-3 fw-semibold text-uppercase small">Về MyShop</h5>
                    <p class="small text-white-50">
                        MyShop là cửa hàng cung cấp các sản phẩm công nghệ chính hãng với giá tốt nhất, đảm bảo chất lượng và dịch vụ hậu mãi chu đáo.
                    </p>
                    <?php // --- Social Icons (Optional) --- ?>
                    <div class="mt-3">
                        <a href="#" class="text-white-50 me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white-50 me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white-50 me-2"><i class="fab fa-instagram"></i></a>
                    </div>
                </div> 

                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-3 fw-semibold text-uppercase small">Liên kết nhanh</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="?page=home" class="link-light text-decoration-none small">Trang chủ</a></li>
                        <li class="mb-2"><a href="?page=shop_grid" class="link-light text-decoration-none small">Cửa hàng</a></li>
                        <li class="mb-2"><a href="#" class="link-light text-decoration-none small">Giới thiệu</a></li> <?php // Update this link if needed ?>
                        <li class="mb-2"><a href="?page=contact" class="link-light text-decoration-none small">Liên hệ</a></li>
                    </ul>
                </div>

                
                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-3 fw-semibold text-uppercase small">Hỗ trợ khách hàng</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="#" class="link-light text-decoration-none small">Chính sách đổi trả</a></li> <?php // Update this link if needed ?>
                        <li class="mb-2"><a href="#" class="link-light text-decoration-none small">Chính sách bảo mật</a></li> <?php // Update this link if needed ?>
                        <li class="mb-2"><a href="#" class="link-light text-decoration-none small">Điều khoản dịch vụ</a></li> <?php // Update this link if needed ?>
                        <li class="mb-2"><a href="#" class="link-light text-decoration-none small">Câu hỏi thường gặp</a></li> <?php // Update this link if needed ?>
                    </ul>
                </div>

                
                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-3 fw-semibold text-uppercase small">Liên hệ</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2 d-flex">
                            <i class="fas fa-map-marker-alt mt-1 me-2 text-secondary" style="width: 15px;"></i>
                            <span class="small text-white-50">19 Đ. Nguyễn Hữu Thọ, Tân Hưng, Quận 7, TP.HCM</span>
                        </li>
                        <li class="mb-2 d-flex">
                            <i class="fas fa-phone-alt mt-1 me-2 text-secondary" style="width: 15px;"></i>
                            <a href="tel:0987654321" class="link-light text-decoration-none small">0987 654 321</a>
                        </li>
                        <li class="mb-2 d-flex">
                            <i class="fas fa-envelope mt-1 me-2 text-secondary" style="width: 15px;"></i>
                            <a href="mailto:contact@myshop.com" class="link-light text-decoration-none small">contact@myshop.com</a>
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="my-4" style="border-color: rgba(255, 255, 255, 0.15);">

            <div class="text-center text-white-50 small">
                &copy; <?= date('Y') ?> MyShop. Thiết kế bởi Nhóm XYZ. <?php // Copyright ?>
            </div>
        </div>
    </footer>


    <?php // Bootstrap JS ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        // --- Global Helper Functions & Listeners ---

        /**
         * Toggles wishlist status via AJAX. Handles UI updates and error messages.
         * This function is now more robust.
         * @param {HTMLElement} buttonElement - The button element clicked.
         * @param {string|number} productId - ID of product.
         */
        if (typeof window.toggleWishlist !== 'function') {
             window.toggleWishlist = async function(buttonElement, productId) {
                if (!buttonElement || !productId) {
                    console.error("toggleWishlist: Missing button element or product ID.");
                    return;
                }
                // Prevent double-clicks while processing
                if (buttonElement.disabled) return;

                const isCurrentlyWishlisted = buttonElement.dataset.isWishlisted === '1';
                const action = isCurrentlyWishlisted ? 'wishlist_remove' : 'wishlist_add';
                const icon = buttonElement.querySelector('i'); // Get icon element

                // Disable button & show spinner
                buttonElement.disabled = true;
                const originalClasses = icon ? Array.from(icon.classList) : []; // Store original icon classes 
                const originalTitle = buttonElement.title; // Store original title 

                if (icon) {
                    icon.className = 'fas fa-spinner fa-spin fs-4'; // Set spinner icon
                } else {
                    buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                }

                // Build URL
                const url = `?page=${action}&id=${productId}&ajax=1&redirect=no`;
                console.log("Sending Wishlist request to:", url); // Debug 

                try {
                    // Perform AJAX request
                    const response = await fetch(url, {
                        method: 'GET', // Or 'POST' if your controller expects POST for AJAX
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    
                    // --- Handle HTTP errors ---
                    if (!response.ok) {
                        let errorMsg = `Lỗi HTTP ${response.status}`;
                        try {
                            // Attempt to parse JSON error message from backend
                            const errorData = await response.json();
                            if (errorData && errorData.message) {
                                errorMsg = errorData.message;
                            }
                        } catch (e) { /* Ignore if response is not JSON */ }
                        throw new Error(errorMsg); // Throw error to be caught below
                    }

                    // Handle successful response (expecting JSON) 
                    const contentType = response.headers.get("content-type");
                    if (!contentType || !contentType.includes("application/json")) {
                         const textResponse = await response.text();
                         console.error("Non-JSON Wishlist Response:", textResponse);
                         throw new Error('Phản hồi không hợp lệ từ máy chủ.'); // Throw error
                    }

                    const data = await response.json();
                    console.log("Wishlist response data:", data); // Debug

                    // --- Handle specific logic based on JSON response ---
                    if (data.login_required === true) {
                         // Backend requires login, perform redirect
                         console.log("Login required, redirecting..."); 
                         const currentUrl = encodeURIComponent(window.location.href || '?page=home');
                         const redirectUrl = `?page=login&redirect=${currentUrl}${window.location.hash || ''}`;
                         window.location.href = redirectUrl;
                         return; 
                    }

                    if (data.success === true) {
                        // Update Button State
                        const newIsWishlisted = !isCurrentlyWishlisted;
                        buttonElement.dataset.isWishlisted = newIsWishlisted ? '1' : '0';
                        buttonElement.classList.toggle('active', newIsWishlisted); // Toggle 'active' class 
                        buttonElement.classList.toggle('text-danger', newIsWishlisted); // Toggle red color 
                        buttonElement.classList.toggle('text-secondary', !newIsWishlisted); // Toggle gray color 
                        buttonElement.title = newIsWishlisted ? 'Xóa khỏi Yêu thích' : 'Thêm vào Yêu thích';

                        // Update Icon (fas fa-heart for active, far fa-heart for inactive)
                        if (icon) {
                            // Giữ lại class kích thước (ví dụ: fs-4) nếu có
                            const sizeClass = originalClasses.find(cls => cls.startsWith('fs-')) || 'fs-4';
                            icon.className = newIsWishlisted ? `fas fa-heart ${sizeClass}` : `far fa-heart ${sizeClass}`;
                        }

                        // Update Header Count
                        if (typeof data.wishlistItemCount !== 'undefined') {
                            const countElement = document.getElementById('header-wishlist-count');
                            if (countElement) {
                                const count = parseInt(data.wishlistItemCount, 10);
                                countElement.textContent = count;
                                countElement.style.display = count > 0 ? '' : 'none'; // Show/hide badge
                            } else {
                                console.warn("Header wishlist count element (#header-wishlist-count) not found.");
                            }
                        }

                    } else {
                        // Operation failed on the backend (but wasn't a login issue)
                        // Show specific error message from server if provided
                        throw new Error(data.message || 'Wishlist action failed.');
                    }

                } catch (error) {
                    console.error('Wishlist Error:', error);
                    alert('Error: ' + error.message); // Show error message to user

                    // Restore Button to Original State on Error
                    if (icon) {
                         // Restore original classes 
                        const sizeClass = originalClasses.find(cls => cls.startsWith('fs-')) || 'fs-4';
                        icon.className = originalClasses.includes('fas') ? `fas fa-heart ${sizeClass}` : `far fa-heart ${sizeClass}`; 
                    } else {
                       
                    }
                    buttonElement.title = originalTitle; // Restore original title


                } finally {
                    // Re-enable Button (unless redirecting for login)
                    if (!window.location.href.includes('page=login')) {
                         buttonElement.disabled = false;
                    }
                }
            }
        } // end window.toggleWishlist definition

        // General Event Listener for Wishlist Buttons
        if (!document.body.dataset.wishlistListenerAttached) {
            document.body.dataset.wishlistListenerAttached = 'true'; 
            console.log("Attaching global wishlist listener.");

            document.body.addEventListener('click', function (event) {
                const wishlistButton = event.target.closest('.btn-wishlist');

                // Check if the button was found and is not disabled
                if (wishlistButton && !wishlistButton.disabled) {
                    const isLoggedIn = document.body.dataset.isLoggedIn === 'true';
                    // *** CRITICAL: Get productId from the specific button found ***
                    const productId = wishlistButton.dataset.productId;

                    if (!productId) { 
                        console.error("Missing productId");
                        return; 
                    }

                    console.log(`Wishlist button clicked! Product ID: ${productId}, Logged In: ${isLoggedIn}`); 

                    if (!isLoggedIn) {
                        console.log("Not logged in");
                        const currentUrl = encodeURIComponent(window.location.href || '?page=home');
                        const redirectUrl = `?page=login&redirect=${currentUrl}${window.location.hash || ''}`;
                        window.location.href = redirectUrl;
                    } else {
                        if (typeof window.toggleWishlist === 'function') {
                            window.toggleWishlist(wishlistButton, productId);
                        } else {
                            console.error("window.toggleWishlist function is not defined.");
                        }
                    }
                }
            });
        } else {
             console.log("Global wishlist listener already attached.");
        } // End wishlist listener

        // Initialize Bootstrap Tooltips 
        document.addEventListener('DOMContentLoaded', function () {
            
            if (typeof bootstrap !== 'undefined' && typeof bootstrap.Tooltip !== 'undefined') {
                
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
                console.log("Bootstrap tooltips initialized.");
            } else {
                console.warn("Bootstrap or Bootstrap Tooltip component not found. Tooltips will not be initialized.");
            }
        }); // End tooltip initialization
    </script>
    <?php // End Global Script ?>

    <?php // Page-Specific Script Loading Section ?>
    <?php
    // Get current page
    $currentPage = $_GET['page'] ?? 'home';
    // base URL for js file
    $scriptBaseUrl = "/webfinal/public/js";

    // Scripts for pages
    $pageScripts = [
        'shop_grid' => 'shop_grid_ajax.js', 
        'product_detail' => 'product_detail.js',
        'cart' => 'cart.js',
        'register' => 'register.js',
        'login' => 'login.js',
        'change_password' => 'change_password.js',
        'reset_password' => 'reset_password.js',
    ];

    // Check if a specific script is defined 
    if (isset($pageScripts[$currentPage])) {
        $scriptName = $pageScripts[$currentPage];
        $scriptPath = BASE_PATH . "/public/js/" . $scriptName; // Đường dẫn tuyệt đối để kiểm tra
        if (file_exists($scriptPath)) {
             // Add an ID for easier debugging in developer tools
            echo "<script id='page-script-{$currentPage}' src='{$scriptBaseUrl}/{$scriptName}' defer></script>"; // Use defer
        } else {
            error_log("Page specific script not found: " . $scriptPath);
        }
    }
    ?>
    <?php // End Page-Specific Script Loading Section ?>

    </body>
    </html>