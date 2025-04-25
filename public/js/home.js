// --- Wishlist Toggle Function (Chỉ chứa logic AJAX) ---
async function toggleWishlist(buttonElement, productId) {
    // ... (code toggleWishlist giữ nguyên như trước) ...
    console.log("AJAX toggleWishlist called for button:", buttonElement, "productId:", productId);

    const isWishlisted = buttonElement.dataset.isWishlisted === '1';
    const action = isWishlisted ? 'wishlist_remove' : 'wishlist_add';
    const icon = buttonElement.querySelector('i');

    buttonElement.disabled = true;
    if(icon) { icon.classList.remove('fa-heart'); icon.classList.add('fa-spinner', 'fa-spin'); }

    try {
        const response = await fetch(`?page=${action}&id=${productId}&ajax=1&redirect=no`, {
            method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            const data = await response.json();
            console.log("Wishlist Response:", data);
            if (data.success) {
                buttonElement.dataset.isWishlisted = isWishlisted ? '0' : '1';
                buttonElement.classList.toggle('active');
                buttonElement.title = isWishlisted ? 'Thêm vào Yêu thích' : 'Xóa khỏi Yêu thích';
                if (typeof data.wishlistItemCount !== 'undefined') {
                    const wishlistCountElement = document.getElementById('header-wishlist-count');
                    if (wishlistCountElement) {
                        const newCount = parseInt(data.wishlistItemCount);
                        wishlistCountElement.textContent = newCount;
                        wishlistCountElement.style.display = newCount > 0 ? 'inline-block' : 'none';
                    }
                }
            } else {
                if (data.login_required) { // Xử lý nếu Controller trả về yêu cầu đăng nhập
                    const currentUrl = encodeURIComponent(window.location.href || '?page=home');
                    window.location.href = `?page=login&redirect=${currentUrl}`;
                    return; // Dừng xử lý tiếp
                }
                alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
            }
        } else {
            const textResponse = await response.text();
            console.error("Non-JSON Wishlist Response:", textResponse);
            throw new Error('Received non-JSON response from server during wishlist toggle.');
        }
    } catch (error) {
        console.error('Error toggling wishlist:', error);
        alert('Lỗi kết nối hoặc xử lý (Wishlist). Vui lòng thử lại.');
    } finally {
        buttonElement.disabled = false;
        if(icon) { icon.classList.remove('fa-spinner', 'fa-spin'); icon.classList.add('fa-heart'); }
    }
}