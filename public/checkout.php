<?php
$pageTitle = 'Thanh toán - Fashion Shop';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../app/controllers/CartController.php';

// Cần đăng nhập để lấy thông tin khách hàng, nếu chưa có thể điền thông tin Guest (tuỳ logic hệ thống, nhưng ở đây có $_SESSION['customer_id'])
$customerId = $_SESSION['customer_id'] ?? null;
if (!$customerId) {
    // Lưu lại URL để sau khi đăng nhập xong sẽ quay lại
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header('Location: login.php');
    exit;
}

$customerName = $_SESSION['customer_name'] ?? '';
$customerPhone = $_SESSION['customer_phone'] ?? '';
$customerEmail = $_SESSION['customer_email'] ?? '';
$customerAddress = $_SESSION['customer_address'] ?? '';

// Fetch saved addresses
$savedAddresses = [];
if ($customerId) {
    require_once __DIR__ . '/../app/config/Database.php';
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM customer_addresses WHERE customer_id = :id ORDER BY is_default DESC");
    $stmt->execute(['id' => $customerId]);
    $savedAddresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Auto-fill default address if not set
    if (empty($customerAddress) && !empty($savedAddresses)) {
        $default = $savedAddresses[0];
        $customerName = $default['receiver_name'];
        $customerPhone = $default['receiver_phone'];
        $customerAddress = $default['address_line'] . ', ' . $default['ward'] . ', ' . $default['district'] . ', ' . $default['province'];
    }
}

$cartController = new CartController();
$cartData = $cartController->getCartItems();
$items = $cartData['items'];
$total = $cartData['total'];

if (empty($items)) {
    // Nếu không có sản phẩm trong giỏ, redirect về giỏ hàng
    header('Location: cart.php');
    exit;
}
?>

<div class="store-wrapper" style="margin-top: 40px; margin-bottom: 80px;">
    <h1 style="font-size: 28px; margin-bottom: 24px; color: var(--text-color);">Thanh toán đơn hàng</h1>

    <div style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 40px; align-items: start;">
        
        <!-- Cột Trái: Thông tin giao hàng & Thanh toán -->
        <div>
            <form id="checkoutForm">
                <div style="background: white; border-radius: 12px; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 24px;">
                    <h3 style="margin-top: 0; margin-bottom: 24px; font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px;">1. Thông tin giao hàng</h3>
                    
                    <?php if (!$customerId): ?>
                    <div style="margin-bottom: 24px; padding: 16px; background: #f8fafc; border-radius: 8px; font-size: 14px;">
                        Bạn đã có tài khoản? <a href="login.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Đăng nhập</a> để thanh toán nhanh hơn và tích điểm.
                    </div>
                    <?php elseif (!empty($savedAddresses)): ?>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px;">Chọn địa chỉ đã lưu</label>
                        <select id="savedAddressSelector" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; background: white;" onchange="fillAddress(this)">
                            <option value="">-- Khác / Nhập địa chỉ mới --</option>
                            <?php foreach ($savedAddresses as $index => $addr): 
                                $fullAddr = $addr['address_line'] . ', ' . $addr['ward'] . ', ' . $addr['district'] . ', ' . $addr['province'];
                            ?>
                                <option value="<?= $index ?>" 
                                    data-name="<?= htmlspecialchars($addr['receiver_name']) ?>"
                                    data-phone="<?= htmlspecialchars($addr['receiver_phone']) ?>"
                                    data-address="<?= htmlspecialchars($fullAddr) ?>"
                                    <?= $index === 0 ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($addr['receiver_name']) ?> - <?= htmlspecialchars($fullAddr) ?> <?= $addr['is_default'] ? '(Mặc định)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <script>
                    function fillAddress(select) {
                        const form = document.getElementById('checkoutForm');
                        if (select.value === "") {
                            form.customer_name.value = "<?= htmlspecialchars($_SESSION['customer_name'] ?? '') ?>";
                            form.phone.value = "<?= htmlspecialchars($_SESSION['customer_phone'] ?? '') ?>";
                            form.address.value = "";
                        } else {
                            const option = select.options[select.selectedIndex];
                            form.customer_name.value = option.getAttribute('data-name');
                            form.phone.value = option.getAttribute('data-phone');
                            form.address.value = option.getAttribute('data-address');
                        }
                    }
                    </script>
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px;">Họ và tên <span style="color:red">*</span></label>
                            <input type="text" name="customer_name" required value="<?= htmlspecialchars($customerName) ?>" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px;">Số điện thoại <span style="color:red">*</span></label>
                            <input type="tel" name="phone" required value="<?= htmlspecialchars($customerPhone) ?>" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit;">
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px;">Địa chỉ Email (Tùy chọn)</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($customerEmail) ?>" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit;">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px;">Địa chỉ giao hàng <span style="color:red">*</span></label>
                        <input type="text" name="address" required value="<?= htmlspecialchars($customerAddress) ?>" placeholder="Số nhà, Tên đường, Phường/Xã, Quận/Huyện, Tỉnh/TP" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit;">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px;">Ghi chú đơn hàng (Tùy chọn)</label>
                        <textarea name="note" rows="3" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; resize: vertical;" placeholder="Giao hàng vào giờ hành chính, gọi trước khi giao..."></textarea>
                    </div>
                </div>

                <div style="background: white; border-radius: 12px; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <h3 style="margin-top: 0; margin-bottom: 24px; font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px;">2. Phương thức thanh toán</h3>
                    
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                        <label style="display: flex; align-items: center; padding: 16px; cursor: pointer; border-bottom: 1px solid #e2e8f0;">
                            <input type="radio" name="payment_method" value="cod" checked style="margin-right: 12px; width: 18px; height: 18px; accent-color: var(--primary);">
                            <div style="flex: 1;">
                                <div style="font-weight: 600; margin-bottom: 4px;">Thanh toán khi nhận hàng (COD)</div>
                                <div style="font-size: 13px; color: var(--text-muted);">Trả tiền mặt khi giao hàng.</div>
                            </div>
                            <i class="fas fa-money-bill-wave" style="font-size: 24px; color: #94a3b8;"></i>
                        </label>

                        <label style="display: flex; align-items: center; padding: 16px; cursor: pointer; background: #f8fafc;">
                            <input type="radio" name="payment_method" value="banking" disabled style="margin-right: 12px; width: 18px; height: 18px;">
                            <div style="flex: 1; opacity: 0.5;">
                                <div style="font-weight: 600; margin-bottom: 4px;">Chuyển khoản qua ngân hàng</div>
                                <div style="font-size: 13px; color: var(--text-muted);">Tính năng đang được bảo trì.</div>
                            </div>
                            <i class="fas fa-university" style="font-size: 24px; color: #cbd5e1;"></i>
                        </label>
                    </div>
                </div>
            </form>
        </div>

        <!-- Cột Phải: Tóm tắt giỏ hàng -->
        <div style="background: white; border-radius: 12px; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); position: sticky; top: 20px;">
            <h3 style="margin-top: 0; margin-bottom: 24px; font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px;">Tóm tắt đơn hàng</h3>
            
            <div style="max-height: 400px; overflow-y: auto; padding-right: 8px; margin-bottom: 24px;">
                <?php foreach ($items as $item): ?>
                <div style="display: flex; gap: 16px; margin-bottom: 16px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 16px;">
                    <div style="position: relative;">
                        <img src="<?= htmlspecialchars($item['primary_image']) ?>" style="width: 64px; height: 64px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <span style="position: absolute; top: -8px; right: -8px; background: var(--text-color); color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600;"><?= $item['quantity'] ?></span>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 14px; margin-bottom: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?= htmlspecialchars($item['product_name']) ?></div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 6px;"><?= htmlspecialchars($item['color']) ?> - <?= htmlspecialchars($item['size']) ?></div>
                        <div style="font-weight: 600; color: var(--primary); font-size: 14px;"><?= number_format($item['price']) ?>đ</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="display: flex; gap: 12px; margin-bottom: 24px;">
                <input type="text" id="promoCode" placeholder="Nhập mã khuyến mãi (VD: FREESHIP)" style="flex: 1; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit;">
                <button type="button" id="btnApplyPromo" style="padding: 12px 24px; background: white; border: 1px solid var(--primary); color: var(--primary); font-weight: 600; border-radius: 6px; cursor: pointer;">Áp dụng</button>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 12px; color: var(--text-muted); font-size: 15px;">
                <span>Tạm tính (Sub Total):</span>
                <span style="color: var(--text-color); font-weight: 600;" id="subtotalDisplay" data-value="<?= $total ?>"><?= number_format($total) ?>đ</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 12px; color: var(--text-muted); font-size: 15px;">
                <span>Phí vận chuyển:</span>
                <span style="color: var(--text-color); font-weight: 600;">30,000đ</span>
            </div>
            <div id="discountRow" style="display: none; justify-content: space-between; margin-bottom: 12px; color: var(--danger); font-size: 15px;">
                <span>Mã giảm giá (<span id="discountCodeText"></span>):</span>
                <span style="font-weight: 600;">-<span id="discountAmountDisplay">0</span>đ</span>
            </div>
            
            <div style="border-top: 1px dashed #cbd5e1; padding-top: 20px; margin-top: 20px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 600; font-size: 18px; color: var(--primary);">Tổng cộng (Final):</span>
                <span id="finalTotalDisplay" style="font-size: 24px; font-weight: 800; color: var(--primary);"><?= number_format($total + 30000) ?>đ</span>
            </div>
            
            <button type="button" id="btnPlaceOrder" style="width: 100%; text-align: center; display: block; box-sizing: border-box; font-size: 16px; padding: 14px; margin-bottom: 16px; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; background: #4F46E5; color: white; transition: 0.2s;" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4F46E5'">Xác nhận đặt hàng</button>
            <a href="category.php" style="width: 100%; text-align: center; display: block; box-sizing: border-box; font-size: 16px; padding: 14px; margin-bottom: 24px; border-radius: 6px; font-weight: 600; text-decoration: none; border: 1px solid #4F46E5; background: transparent; color: #111827; transition: 0.2s;" onmouseover="this.style.background='#f1f1f7ff'" onmouseout="this.style.background='transparent'">Tiếp tục mua hàng</a>
            
            <div style="text-align: center;">
                <a href="cart.php" style="color: #111827; font-weight: 600; font-size: 14px; text-decoration: none;">&larr; Quay lại giỏ hàng</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const formatMoney = (amount) => {
        return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
    };

    const btnPlaceOrder = document.getElementById('btnPlaceOrder');
    const checkoutForm = document.getElementById('checkoutForm');
    const btnApplyPromo = document.getElementById('btnApplyPromo');
    const promoCodeInput = document.getElementById('promoCode');
    const discountRow = document.getElementById('discountRow');
    const discountCodeText = document.getElementById('discountCodeText');
    const discountAmountDisplay = document.getElementById('discountAmountDisplay');
    const finalTotalDisplay = document.getElementById('finalTotalDisplay');
    const subtotalDisplay = document.getElementById('subtotalDisplay');

    let currentDiscount = 0;
    const shippingFee = 30000;

    btnApplyPromo.addEventListener('click', async () => {
        const code = promoCodeInput.value.trim();
        if (!code) {
            alert('Vui lòng nhập mã khuyến mãi.');
            return;
        }

        btnApplyPromo.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btnApplyPromo.disabled = true;

        try {
            const res = await fetch('/fashion-shop/api/checkout/apply_promo.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ code: code })
            });
            const data = await res.json();
            
            if (data.success) {
                currentDiscount = parseFloat(data.discount);
                discountRow.style.display = 'flex';
                discountCodeText.textContent = data.code;
                discountAmountDisplay.textContent = new Intl.NumberFormat('vi-VN').format(currentDiscount);
                
                const subtotal = parseFloat(subtotalDisplay.dataset.value);
                const final = subtotal + shippingFee - currentDiscount;
                finalTotalDisplay.textContent = formatMoney(final);
                
                alert('Áp dụng mã khuyến mãi thành công!');
            } else {
                alert(data.message);
            }
        } catch (err) {
            alert('Lỗi kết nối máy chủ');
        } finally {
            btnApplyPromo.innerHTML = 'Áp dụng';
            btnApplyPromo.disabled = false;
        }
    });

    btnPlaceOrder.addEventListener('click', async () => {
        if (!checkoutForm.checkValidity()) {
            checkoutForm.reportValidity();
            return;
        }

        const formData = new FormData(checkoutForm);
        const orderData = Object.fromEntries(formData.entries());
        
        btnPlaceOrder.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        btnPlaceOrder.disabled = true;

        try {
            const res = await fetch('/fashion-shop/api/checkout/process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(orderData)
            });
            const data = await res.json();

            if (data.success) {
                // Thành công, điều hướng sang trang cám ơn hoặc lịch sử đơn hàng
                window.location.href = 'order_success.php?order_code=' + data.order_code;
            } else {
                alert('Lỗi: ' + data.message);
                btnPlaceOrder.innerHTML = 'Đặt Hàng Ngay';
                btnPlaceOrder.disabled = false;
            }
        } catch (err) {
            console.error(err);
            alert('Lỗi kết nối máy chủ!');
            btnPlaceOrder.innerHTML = 'Đặt Hàng Ngay';
            btnPlaceOrder.disabled = false;
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
