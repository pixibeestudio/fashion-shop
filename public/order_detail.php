<?php
$pageTitle = 'Chi tiết đơn hàng - Fashion Shop';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../app/config/Database.php';

// Auth Check
if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

$customerId = $_SESSION['customer_id'];
$orderId = $_GET['id'] ?? null;

if (!$orderId) {
    header('Location: profile.php');
    exit;
}

$db = Database::getInstance()->getConnection();

// Fetch Order Info
$stmt = $db->prepare("SELECT * FROM orders WHERE id = :id AND customer_id = :cid");
$stmt->execute(['id' => $orderId, 'cid' => $customerId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<div style='text-align:center; margin-top:100px; margin-bottom:100px;'><h1>Không tìm thấy đơn hàng</h1></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch Order Items
$stmtItems = $db->prepare("SELECT * FROM order_items WHERE order_id = :oid");
$stmtItems->execute(['oid' => $orderId]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

// Fetch Promotion if any
$promotion = null;
if ($order['promotion_id']) {
    $stmtPromo = $db->prepare("SELECT code FROM promotions WHERE id = :id");
    $stmtPromo->execute(['id' => $order['promotion_id']]);
    $promotion = $stmtPromo->fetchColumn();
}

function renderStatus($status) {
    switch ($status) {
        case 'pending': return 'Chờ xử lý';
        case 'processing': return 'Đang xử lý';
        case 'shipped': return 'Đang giao';
        case 'delivered': return 'Đã giao';
        case 'cancelled': return 'Đã hủy';
        default: return ucfirst($status);
    }
}
?>

<div class="store-wrapper" style="margin-top: 40px; margin-bottom: 80px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1 style="font-size: 24px; margin: 0; color: var(--text-color);">Chi tiết đơn hàng #<?= htmlspecialchars($order['order_number']) ?></h1>
        <a href="profile.php" style="color: var(--primary); text-decoration: none; font-weight: 600;"><i class="fas fa-arrow-left"></i> Quay lại</a>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px; align-items: start;">
        <!-- Left: Items -->
        <div style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 24px;">
            <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">Sản phẩm đã mua</h3>
            
            <?php foreach ($items as $item): ?>
                <div style="display: flex; gap: 16px; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px; align-items: center;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 15px; margin-bottom: 4px;"><?= htmlspecialchars($item['product_name']) ?></div>
                        <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Mã SP: <?= htmlspecialchars($item['sku']) ?> | Phân loại: <?= htmlspecialchars($item['color']) ?> - <?= htmlspecialchars($item['size']) ?></div>
                        <div style="font-weight: 600; color: var(--primary); font-size: 14px;"><?= number_format($item['unit_price']) ?>đ</div>
                    </div>
                    <div style="font-weight: 600; color: var(--text-color);">
                        x<?= $item['quantity'] ?>
                    </div>
                    <div style="font-weight: 700; color: var(--primary); font-size: 16px; min-width: 100px; text-align: right;">
                        <?= number_format($item['subtotal']) ?>đ
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Right: Summary & Info -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Order Summary -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 24px;">
                <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">Tóm tắt đơn hàng</h3>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; color: var(--text-muted); font-size: 14px;">
                    <span>Tạm tính:</span>
                    <span style="color: var(--text-color); font-weight: 600;"><?= number_format($order['subtotal']) ?>đ</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; color: var(--text-muted); font-size: 14px;">
                    <span>Phí vận chuyển:</span>
                    <span style="color: var(--text-color); font-weight: 600;"><?= number_format($order['shipping_fee']) ?>đ</span>
                </div>
                <?php if ($order['discount'] > 0): ?>
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; color: var(--danger); font-size: 14px;">
                    <span>Khuyến mãi (<?= htmlspecialchars($promotion) ?>):</span>
                    <span style="font-weight: 600;">-<?= number_format($order['discount']) ?>đ</span>
                </div>
                <?php endif; ?>
                
                <div style="border-top: 1px dashed #e2e8f0; padding-top: 16px; margin-top: 16px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600;">Thành tiền:</span>
                    <span style="font-size: 24px; font-weight: 800; color: var(--primary);"><?= number_format($order['total']) ?>đ</span>
                </div>
            </div>

            <!-- Shipping Info -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 24px;">
                <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">Thông tin giao hàng</h3>
                
                <div style="margin-bottom: 12px;">
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Ngày đặt hàng</div>
                    <div style="font-weight: 600;"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
                </div>
                <div style="margin-bottom: 12px;">
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Trạng thái vận chuyển</div>
                    <div style="font-weight: 600; color: var(--primary);"><?= renderStatus($order['shipping_status']) ?></div>
                </div>
                <div style="margin-bottom: 12px;">
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Trạng thái thanh toán</div>
                    <div style="font-weight: 600; color: <?= $order['payment_status'] === 'paid' ? 'var(--success)' : 'var(--text-muted)' ?>;"><?= $order['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' ?></div>
                </div>
                <div style="margin-bottom: 12px;">
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Phương thức thanh toán</div>
                    <div style="font-weight: 600;"><?= strtoupper($order['payment_method']) ?></div>
                </div>
                <div style="margin-bottom: 12px;">
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Địa chỉ giao hàng</div>
                    <div style="font-weight: 600; line-height: 1.5;"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></div>
                </div>
                <?php if (!empty($order['notes'])): ?>
                <div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Ghi chú</div>
                    <div style="font-style: italic; color: #475569;"><?= nl2br(htmlspecialchars($order['notes'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
