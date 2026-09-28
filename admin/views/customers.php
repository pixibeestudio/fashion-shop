<?php
require_once __DIR__ . '/../../app/controllers/CustomerController.php';
$customerController = new CustomerController();
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$pagination = $customerController->paginate($page);
$customers = $pagination['data'];
?>
<div class="page-header">
    <div class="page-title">Quản lý Khách hàng</div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Khách Hàng</th>
                <th>Liên hệ</th>
                <th>Ngày đăng ký</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($customers)): ?>
            <tr>
                <td colspan="6" style="text-align: center; color: #64748b; padding: 20px;">Chưa có khách hàng nào</td>
            </tr>
            <?php else: ?>
                <?php foreach ($customers as $c): ?>
                <tr>
                    <td><b>#<?= htmlspecialchars($c['id']) ?></b></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="avatar" style="width: 32px; height: 32px; font-size: 14px;">
                                <?= strtoupper(mb_substr($c['full_name'], 0, 1, 'UTF-8')) ?>
                            </div>
                            <strong><?= htmlspecialchars($c['full_name']) ?></strong>
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 13px;">
                            <i class="fas fa-envelope" style="color: #94a3b8; width: 15px;"></i> <?= htmlspecialchars($c['email']) ?><br>
                            <i class="fas fa-phone" style="color: #94a3b8; width: 15px;"></i> <?= htmlspecialchars($c['phone'] ?? 'Chưa cập nhật') ?>
                        </div>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></td>
                    <td>
                        <?php if ($c['status'] === 'active'): ?>
                            <span class="tag" style="background: #dcfce7; color: #166534;">Hoạt động</span>
                        <?php else: ?>
                            <span class="tag" style="background: #fee2e2; color: #991b1b;">Bị Khóa</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space: nowrap;">
                        <button class="btn-icon btn-view-customer" data-id="<?= $c['id'] ?>" title="Xem chi tiết" style="color: #64748b; border:none; background:transparent; cursor:pointer; font-size:16px; margin-right: 12px;"><i class="fas fa-eye"></i></button>
                        <button class="btn-icon btn-toggle-status" data-id="<?= $c['id'] ?>" data-status="<?= $c['status'] ?>" title="Khóa/Mở khóa" style="color: <?= $c['status'] === 'active' ? '#ef4444' : '#10b981' ?>; border:none; background:transparent; cursor:pointer; font-size:16px;">
                            <i class="fas <?= $c['status'] === 'active' ? 'fa-lock' : 'fa-lock-open' ?>"></i>
                        </button>
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
        <a href="?page=customers&p=<?= $i ?>" class="page-link <?= $i === $pagination['current_page'] ? 'active' : '' ?>" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; text-decoration: none; color: <?= $i === $pagination['current_page'] ? 'white' : 'var(--text-color)' ?>; background: <?= $i === $pagination['current_page'] ? 'var(--primary)' : 'white' ?>;"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<!-- Modal Xem Chi Tiết Khách Hàng -->
<div class="modal-overlay" id="viewCustomerModal">
    <div class="modal-content" style="max-width: 700px; width: 90%;">
        <div class="modal-header">
            <h2>Hồ sơ Khách Hàng</h2>
        </div>
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
            <div style="display: flex; gap: 20px; align-items: flex-start;">
                <div class="avatar" id="vcAvatar" style="width: 80px; height: 80px; font-size: 30px;">A</div>
                <div style="flex: 1;">
                    <h3 id="vcName" style="margin: 0 0 10px 0; font-size: 20px;">Tên Khách Hàng</h3>
                    <p style="margin-bottom: 5px; color: #475569;"><i class="fas fa-envelope" style="width:20px;"></i> <span id="vcEmail"></span></p>
                    <p style="margin-bottom: 5px; color: #475569;"><i class="fas fa-phone" style="width:20px;"></i> <span id="vcPhone"></span></p>
                    <p style="margin-bottom: 5px; color: #475569;"><i class="fas fa-calendar-alt" style="width:20px;"></i> Đăng ký: <span id="vcDate"></span></p>
                    <p style="margin-bottom: 0; color: #475569;"><i class="fas fa-info-circle" style="width:20px;"></i> Trạng thái: <strong id="vcStatus"></strong></p>
                </div>
            </div>
            
            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
            
            <h4 style="margin-bottom: 15px;">Danh sách sổ địa chỉ</h4>
            <div id="vcAddresses">
                <!-- Chứa các địa chỉ render bằng JS -->
            </div>
        </div>
        <div class="modal-footer" style="justify-content: space-between;">
            <div>
                <button type="button" class="btn-primary" id="btnEditCustomerInfo" style="background: var(--primary);"><i class="fas fa-pencil-alt"></i> Sửa TT</button>
                <button type="button" class="btn-primary" id="btnResetPassword" style="background: #eab308; color: #fff;"><i class="fas fa-key"></i> Đổi Mật Khẩu</button>
            </div>
            <button type="button" class="btn-cancel" id="btnCloseCustomerModal">Đóng</button>
        </div>
    </div>
</div>

<!-- Modal Sửa Thông Tin -->
<div class="modal-overlay" id="editCustomerModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>Chỉnh sửa Khách hàng</h2>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editCustomerId">
            <div class="form-group">
                <label>Họ và tên *</label>
                <input type="text" id="editCustomerName" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" id="editCustomerEmail" required>
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="text" id="editCustomerPhone">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btnCloseEditCustomerModal">Hủy</button>
            <button type="button" class="btn-primary" id="btnSaveCustomerInfo">Lưu thay đổi</button>
        </div>
    </div>
</div>

<!-- Modal Đổi Mật Khẩu -->
<div class="modal-overlay" id="resetPasswordModal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h2>Cấp lại Mật Khẩu</h2>
        </div>
        <div class="modal-body">
            <input type="hidden" id="resetPasswordCustomerId">
            <p style="margin-bottom: 15px; color: #475569; font-size: 14px;">Bạn đang cấp lại mật khẩu cho <strong id="resetPasswordCustomerName"></strong></p>
            <div class="form-group">
                <label>Mật khẩu mới *</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="newCustomerPassword" required style="flex: 1;">
                    <button type="button" class="btn-cancel" id="btnGeneratePassword" style="white-space: nowrap;"><i class="fas fa-random"></i> Tạo ngẫu nhiên</button>
                </div>
                <small style="color: #64748b; margin-top: 5px; display: block;">Hãy copy mật khẩu này và gửi cho Khách hàng.</small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btnCloseResetPasswordModal">Hủy</button>
            <button type="button" class="btn-primary" id="btnSaveNewPassword" style="background: #eab308; color: #fff;">Lưu mật khẩu mới</button>
        </div>
    </div>
</div>

<script src="/fashion-shop/assets/js/customers.js"></script>
