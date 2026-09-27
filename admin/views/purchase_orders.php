<?php
require_once __DIR__ . '/../../app/controllers/PurchaseOrderController.php';
$poController = new PurchaseOrderController();
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$pagination = $poController->paginate($page);
$purchaseOrders = $pagination['data'];
?>
<div class="page-header">
    <div class="page-title">Lịch sử Nhập kho</div>
    <a href="?page=purchase_order_create" class="btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;"><i class="fas fa-plus"></i> Tạo Phiếu Nhập Mới</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Mã Phiếu</th>
                <th>Nhà Cung Cấp</th>
                <th>Ngày Nhập</th>
                <th>Tổng Tiền</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($purchaseOrders)): ?>
            <tr>
                <td colspan="6" style="text-align: center; color: #64748b; padding: 20px;">Chưa có phiếu nhập kho nào</td>
            </tr>
            <?php else: ?>
                <?php foreach ($purchaseOrders as $po): ?>
                <tr>
                    <td><b>#PO-<?= htmlspecialchars($po['id']) ?></b></td>
                    <td><?= htmlspecialchars($po['supplier_name']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($po['created_at'])) ?></td>
                    <td style="font-weight: 600; color: var(--primary);"><?= number_format($po['total_amount']) ?>đ</td>
                    <td>
                        <?php if ($po['status'] === 'completed'): ?>
                            <span class="tag" style="background: #dcfce7; color: #166534;">Hoàn thành</span>
                        <?php else: ?>
                            <span class="tag gray"><?= htmlspecialchars($po['status']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space: nowrap;">
                        <button class="btn-icon btn-view-po" data-id="<?= $po['id'] ?>" title="Xem chi tiết" style="color: #64748b; border:none; background:transparent; cursor:pointer; font-size:16px; margin-right: 12px;"><i class="fas fa-eye"></i></button>
                        <a href="?page=purchase_order_edit&id=<?= $po['id'] ?>" class="btn-icon" title="Sửa phiếu" style="color: var(--primary); text-decoration:none; margin-right: 12px; font-size:16px;"><i class="fas fa-pencil-alt"></i></a>
                        <button class="btn-icon btn-delete-po" data-id="<?= $po['id'] ?>" title="Xóa phiếu" style="color: var(--danger); border:none; background:transparent; cursor:pointer; font-size:16px;"><i class="fas fa-trash-alt"></i></button>
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
        <a href="?page=purchase_orders&p=<?= $i ?>" class="page-link <?= $i === $pagination['current_page'] ? 'active' : '' ?>" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; text-decoration: none; color: <?= $i === $pagination['current_page'] ? 'white' : 'var(--text-color)' ?>; background: <?= $i === $pagination['current_page'] ? 'var(--primary)' : 'white' ?>;"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<!-- Modal Xem Chi Tiết Phiếu Nhập -->
<div class="modal-overlay" id="viewPoModal">
    <div class="modal-content" style="max-width: 800px; width: 90%;">
        <div class="modal-header">
            <h2>Chi tiết Phiếu Nhập Kho</h2>
        </div>
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 300px;">
                    <div style="margin-bottom: 10px;"><strong>Mã Phiếu:</strong> <span id="vpoId"></span></div>
                    <div style="margin-bottom: 10px;"><strong>Ngày lập:</strong> <span id="vpoDate"></span></div>
                    <div style="margin-bottom: 10px;"><strong>Nhân viên:</strong> <span id="vpoUser"></span></div>
                    <div style="margin-bottom: 10px;"><strong>Trạng thái:</strong> <span id="vpoStatus"></span></div>
                </div>
                <div style="flex: 1; min-width: 300px; background: #f8fafc; padding: 15px; border-radius: 8px;">
                    <div style="margin-bottom: 5px;"><strong>Nhà Cung Cấp:</strong> <span id="vpoSupplierName"></span></div>
                    <div style="margin-bottom: 5px;"><strong>Số điện thoại:</strong> <span id="vpoSupplierPhone"></span></div>
                    <div style="margin-bottom: 5px;"><strong>Địa chỉ:</strong> <span id="vpoSupplierAddress"></span></div>
                </div>
            </div>
            
            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
            
            <strong>Chi tiết Hàng Hóa:</strong>
            <div class="table-container" style="margin-top: 10px;">
                <table>
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Phân loại</th>
                            <th>Giá nhập</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody id="vpoItems">
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align: right; font-weight: bold;">Tổng cộng:</td>
                            <td id="vpoTotal" style="font-weight: bold; color: var(--primary);">0đ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btnCloseViewPoModal">Đóng</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // View Modal Logic
    const viewModal = document.getElementById('viewPoModal');
    document.querySelectorAll('.btn-view-po').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            try {
                const response = await fetch(`/fashion-shop/api/admin/purchase_orders/get.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const po = result.data;
                    document.getElementById('vpoId').textContent = '#PO-' + po.id;
                    document.getElementById('vpoDate').textContent = po.created_at;
                    document.getElementById('vpoUser').textContent = po.user_name || 'System';
                    document.getElementById('vpoStatus').textContent = po.status === 'completed' ? 'Hoàn thành' : po.status;
                    
                    document.getElementById('vpoSupplierName').textContent = po.supplier_name;
                    document.getElementById('vpoSupplierPhone').textContent = po.supplier_phone || '-';
                    document.getElementById('vpoSupplierAddress').textContent = po.supplier_address || '-';
                    
                    // Render items
                    const itemsBody = document.getElementById('vpoItems');
                    itemsBody.innerHTML = '';
                    if (po.items && po.items.length > 0) {
                        po.items.forEach(item => {
                            const subtotal = item.quantity * item.unit_price;
                            itemsBody.innerHTML += `
                                <tr>
                                    <td>${item.product_name}</td>
                                    <td>SKU: ${item.sku}<br><small style="color:#64748b;">${item.color} - ${item.size}</small></td>
                                    <td>${Number(item.unit_price).toLocaleString()}đ</td>
                                    <td>${item.quantity}</td>
                                    <td style="font-weight:500;">${Number(subtotal).toLocaleString()}đ</td>
                                </tr>
                            `;
                        });
                    }
                    
                    document.getElementById('vpoTotal').textContent = Number(po.total_amount).toLocaleString() + 'đ';

                    viewModal.classList.add('active');
                } else {
                    alert('Lỗi: ' + result.message);
                }
            } catch (error) {
                alert('Lỗi kết nối máy chủ!');
            }
        });
    });

    document.getElementById('btnCloseViewPoModal').addEventListener('click', () => {
        viewModal.classList.remove('active');
    });

    // Delete Logic
    document.querySelectorAll('.btn-delete-po').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (confirm('CẢNH BÁO: Xóa phiếu nhập kho sẽ làm TỤT số lượng tồn kho của các sản phẩm tương ứng. Bạn có chắc chắn muốn xóa?')) {
                const id = this.getAttribute('data-id');
                try {
                    const response = await fetch('/fashion-shop/api/admin/purchase_orders/delete.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
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
            }
        });
    });
});
</script>
