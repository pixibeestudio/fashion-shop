<?php
$pageTitle = 'Đặt hàng thành công - Fashion Shop';
require_once __DIR__ . '/includes/header.php';

$orderCode = $_GET['order_code'] ?? '';

if (!$orderCode) {
    header('Location: index.php');
    exit;
}
?>

<div class="store-wrapper" style="margin-top: 60px; margin-bottom: 100px; text-align: center;">
    <div style="max-width: 600px; margin: 0 auto; background: white; padding: 48px; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
        <div style="width: 80px; height: 80px; background: #dcfce7; color: #16a34a; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 24px;">
            <i class="fas fa-check"></i>
        </div>
        
        <h1 style="font-size: 28px; margin-bottom: 16px; color: var(--text-color);">Cảm ơn bạn đã đặt hàng!</h1>
        <p style="color: var(--text-muted); font-size: 16px; margin-bottom: 24px; line-height: 1.6;">
            Đơn hàng của bạn đã được tiếp nhận và đang trong quá trình xử lý. Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất để xác nhận đơn hàng.
        </p>
        
        <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 16px; margin-bottom: 32px; display: inline-block;">
            <span style="color: var(--text-muted); font-size: 14px; margin-right: 8px;">Mã đơn hàng:</span>
            <strong style="font-size: 20px; color: var(--primary); font-family: monospace;"><?= htmlspecialchars($orderCode) ?></strong>
        </div>
        
        <div style="display: flex; gap: 16px; justify-content: center;">
            <a href="index.php" class="btn-outline" style="padding: 12px 24px; font-weight: 600;">Về Trang Chủ</a>
            <?php if (isset($_SESSION['customer_id'])): ?>
            <a href="profile.php" class="btn-primary" style="padding: 12px 24px; font-weight: 600;">Xem Đơn Hàng</a>
            <?php else: ?>
            <a href="category.php" class="btn-primary" style="padding: 12px 24px; font-weight: 600;">Tiếp Tục Mua Sắm</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
