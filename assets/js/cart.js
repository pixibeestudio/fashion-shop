document.addEventListener('DOMContentLoaded', () => {
    // Update global cart badge
    const updateCartBadge = async () => {
        const badge = document.getElementById('cartCountBadge');
        if (!badge) return;
        
        try {
            const response = await fetch('/fashion-shop/api/cart/count.php');
            const result = await response.json();
            if (result.success) {
                badge.textContent = result.cart_count;
                badge.style.display = result.cart_count > 0 ? 'flex' : 'none';
            }
        } catch (error) {
            console.error('Lỗi khi lấy số lượng giỏ hàng:', error);
        }
    };

    // Gọi lần đầu khi load trang
    updateCartBadge();

    // Export function to global to allow other scripts to call it
    window.updateCartBadge = updateCartBadge;
});
