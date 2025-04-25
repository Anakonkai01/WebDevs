// --- Wishlist Toggle Function (Chỉ chứa logic AJAX) ---
        async function toggleWishlist(buttonElement, productId) {
            // Hàm này giờ chỉ chạy khi người dùng ĐÃ ĐĂNG NHẬP (được gọi bởi event listener)
            console.log("AJAX toggleWishlist called for button:", buttonElement, "productId:", productId);

            const isWishlisted = buttonElement.dataset.isWishlisted === '1';
            const action = isWishlisted ? 'wishlist_remove' : 'wishlist_add';
            const icon = buttonElement.querySelector('i');

            buttonElement.disabled = true; // Disable nút tạm thời
            icon.classList.remove('fa-heart'); // Bỏ icon trái tim
            icon.classList.add('fa-spinner', 'fa-spin'); // Thêm icon xoay

            try {
                // Gửi yêu cầu AJAX
                const response = await fetch(`?page=${action}&id=${productId}&ajax=1&redirect=no`, {
                    method: 'GET', // Hoặc POST nếu controller nhận POST cho ajax
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                // Kiểm tra content type trước khi parse JSON
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    const data = await response.json(); // Parse JSON
                    console.log("Wishlist Response:", data); // Log để debug

                    if (data.success) {
                        // Cập nhật trạng thái nút
                        buttonElement.dataset.isWishlisted = isWishlisted ? '0' : '1';
                        buttonElement.classList.toggle('active');
                        buttonElement.title = isWishlisted ? 'Thêm vào Yêu thích' : 'Xóa khỏi Yêu thích';

                        // *** CẬP NHẬT HEADER COUNT ***
                        if (typeof data.wishlistItemCount !== 'undefined') {
                            const wishlistCountElement = document.getElementById('header-wishlist-count');
                            if (wishlistCountElement) {
                                const newCount = parseInt(data.wishlistItemCount);
                                wishlistCountElement.textContent = newCount;
                                // Hiện/ẩn badge dựa trên số lượng mới
                                wishlistCountElement.style.display = newCount > 0 ? 'inline-block' : 'none';
                            }
                        }
                        // *** KẾT THÚC CẬP NHẬT HEADER COUNT ***

                    } else {
                        // Các lỗi khác từ server (không phải login_required)
                        alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                    }
                } else {
                    // Xử lý trường hợp không phải JSON
                    const textResponse = await response.text();
                    console.error("Non-JSON Wishlist Response:", textResponse);
                    throw new Error('Received non-JSON response from server during wishlist toggle.');
                }
            } catch (error) {
                console.error('Error toggling wishlist:', error);
                alert('Lỗi kết nối hoặc xử lý (Wishlist). Vui lòng thử lại.');
            } finally {
                // Khôi phục trạng thái nút
                buttonElement.disabled = false;
                icon.classList.remove('fa-spinner', 'fa-spin'); // Bỏ icon xoay
                icon.classList.add('fa-heart'); // Thêm lại icon trái tim
            }
        }
        // No addToCart JS needed here now