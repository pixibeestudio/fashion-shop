<?php
require_once __DIR__ . '/../../app/controllers/UserController.php';
$userController = new UserController();
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$pagination = $userController->paginate($page);
$users = $pagination['data'];
$roles = $userController->getRoles();
?>
<div class="page-header">
    <div class="page-title">Quản lý Nhân sự</div>
    <button class="btn-primary" id="btnAddNewUser"><i class="fas fa-plus"></i> Thêm Nhân viên</button>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nhân viên</th>
                <th>Liên hệ</th>
                <th>Chức vụ</th>
                <th>Ngày tạo</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
            <tr>
                <td colspan="7" style="text-align: center; color: #64748b; padding: 20px;">Chưa có nhân viên nào</td>
            </tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><b>#<?= htmlspecialchars($u['id']) ?></b></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="avatar" style="width: 32px; height: 32px; font-size: 14px;">
                                <?= strtoupper(mb_substr($u['full_name'], 0, 1, 'UTF-8')) ?>
                            </div>
                            <strong><?= htmlspecialchars($u['full_name']) ?></strong>
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 13px;">
                            <i class="fas fa-envelope" style="color: #94a3b8; width: 15px;"></i> <?= htmlspecialchars($u['email']) ?><br>
                            <i class="fas fa-phone" style="color: #94a3b8; width: 15px;"></i> <?= htmlspecialchars($u['phone'] ?? '') ?>
                        </div>
                    </td>
                    <td>
                        <?php 
                        $roleColors = [
                            1 => ['bg' => '#fee2e2', 'color' => '#991b1b'], // Admin
                            2 => ['bg' => '#ffedd5', 'color' => '#9a3412'], // Thủ kho
                            3 => ['bg' => '#e0e7ff', 'color' => '#3730a3'], // Thu ngân
                        ];
                        $rc = $roleColors[$u['role_id']] ?? ['bg' => '#f1f5f9', 'color' => '#475569'];
                        ?>
                        <span class="tag" style="background: <?= $rc['bg'] ?>; color: <?= $rc['color'] ?>;">
                            <?= htmlspecialchars($u['role_name']) ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php if ($u['status'] === 'active'): ?>
                            <span class="tag" style="background: #dcfce7; color: #166534;">Hoạt động</span>
                        <?php else: ?>
                            <span class="tag" style="background: #fef2f2; color: #991b1b;">Bị Khóa</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space: nowrap;">
                        <button class="btn-icon btn-edit-user" 
                            data-id="<?= $u['id'] ?>" 
                            data-name="<?= htmlspecialchars($u['full_name']) ?>"
                            data-email="<?= htmlspecialchars($u['email']) ?>"
                            data-phone="<?= htmlspecialchars($u['phone']) ?>"
                            data-role="<?= $u['role_id'] ?>"
                            title="Sửa thông tin" style="color: var(--primary); border:none; background:transparent; cursor:pointer; font-size:16px; margin-right: 12px;">
                            <i class="fas fa-pencil-alt"></i>
                        </button>

                        <button class="btn-icon btn-reset-password" 
                            data-id="<?= $u['id'] ?>" 
                            data-name="<?= htmlspecialchars($u['full_name']) ?>"
                            title="Cấp lại mật khẩu" style="color: #eab308; border:none; background:transparent; cursor:pointer; font-size:16px; margin-right: 12px;">
                            <i class="fas fa-key"></i>
                        </button>

                        <?php if ($u['id'] != ($_SESSION['admin_id'] ?? 0)): ?>
                        <button class="btn-icon btn-toggle-status" 
                            data-id="<?= $u['id'] ?>" 
                            data-status="<?= $u['status'] ?>" 
                            title="Khóa/Mở khóa" style="color: <?= $u['status'] === 'active' ? '#ef4444' : '#10b981' ?>; border:none; background:transparent; cursor:pointer; font-size:16px; margin-right: 12px;">
                            <i class="fas <?= $u['status'] === 'active' ? 'fa-lock' : 'fa-lock-open' ?>"></i>
                        </button>
                        
                        <button class="btn-icon btn-delete-user" 
                            data-id="<?= $u['id'] ?>" 
                            title="Xóa tài khoản" style="color: #ef4444; border:none; background:transparent; cursor:pointer; font-size:16px;">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php else: ?>
                            <span style="display:inline-block; width: 16px;"></span>
                        <?php endif; ?>
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
        <a href="?page=users&p=<?= $i ?>" class="page-link <?= $i === $pagination['current_page'] ? 'active' : '' ?>" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; text-decoration: none; color: <?= $i === $pagination['current_page'] ? 'white' : 'var(--text-color)' ?>; background: <?= $i === $pagination['current_page'] ? 'var(--primary)' : 'white' ?>;"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<!-- Modal Thêm/Sửa Nhân viên -->
<div class="modal-overlay" id="userModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2 id="userModalTitle">Thêm Nhân viên</h2>
        </div>
        <div class="modal-body">
            <input type="hidden" id="userId">
            <div class="form-group">
                <label>Họ và tên *</label>
                <input type="text" id="userName" required>
            </div>
            <div class="form-group">
                <label>Chức vụ (Role) *</label>
                <select id="userRole" required>
                    <option value="">-- Chọn chức vụ --</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small style="color: #64748b; font-style: italic;">* Chỉ có 1 Admin duy nhất trong hệ thống.</small>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" id="userEmail" required>
            </div>
            <div class="form-group">
                <label>Số điện thoại *</label>
                <input type="text" id="userPhone" required>
            </div>
            <div class="form-group" id="passwordGroup">
                <label>Mật khẩu khởi tạo * (Phải có 8 ký tự, bao gồm chữ HOA, chữ thường, số, ký tự ĐB)</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="userPassword" style="flex: 1;">
                    <button type="button" class="btn-cancel" id="btnGenInitialPass"><i class="fas fa-random"></i> Sinh ngẫu nhiên</button>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btnCloseUserModal">Hủy</button>
            <button type="button" class="btn-primary" id="btnSaveUser">Lưu nhân viên</button>
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
            <input type="hidden" id="resetPasswordUserId">
            <p style="margin-bottom: 15px; color: #475569; font-size: 14px;">Bạn đang cấp lại mật khẩu cho <strong id="resetPasswordUserName"></strong></p>
            <div class="form-group">
                <label>Mật khẩu mới *</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="newPassword" required style="flex: 1;">
                    <button type="button" class="btn-cancel" id="btnGeneratePassword" style="white-space: nowrap;"><i class="fas fa-random"></i> Sinh ngẫu nhiên</button>
                </div>
                <small style="color: #64748b; margin-top: 5px; display: block;">Mật khẩu mới phải đáp ứng chuẩn bảo mật (Hoa, thường, số, ký tự đặc biệt).</small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btnCloseResetPasswordModal">Hủy</button>
            <button type="button" class="btn-primary" id="btnSaveNewPassword" style="background: #eab308; color: #fff;">Lưu mật khẩu mới</button>
        </div>
    </div>
</div>

<script src="/fashion-shop/assets/js/users.js"></script>
