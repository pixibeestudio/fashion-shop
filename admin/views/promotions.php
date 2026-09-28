<?php
require_once __DIR__ . '/../../app/controllers/PromotionController.php';

$controller = new PromotionController();
$page = $_GET['p'] ?? 1;
$result = $controller->paginate($page);
$promotions = $result['data'];
$totalPages = $result['pages'];
$currentPage = $result['current_page'];
$now = new DateTime();
?>

<div class="page-header">
    <div class="page-title">Quản lý Khuyến mãi</div>
    <button class="btn-primary" id="btnShowAddModal"><i class="fas fa-plus"></i> Thêm Khuyến mãi</button>
</div>

<div class="table-container">
    <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>MÃ KHUYẾN MÃI</th>
                    <th>LOẠI GIẢM</th>
                    <th>GIÁ TRỊ</th>
                    <th>THỜI GIAN ÁP DỤNG</th>
                    <th>TRẠNG THÁI</th>
                    <th>THAO TÁC</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($promotions)): ?>
                    <tr><td colspan="7" style="text-align: center; color: #64748b; padding: 20px;">Chưa có mã khuyến mãi nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($promotions as $promo): 
                        $start = new DateTime($promo['start_date']);
                        $end = new DateTime($promo['end_date']);
                        
                        // Xử lý status hiển thị
                        $statusText = '';
                        $statusClass = '';
                        $isLocked = $promo['status'] === 'disabled';
                        
                        if ($isLocked) {
                            $statusText = 'Bị vô hiệu hóa';
                            $statusClass = 'disabled';
                        } else if ($end < $now) {
                            $statusText = 'Đã hết hạn';
                            $statusClass = 'expired';
                        } else if ($start > $now) {
                            $statusText = 'Sắp tới';
                            $statusClass = 'upcoming';
                        } else {
                            $statusText = 'Đang diễn ra';
                            $statusClass = 'active';
                        }
                    ?>
                    <tr>
                        <td><b>#<?= htmlspecialchars($promo['id']) ?></b></td>
                        <td><strong style="color: var(--primary);"><?= htmlspecialchars($promo['code']) ?></strong></td>
                        <td>
                            <?php if ($promo['discount_type'] === 'percent'): ?>
                                <span class="tag" style="background:#e0e7ff; color:#3730a3;">Phần trăm (%)</span>
                            <?php else: ?>
                                <span class="tag" style="background:#dcfce7; color:#166534;">Cố định (VNĐ)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="color: #ef4444;">
                            <?php 
                            if ($promo['discount_type'] === 'percent') {
                                echo number_format($promo['discount_value'], 0) . '%';
                            } else {
                                echo number_format($promo['discount_value'], 0) . ' đ';
                            }
                            ?>
                            </strong>
                        </td>
                        <td style="font-size: 13px;">
                            <div style="color: #64748b; margin-bottom: 4px;"><i class="far fa-calendar-alt"></i> Từ: <b><?= $start->format('d/m/Y H:i') ?></b></div>
                            <div style="color: #64748b;"><i class="far fa-calendar-check"></i> Đến: <b><?= $end->format('d/m/Y H:i') ?></b></div>
                        </td>
                        <td>
                            <?php
                            $badgeStyles = [
                                'disabled' => 'background: #fef2f2; color: #991b1b;',
                                'expired' => 'background: #f1f5f9; color: #475569;',
                                'upcoming' => 'background: #fef9c3; color: #854d0e;',
                                'active' => 'background: #dcfce7; color: #166534;'
                            ];
                            $bStyle = $badgeStyles[$statusClass] ?? '';
                            ?>
                            <span class="tag" style="<?= $bStyle ?>"><?= $statusText ?></span>
                        </td>
                        <td style="white-space: nowrap;">
                            <button class="btn-icon btn-edit-promo" 
                                data-id="<?= $promo['id'] ?>"
                                data-code="<?= htmlspecialchars($promo['code']) ?>"
                                data-type="<?= $promo['discount_type'] ?>"
                                data-value="<?= $promo['discount_value'] ?>"
                                data-start="<?= $start->format('Y-m-d\TH:i') ?>"
                                data-end="<?= $end->format('Y-m-d\TH:i') ?>"
                                title="Sửa" style="color: var(--primary); border:none; background:transparent; cursor:pointer; font-size:16px; margin-right: 12px;">
                                <i class="fas fa-pencil-alt"></i>
                            </button>

                            <button class="btn-icon btn-toggle-status" 
                                data-id="<?= $promo['id'] ?>" 
                                data-status="<?= $promo['status'] ?>" 
                                title="<?= $isLocked ? 'Mở khóa' : 'Khóa/Vô hiệu hóa' ?>" 
                                style="color: <?= !$isLocked ? '#ef4444' : '#10b981' ?>; border:none; background:transparent; cursor:pointer; font-size:16px;">
                                <i class="fas <?= !$isLocked ? 'fa-lock' : 'fa-lock-open' ?>"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=promotions&p=<?= $i ?>" class="<?= $i == $currentPage ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Thêm/Sửa Promo -->
<div class="modal-overlay" id="promoModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2 id="modalTitle">Thêm Khuyến Mãi</h2>
        </div>
        <div class="modal-body">
            <input type="hidden" id="promoId">
            <div class="form-group">
                <label>Mã Khuyến Mãi (CODE) *</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="promoCode" required style="flex: 1; text-transform: uppercase;">
                    <button type="button" class="btn-cancel" id="btnGenCode" style="white-space: nowrap;"><i class="fas fa-random"></i> Sinh mã</button>
                </div>
            </div>
            <div class="form-group">
                <label>Loại Giảm Giá *</label>
                <select id="promoType" required>
                    <option value="percent">Giảm theo phần trăm (%)</option>
                    <option value="fixed">Giảm cố định (VNĐ)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Giá Trị Giảm *</label>
                <input type="number" id="promoValue" required min="1">
            </div>
            <div class="form-group">
                <label>Bắt đầu từ *</label>
                <input type="datetime-local" id="promoStart" required>
            </div>
            <div class="form-group">
                <label>Kết thúc vào *</label>
                <input type="datetime-local" id="promoEnd" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btnClosePromoModal">Hủy</button>
            <button type="button" class="btn-primary" id="btnSavePromo">Lưu</button>
        </div>
    </div>
</div>

<script src="/fashion-shop/assets/js/promotions.js"></script>
