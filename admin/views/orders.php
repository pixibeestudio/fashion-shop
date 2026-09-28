<?php
require_once __DIR__ . '/../../app/controllers/OrderController.php';
$orderController = new OrderController();
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$pagination = $orderController->paginate($page);
$orders = $pagination['data'];
?>
<div class="page-header">
    <div class="page-title">Quản lý Đơn hàng</div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Mã Đơn</th>
                <th>Khách Hàng</th>
                <th>Ngày Đặt</th>
                <th>Tổng Tiền</th>
                <th>Thanh Toán</th>
                <th>Giao Hàng</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
            <tr>
                <td colspan="7" style="text-align: center; color: #64748b; padding: 20px;">Chưa có đơn hàng nào</td>
            </tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><b><?= htmlspecialchars($order['order_number']) ?></b></td>
                    <td>
                        <?= htmlspecialchars($order['full_name']) ?><br>
                        <small style="color: #64748b;"><?= htmlspecialchars($order['phone'] ?? '') ?></small>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                    <td style="font-weight: 600; color: var(--primary);"><?= number_format($order['total']) ?>đ</td>
                    <td>
                        <?php 
                        $payColor = $order['payment_status'] === 'paid' ? '#166534' : ($order['payment_status'] === 'refunded' ? '#991b1b' : '#b45309');
                        $payBg = $order['payment_status'] === 'paid' ? '#dcfce7' : ($order['payment_status'] === 'refunded' ? '#fee2e2' : '#fef3c7');
                        $payText = $order['payment_status'] === 'paid' ? 'Đã thanh toán' : ($order['payment_status'] === 'refunded' ? 'Hoàn tiền' : 'Chưa thanh toán');
                        ?>
                        <span class="tag" style="background: <?= $payBg ?>; color: <?= $payColor ?>;"><?= $payText ?></span>
                    </td>
                    <td>
                        <?php 
                        $shipColors = [
                            'pending' => ['bg' => '#f1f5f9', 'color' => '#475569', 'text' => 'Chờ xử lý'],
                            'processing' => ['bg' => '#dbeafe', 'color' => '#1e40af', 'text' => 'Đang xử lý'],
                            'shipped' => ['bg' => '#fef3c7', 'color' => '#b45309', 'text' => 'Đang giao'],
                            'delivered' => ['bg' => '#dcfce7', 'color' => '#166534', 'text' => 'Thành công'],
                            'cancelled' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'text' => 'Đã Hủy']
                        ];
                        $ship = $shipColors[$order['shipping_status']] ?? $shipColors['pending'];
                        ?>
                        <span class="tag" style="background: <?= $ship['bg'] ?>; color: <?= $ship['color'] ?>;"><?= $ship['text'] ?></span>
                    </td>
                    <td style="white-space: nowrap;">
                        <a href="?page=order_detail&id=<?= $order['id'] ?>" class="btn-icon" title="Xem & Cập nhật" style="color: var(--primary); text-decoration:none; font-size:16px;">
                            <i class="fas fa-eye"></i> / <i class="fas fa-pencil-alt"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination UI -->
<?php if ($pagination['total'] > 10): ?>
<div class="pagination" style="display: flex; justify-content: flex-start; margin-top: 15px; gap: 5px;">
    <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
        <a href="?page=orders&p=<?= $i ?>" class="page-link <?= $i === $pagination['current_page'] ? 'active' : '' ?>" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; text-decoration: none; color: <?= $i === $pagination['current_page'] ? 'white' : 'var(--text-color)' ?>; background: <?= $i === $pagination['current_page'] ? 'var(--primary)' : 'white' ?>;"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
