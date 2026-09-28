<?php
require_once __DIR__ . '/../../app/controllers/OrderController.php';
$orderController = new OrderController();

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "Thiếu ID Đơn hàng.";
    exit;
}

$orderModel = new Order();
$order = $orderModel->getById($id);

if (!$order) {
    echo "Không tìm thấy Đơn hàng.";
    exit;
}

$items = $order['items'];
?>
<div class="page-header">
    <div class="page-title">Chi tiết Đơn hàng <b>#<?= htmlspecialchars($order['order_number']) ?></b></div>
    <a href="?page=orders" class="btn-cancel" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;"><i class="fas fa-arrow-left"></i> Trở về</a>
</div>

<div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
    
    <!-- Cột trái: Chi tiết sản phẩm -->
    <div class="card" style="flex: 2; min-width: 600px;">
        <div class="card-header">Sản phẩm khách đặt</div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Sản phẩm</th>
                            <th>Phân loại</th>
                            <th>Đơn giá</th>
                            <th>SL</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <strong><?= htmlspecialchars($item['product_name']) ?></strong><br>
                                <small style="color: #64748b;">SKU: <?= htmlspecialchars($item['sku']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($item['color']) ?> - <?= htmlspecialchars($item['size']) ?></td>
                            <td><?= number_format($item['unit_price']) ?>đ</td>
                            <td><?= $item['quantity'] ?></td>
                            <td style="font-weight: 500;"><?= number_format($item['subtotal']) ?>đ</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tổng kết tiền -->
            <div style="margin-top: 20px; background: #f8fafc; padding: 15px; border-radius: 8px; max-width: 400px; margin-left: auto;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span style="color: #64748b;">Tạm tính:</span>
                    <strong><?= number_format($order['subtotal']) ?>đ</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span style="color: #64748b;">Phí vận chuyển:</span>
                    <strong><?= number_format($order['shipping_fee']) ?>đ</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span style="color: #64748b;">Giảm giá:</span>
                    <strong style="color: #ef4444;">-<?= number_format($order['discount']) ?>đ</strong>
                </div>
                <hr style="border:0; border-top: 1px dashed #cbd5e1; margin: 10px 0;">
                <div style="display: flex; justify-content: space-between; font-size: 18px;">
                    <strong>Tổng cộng:</strong>
                    <strong style="color: var(--primary);"><?= number_format($order['total']) ?>đ</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Cột phải: Khách hàng & Cập nhật trạng thái -->
    <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column; gap: 20px;">
        
        <div class="card">
            <div class="card-header">Thông tin Khách hàng</div>
            <div class="card-body">
                <p style="margin-bottom: 10px;"><strong>Họ tên:</strong> <?= htmlspecialchars($order['full_name']) ?></p>
                <p style="margin-bottom: 10px;"><strong>Điện thoại:</strong> <?= htmlspecialchars($order['phone'] ?? 'Chưa cung cấp') ?></p>
                <p style="margin-bottom: 10px;"><strong>Email:</strong> <?= htmlspecialchars($order['email'] ?? 'Chưa cung cấp') ?></p>
                <p style="margin-bottom: 0;"><strong>Địa chỉ giao:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Trạng thái Đơn hàng</div>
            <div class="card-body">
                <div class="form-group">
                    <label>Trạng thái Giao hàng</label>
                    <select id="shippingStatus">
                        <option value="pending" <?= $order['shipping_status'] == 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                        <option value="processing" <?= $order['shipping_status'] == 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                        <option value="shipped" <?= $order['shipping_status'] == 'shipped' ? 'selected' : '' ?>>Đang giao hàng</option>
                        <option value="delivered" <?= $order['shipping_status'] == 'delivered' ? 'selected' : '' ?>>Giao thành công</option>
                        <option value="cancelled" <?= $order['shipping_status'] == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Trạng thái Thanh toán</label>
                    <select id="paymentStatus">
                        <option value="unpaid" <?= $order['payment_status'] == 'unpaid' ? 'selected' : '' ?>>Chưa thanh toán</option>
                        <option value="paid" <?= $order['payment_status'] == 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                        <option value="refunded" <?= $order['payment_status'] == 'refunded' ? 'selected' : '' ?>>Hoàn tiền</option>
                    </select>
                    <small style="color: #64748b; display: block; margin-top: 5px;">Phương thức: <?= strtoupper($order['payment_method']) ?></small>
                </div>

                <div class="form-group">
                    <label>Ghi chú của Admin</label>
                    <textarea id="adminNotes" rows="3"><?= htmlspecialchars($order['notes'] ?? '') ?></textarea>
                </div>

                <p id="cancelWarning" style="color: var(--danger); font-size: 13px; display: none; margin-bottom: 10px;">
                    Lưu ý: Hủy đơn hàng sẽ tự động hoàn trả số lượng lại cho Tồn kho!
                </p>

                <button class="btn-primary" id="btnUpdateStatus" style="width: 100%;">Cập nhật Trạng thái</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const shippingStatusEl = document.getElementById('shippingStatus');
    const paymentStatusEl = document.getElementById('paymentStatus');
    const adminNotesEl = document.getElementById('adminNotes');
    const btnUpdateStatus = document.getElementById('btnUpdateStatus');
    const cancelWarning = document.getElementById('cancelWarning');

    const originalStatus = '<?= $order['shipping_status'] ?>';

    // Show warning if trying to cancel
    shippingStatusEl.addEventListener('change', () => {
        if (shippingStatusEl.value === 'cancelled' && originalStatus !== 'cancelled') {
            cancelWarning.style.display = 'block';
        } else {
            cancelWarning.style.display = 'none';
        }
    });

    btnUpdateStatus.addEventListener('click', async () => {
        if (shippingStatusEl.value === 'cancelled' && originalStatus !== 'cancelled') {
            if (!confirm('Bạn có chắc chắn muốn HỦY đơn hàng này? Thao tác này sẽ hoàn trả Tồn kho.')) return;
        }

        const payload = {
            id: <?= $order['id'] ?>,
            shipping_status: shippingStatusEl.value,
            payment_status: paymentStatusEl.value,
            notes: adminNotesEl.value
        };

        try {
            const response = await fetch('/fashion-shop/api/admin/orders/update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert('Lỗi: ' + result.message);
            }
        } catch (error) {
            alert('Lỗi kết nối máy chủ!');
        }
    });
});
</script>
