<?php // webfinal/app/layout/footer.php ?>

    </main> <?php // Đóng thẻ main từ header.php ?>

    <?php // --- Footer Section HTML --- ?>
    <footer class="site-footer mt-auto bg-dark text-white pt-5 pb-4">
        <div class="container">
            <div class="row gy-4">
                <?php // Cột Về MyShop ?>
                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-3 fw-semibold text-uppercase small">Về MyShop</h5>
                    <p class="small text-white-50">
                        MyShop là cửa hàng cung cấp các sản phẩm công nghệ chính hãng với giá tốt nhất, đảm bảo chất lượng và dịch vụ hậu mãi chu đáo.
                    </p>
                    <?php // Optional: Social Icons ?>
                    <div class="mt-3">
                        <a href="#" class="text-white-50 me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white-50 me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white-50 me-2"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>

                <?php // Cột Liên kết nhanh ?>
                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-3 fw-semibold text-uppercase small">Liên kết nhanh</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="?page=home" class="link-light text-decoration-none small">Trang chủ</a></li>
                        <li class="mb-2"><a href="?page=shop_grid" class="link-light text-decoration-none small">Cửa hàng</a></li>
                        <li class="mb-2"><a href="#" class="link-light text-decoration-none small">Giới thiệu</a></li> <?php // Cập nhật link nếu có ?>
                        <li class="mb-2"><a href="?page=contact" class="link-light text-decoration-none small">Liên hệ</a></li>
                    </ul>
                </div>

                <?php // Cột Hỗ trợ khách hàng ?>
                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-3 fw-semibold text-uppercase small">Hỗ trợ khách hàng</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="#" class="link-light text-decoration-none small">Chính sách đổi trả</a></li> <?php // Cập nhật link ?>
                        <li class="mb-2"><a href="#" class="link-light text-decoration-none small">Chính sách bảo mật</a></li> <?php // Cập nhật link ?>
                        <li class="mb-2"><a href="#" class="link-light text-decoration-none small">Điều khoản dịch vụ</a></li> <?php // Cập nhật link ?>
                        <li class="mb-2"><a href="#" class="link-light text-decoration-none small">Câu hỏi thường gặp</a></li> <?php // Cập nhật link ?>
                    </ul>
                </div>

                <?php // Cột Liên hệ ?>
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
                &copy; <?= date('Y') ?> MyShop. Thiết kế bởi Nhóm XYZ. <?php // Thêm tên nhóm/tác giả nếu muốn ?>
            </div>
        </div>
    </footer>
    <?php // --- END Footer Section HTML --- ?>


    <?php // --- JavaScript Includes & Global Scripts --- ?>
    <?php // Bootstrap JS (needs Popper) - Đã xóa integrity và crossorigin để sửa lỗi parsing ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        // --- Global Helper Functions & Listeners ---

        /**
         * Toggles wishlist status via AJAX. Handles UI updates and error messages.
         * This function is now more robust.
         * @param {HTMLElement} buttonElement - The button element clicked.
         * @param {string|number} productId - The ID of the product.
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
                const icon = buttonElement.querySelector('i'); // Get the icon element

                // --- UI Feedback: Disable button & show spinner ---
                buttonElement.disabled = true;
                const originalClasses = icon ? Array.from(icon.classList) : []; // Store original icon classes
                const originalTitle = buttonElement.title; // Store original title

                if (icon) {
                    icon.className = 'fas fa-spinner fa-spin fs-4'; // Set spinner icon, giữ fs-4 nếu cần
                } else {
                    // Fallback if no icon found (less likely with current HTML)
                    buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                }

                // Build URL correctly
                const url = `?page=${action}&id=${productId}&ajax=1&redirect=no`;
                console.log("Sending Wishlist request to:", url); // Debug

                try {
                    // --- Perform the AJAX request ---
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

                    // --- Handle successful response (expecting JSON) ---
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
                         // Important: Do not re-enable button or reset icon, as the page is navigating away
                         return; // Exit the function
                    }

                    if (data.success === true) {
                        // --- Update Button State ---
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

                        // --- Update Header Count ---
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
                        throw new Error(data.message || 'Thao tác yêu thích thất bại.');
                    }

                } catch (error) {
                    // --- Handle Fetch/Network errors or errors thrown above ---
                    console.error('Lỗi khi xử lý yêu thích:', error);
                    alert('Lỗi: ' + error.message); // Show specific error message to user

                    // --- Restore Button to Original State on Error ---
                     // (Check if icon element exists before trying to reset its class)
                    if (icon) {
                         // Giữ lại class kích thước (ví dụ: fs-4) khi khôi phục
                        const sizeClass = originalClasses.find(cls => cls.startsWith('fs-')) || 'fs-4';
                        icon.className = originalClasses.includes('fas') ? `fas fa-heart ${sizeClass}` : `far fa-heart ${sizeClass}`; // Restore based on original filled/outline
                    } else {
                        // Restore original HTML if icon wasn't found initially
                        // buttonElement.innerHTML = originalIconHTML; // Need to store this if needed
                    }
                    buttonElement.title = originalTitle; // Restore original title


                } finally {
                    // --- Re-enable Button (unless redirecting for login) ---
                     // Check if the current URL contains 'page=login'. If it doesn't, it means no redirect happened.
                    if (!window.location.href.includes('page=login')) {
                         buttonElement.disabled = false;
                         // The icon/title should already be restored or updated in try/catch blocks
                    }
                }
            }
        } // end window.toggleWishlist definition

        // --- General Event Listener for Wishlist Buttons (using Event Delegation) ---
        // Ensure this listener is attached only once
        if (!document.body.dataset.wishlistListenerAttached) {
            document.body.dataset.wishlistListenerAttached = 'true'; // Mark as attached
            console.log("Attaching global wishlist listener."); // Debug

            document.body.addEventListener('click', function(event) {
                // Find the closest ancestor button with the class .btn-wishlist
                const wishlistButton = event.target.closest('.btn-wishlist');

                // Check if the button was found and is not disabled
                if (wishlistButton && !wishlistButton.disabled) {
                    const isLoggedIn = document.body.dataset.isLoggedIn === 'true';
                    // *** CRITICAL: Get productId from the specific button found ***
                    const productId = wishlistButton.dataset.productId;

                    if (!productId) {
                        console.error("Missing data-product-id on the clicked wishlist button.");
                        return; // Stop if productId is missing
                    }

                    console.log(`Wishlist button clicked! Product ID: ${productId}, Logged In: ${isLoggedIn}`); // Debug log

                    if (!isLoggedIn) {
                        // Redirect to login if not logged in
                        console.log("User not logged in, redirecting to login.");
                        const currentUrl = encodeURIComponent(window.location.href || '?page=home');
                        // Include hash for potential anchors like #reviews-content
                        const redirectUrl = `?page=login&redirect=${currentUrl}${window.location.hash || ''}`;
                        window.location.href = redirectUrl;
                    } else {
                        // Call the AJAX handler if logged in
                        // Make sure window.toggleWishlist is defined and accessible
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
        }


        // --- Initialize Bootstrap Tooltips ---
         document.addEventListener('DOMContentLoaded', function () {
             // Check if Bootstrap and Tooltip component are loaded
             if (typeof bootstrap !== 'undefined' && typeof bootstrap.Tooltip !== 'undefined') {
                 var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                 var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                   return new bootstrap.Tooltip(tooltipTriggerEl);
                 });
                 console.log("Bootstrap tooltips initialized."); // Debug log
             } else {
                 console.warn("Bootstrap or Bootstrap Tooltip component not found. Tooltips will not be initialized.");
             }
         });

    </script>

    <?php // --- Page-Specific Script Loading --- ?>
    <?php
    $currentPage = $_GET['page'] ?? 'home';
    $scriptBaseUrl = "/webfinal/public/js"; // Use a variable for base path

    // Define scripts for different pages
    // **Quan trọng:** Chỉ nên load 1 file JS chính cho mỗi trang (ví dụ: shop_grid_ajax.js xử lý cả AJAX và các tương tác khác trên trang shop)
    $pageScripts = [
        'shop_grid' => 'shop_grid_ajax.js', // JS này nên xử lý cả AJAX và các tương tác khác của trang shop
        'product_detail' => 'product_detail.js',
        'cart' => 'cart.js',
        'register' => 'register.js',
        'login' => 'login.js',
        'change_password' => 'change_password.js',
        'reset_password' => 'reset_password.js',
        // 'home' => 'home.js', // Trang home hiện chỉ cần listener wishlist toàn cục ở footer là đủ
    ];

    // Include the script if it exists for the current page
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
    <?php // --- End Page-Specific Script Loading --- ?>

    </body>
    </html>