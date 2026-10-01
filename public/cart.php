<?php
$pageTitle = 'Giỏ hàng của bạn - Fashion Shop';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../app/controllers/CartController.php';

$cartController = new CartController();
$cartData = $cartController->getCartItems();
$items = $cartData['items'];
$total = $cartData['total'];
?>

<div class="store-wrapper" style="margin-top: 40px; margin-bottom: 80px;">
    <h1 style="font-size: 28px; margin-bottom: 24px; color: var(--text-color);">Giỏ hàng của bạn</h1>

    <?php if (empty($items)): ?>
        <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <div style="font-size: 64px; color: #cbd5e1; margin-bottom: 16px;"><i class="fas fa-shopping-basket"></i></div>
            <h2 style="font-size: 20px; margin-bottom: 12px;">Giỏ hàng trống</h2>
            <p style="color: var(--text-muted); margin-bottom: 24px;">Bạn chưa có sản phẩm nào trong giỏ hàng.</p>
            <a href="category.php" class="store-btn" style="display: inline-block;">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px; align-items: start;">
            <!-- Danh sách sản phẩm -->
            <div class="cart-items" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 16px; margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                        <input type="checkbox" id="selectAllCart" style="width: 18px; height: 18px; accent-color: var(--primary);">
                        Chọn tất cả (<?= count($items) ?>)
                    </label>
                    <button type="button" id="btnDeleteSelected" style="color: var(--danger); background: none; border: none; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 15px;">
                        <i class="fas fa-trash-alt"></i> Xóa mục đã chọn
                    </button>
                </div>
                <?php foreach ($items as $item): ?>
                    <div class="cart-item" data-variant-id="<?= $item['variant_id'] ?>" style="display: flex; gap: 20px; padding-bottom: 20px; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; align-items: center;">
                        <input type="checkbox" class="cart-item-checkbox" value="<?= $item['variant_id'] ?>" style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <img src="<?= htmlspecialchars($item['primary_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                        
                        <div style="flex: 1;">
                            <a href="product.php?slug=<?= $item['product_slug'] ?>" style="text-decoration: none; color: inherit;">
                                <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 600;"><?= htmlspecialchars($item['product_name']) ?></h3>
                            </a>
                            <p style="color: var(--text-muted); font-size: 14px; margin: 0 0 12px 0;">
                                Phân loại: <?= htmlspecialchars($item['color']) ?> - <?= htmlspecialchars($item['size']) ?>
                            </p>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div class="quantity-control" style="display: flex; align-items: center; border: 1px solid #cbd5e1; border-radius: 4px; overflow: hidden; width: max-content;">
                                    <button class="btn-qty-minus" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border: none; border-right: 1px solid #cbd5e1; cursor: pointer; transition: 0.2s;"><i class="fas fa-minus" style="font-size: 12px; color: #475569;"></i></button>
                                    <input type="number" class="qty-input" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock_quantity'] ?>" style="width: 48px; height: 32px; text-align: center; border: none; font-weight: 600; font-size: 14px; outline: none; -moz-appearance: textfield; padding: 0;">
                                    <button class="btn-qty-plus" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border: none; border-left: 1px solid #cbd5e1; cursor: pointer; transition: 0.2s;"><i class="fas fa-plus" style="font-size: 12px; color: #475569;"></i></button>
                                </div>
                                
                                <div style="text-align: right;">
                                    <div style="color: var(--primary); font-weight: 700; font-size: 16px; margin-bottom: 8px;" class="item-subtotal"><?= number_format($item['subtotal']) ?>đ</div>
                                    <button class="btn-remove-item" style="color: #ef4444; background: #fee2e2; border: none; cursor: pointer; font-size: 14px; padding: 6px 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; font-weight: 500; transition: 0.2s;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">
                                        <i class="fas fa-trash-alt"></i> Xóa
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Tổng kết đơn hàng -->
            <div class="cart-summary" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); position: sticky; top: 20px;">
                <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">Tóm tắt đơn hàng</h3>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 16px; color: var(--text-muted);">
                    <span>Tạm tính:</span>
                    <span id="summarySubtotal" style="color: var(--text-color); font-weight: 600;"><?= number_format($total) ?>đ</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 16px; color: var(--text-muted);">
                    <span>Phí vận chuyển:</span>
                    <span style="color: var(--text-color);">Chưa tính</span>
                </div>
                
                <div style="border-top: 1px dashed #e2e8f0; padding-top: 16px; margin-top: 16px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600;">Tổng tiền:</span>
                    <span id="summaryTotal" style="font-size: 24px; font-weight: 800; color: var(--primary);"><?= number_format($total) ?>đ</span>
                </div>
                
                <a href="checkout.php" class="store-btn" style="width: 100%; text-align: center; display: block; box-sizing: border-box; font-size: 16px; padding: 14px; border-radius: 6px; margin-bottom: 12px; text-decoration: none; background: #4F46E5; color: white; border: 1px solid #4F46E5; transition: 0.2s;" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4F46E5'">Thanh Toán Ngay</a>
                <a href="category.php" class="store-btn" style="width: 100%; text-align: center; display: block; box-sizing: border-box; font-size: 16px; padding: 14px; border-radius: 6px; background: #ffffffff; color: #4F46E5; border: 1px solid #4F46E5; text-decoration: none; transition: 0.2s;" onmouseover="this.style.background='#e8e8f9'" onmouseout="this.style.background='transparent'">Tiếp tục mua hàng</a>
                <div style="text-align: center; margin-top: 16px;">
                    <a href="index.php" style="color: var(--text-color); font-weight: 600; font-size: 14px; text-decoration: none;">&larr; Quay về Trang chủ</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Format currency helper
    const formatMoney = (amount) => {
        return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
    };

    // Helper: Gọi API cập nhật giỏ hàng
    const updateCartItem = async (variantId, quantity, itemElement) => {
        try {
            const res = await fetch('/fashion-shop/api/cart/update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ variant_id: variantId, quantity: quantity })
            });
            const data = await res.json();
            
            if (data.success) {
                // Update global badge
                if (window.updateCartBadge) window.updateCartBadge();
                
                // Nếu số lượng = 0 (Xóa) thì reload page cho nhanh hoặc xóa thẻ HTML
                if (quantity === 0) {
                    location.reload();
                    return;
                }
                
                // Cập nhật lại UI tóm tắt
                document.getElementById('summarySubtotal').textContent = formatMoney(data.cart_total);
                document.getElementById('summaryTotal').textContent = formatMoney(data.cart_total);
                
                // Cập nhật lại Item Subtotal bằng cách lấy giá trị price * qty, nhưng mình chưa lưu price trong JS
                // Giải pháp nhanh là reload page nếu muốn dễ, nhưng để mượt ta reload
                location.reload(); 
            } else {
                alert(data.message);
                // Hoàn tác input về giá trị trước đó (sẽ dễ hơn nếu reload)
                location.reload();
            }
        } catch (err) {
            console.error(err);
            alert('Lỗi kết nối máy chủ!');
        }
    };

    // Xử lý nút tăng giảm số lượng
    document.querySelectorAll('.cart-item').forEach(item => {
        const variantId = item.dataset.variantId;
        const btnMinus = item.querySelector('.btn-qty-minus');
        const btnPlus = item.querySelector('.btn-qty-plus');
        const inputQty = item.querySelector('.qty-input');
        const btnRemove = item.querySelector('.btn-remove-item');
        
        let debounceTimer;

        const updateAPI = (newVal) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                updateCartItem(variantId, newVal, item);
            }, 500);
        };

        btnMinus.addEventListener('click', () => {
            let val = parseInt(inputQty.value) || 1;
            if (val > 1) {
                inputQty.value = val - 1;
                updateAPI(val - 1);
            }
        });

        btnPlus.addEventListener('click', () => {
            let val = parseInt(inputQty.value) || 1;
            let max = parseInt(inputQty.max);
            if (val < max) {
                inputQty.value = val + 1;
                updateAPI(val + 1);
            } else {
                alert(`Sản phẩm này chỉ còn tối đa ${max} sản phẩm!`);
            }
        });

        inputQty.addEventListener('change', () => {
            let val = parseInt(inputQty.value);
            let max = parseInt(inputQty.max);
            if (isNaN(val) || val < 1) val = 1;
            if (val > max) {
                alert(`Sản phẩm này chỉ còn tối đa ${max} sản phẩm!`);
                val = max;
            }
            inputQty.value = val;
            updateAPI(val);
        });

        // Xóa khỏi giỏ hàng
        btnRemove.addEventListener('click', async () => {
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                try {
                    const res = await fetch('/fashion-shop/api/cart/remove.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ variant_id: variantId })
                    });
                    const data = await res.json();
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                } catch (err) {
                    alert('Lỗi kết nối máy chủ!');
                }
            }
        });
    });

    // Checkbox logic
    const selectAllCart = document.getElementById('selectAllCart');
    const itemCheckboxes = document.querySelectorAll('.cart-item-checkbox');
    const btnDeleteSelected = document.getElementById('btnDeleteSelected');

    if (selectAllCart) {
        selectAllCart.addEventListener('change', function() {
            itemCheckboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    if (itemCheckboxes.length > 0) {
        itemCheckboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                const allChecked = document.querySelectorAll('.cart-item-checkbox:checked').length === itemCheckboxes.length;
                if (selectAllCart) selectAllCart.checked = allChecked;
            });
        });
    }

    if (btnDeleteSelected) {
        btnDeleteSelected.addEventListener('click', async () => {
            const checkedBoxes = document.querySelectorAll('.cart-item-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Vui lòng chọn ít nhất 1 sản phẩm để xóa.');
                return;
            }

            if (confirm(`Bạn có chắc chắn muốn xóa ${checkedBoxes.length} sản phẩm đã chọn?`)) {
                // Delete one by one via API sequentially
                btnDeleteSelected.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xóa...';
                let successCount = 0;

                for (let cb of checkedBoxes) {
                    try {
                        const res = await fetch('/fashion-shop/api/cart/remove.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ variant_id: cb.value })
                        });
                        const data = await res.json();
                        if (data.success) successCount++;
                    } catch (e) {
                        console.error('Lỗi khi xóa', cb.value);
                    }
                }

                alert(`Đã xóa thành công ${successCount} sản phẩm.`);
                location.reload();
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
