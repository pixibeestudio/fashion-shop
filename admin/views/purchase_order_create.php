<?php
require_once __DIR__ . '/../../app/controllers/PurchaseOrderController.php';
$poController = new PurchaseOrderController();
$data = $poController->getCreateFormData();
$suppliers = $data['suppliers'];
$variants = $data['variants'];
?>
<div class="page-header">
    <div class="page-title">Tạo Phiếu Nhập Kho</div>
    <a href="?page=purchase_orders" class="btn-cancel" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;"><i class="fas fa-arrow-left"></i> Trở về</a>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">Thông tin Nhà Cung Cấp</div>
    <div class="card-body">
        <div style="display: flex; gap: 15px; align-items: flex-end;">
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label>Chọn Nhà Cung Cấp *</label>
                <select id="poSupplier" required>
                    <option value="">-- Chọn Nhà Cung Cấp --</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['phone'] ?? 'Không có SĐT') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="button" class="btn-primary" id="btnOpenSupplierModal" style="height: 42px;"><i class="fas fa-plus"></i> Thêm mới</button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Chi tiết Hàng Hóa Nhập</div>
    <div class="card-body">
        <div style="display: flex; gap: 15px; align-items: flex-end; margin-bottom: 20px; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0;">
            <div class="form-group" style="flex: 2; margin-bottom: 0;">
                <label>Tìm Sản Phẩm (Phân loại)</label>
                <!-- Using a select for simplicity, but could be enhanced with Select2 or Autocomplete -->
                <select id="selectVariant">
                    <option value="">-- Chọn Sản phẩm nhập --</option>
                    <?php foreach ($variants as $v): ?>
                        <option value="<?= $v['id'] ?>" data-name="<?= htmlspecialchars($v['name']) ?>" data-sku="<?= htmlspecialchars($v['sku']) ?>" data-color="<?= htmlspecialchars($v['color']) ?>" data-size="<?= htmlspecialchars($v['size']) ?>">
                            [<?= htmlspecialchars($v['sku']) ?>] <?= htmlspecialchars($v['name']) ?> - <?= htmlspecialchars($v['color']) ?> - <?= htmlspecialchars($v['size']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label>Số lượng</label>
                <input type="number" id="inputQty" min="1" value="1">
            </div>
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label>Giá nhập (VNĐ)</label>
                <input type="number" id="inputPrice" min="0" value="0" step="1000">
            </div>
            <button type="button" class="btn-primary" id="btnAddItem" style="height: 42px; background: #10b981;"><i class="fas fa-plus"></i> Thêm vào phiếu</button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Sản phẩm / SKU</th>
                        <th>Phân loại</th>
                        <th>Số lượng</th>
                        <th>Giá nhập</th>
                        <th>Thành tiền</th>
                        <th>Xóa</th>
                    </tr>
                </thead>
                <tbody id="poTableBody">
                    <tr id="emptyRow">
                        <td colspan="7" style="text-align: center; color: #64748b; padding: 20px;">Chưa có sản phẩm nào trong phiếu.</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align: right; font-weight: bold; font-size: 16px;">Tổng Cộng:</td>
                        <td id="poTotalAmount" style="font-weight: bold; color: var(--primary); font-size: 18px;">0đ</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div style="margin-top: 30px; text-align: right;">
            <button type="button" class="btn-primary" id="btnSubmitPO" style="padding: 12px 24px; font-size: 16px;"><i class="fas fa-save"></i> Hoàn Tất Lập Phiếu</button>
        </div>
    </div>
</div>

<!-- Modal Thêm Nhà Cung Cấp -->
<div class="modal-overlay" id="supplierModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>Thêm Nhà Cung Cấp Mới</h2>
        </div>
        <div class="modal-body">
            <form id="supplierForm">
                <div class="form-group">
                    <label>Tên Nhà Cung Cấp *</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone">
                </div>
                <div class="form-group">
                    <label>Địa chỉ</label>
                    <textarea name="address" rows="3" style="resize: vertical;"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btnCloseSupplierModal">Hủy</button>
            <button type="button" class="btn-submit" id="btnSubmitSupplier">Lưu Nhà Cung Cấp</button>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="/fashion-shop/assets/js/purchase_orders.js"></script>
