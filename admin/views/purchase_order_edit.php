<?php
require_once __DIR__ . '/../../app/controllers/PurchaseOrderController.php';
$poController = new PurchaseOrderController();

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "Thiếu ID Phiếu Nhập.";
    exit;
}

$poModel = new PurchaseOrder();
$po = $poModel->getById($id);

if (!$po) {
    echo "Không tìm thấy Phiếu Nhập.";
    exit;
}

$data = $poController->getCreateFormData();
$suppliers = $data['suppliers'];
$variants = $data['variants'];
?>
<div class="page-header">
    <div class="page-title">Sửa Phiếu Nhập Kho #PO-<?= htmlspecialchars($po['id']) ?></div>
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
                        <option value="<?= $s['id'] ?>" <?= $s['id'] == $po['supplier_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['phone'] ?? 'Không có SĐT') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Chi tiết Hàng Hóa Nhập</div>
    <div class="card-body">
        <div style="display: flex; gap: 15px; align-items: flex-end; margin-bottom: 20px; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0;">
            <div class="form-group" style="flex: 2; margin-bottom: 0;">
                <label>Tìm Sản Phẩm (Phân loại)</label>
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
            <p style="color: var(--danger); font-size: 13px; margin-bottom: 10px;">Lưu ý: Lưu thay đổi sẽ tính toán lại và đồng bộ lại toàn bộ số lượng Tồn Kho.</p>
            <button type="button" class="btn-primary" id="btnSubmitPO" style="padding: 12px 24px; font-size: 16px;"><i class="fas fa-save"></i> Cập Nhật Phiếu Nhập</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const selectVariant = document.getElementById('selectVariant');
    const inputQty = document.getElementById('inputQty');
    const inputPrice = document.getElementById('inputPrice');
    const btnAddItem = document.getElementById('btnAddItem');
    const poTableBody = document.getElementById('poTableBody');
    const poTotalAmountEl = document.getElementById('poTotalAmount');
    const btnSubmitPO = document.getElementById('btnSubmitPO');
    const poSupplierSelect = document.getElementById('poSupplier');

    // Pre-load items from PHP
    let poItems = <?= json_encode(array_map(function($i) {
        return [
            'variant_id' => $i['product_variant_id'],
            'name' => $i['product_name'],
            'sku' => $i['sku'],
            'color' => $i['color'],
            'size' => $i['size'],
            'quantity' => (int)$i['quantity'],
            'unit_price' => (float)$i['unit_price']
        ];
    }, $po['items'])) ?>;

    const formatCurrency = (number) => Number(number).toLocaleString() + 'đ';

    const renderTable = () => {
        poTableBody.innerHTML = '';
        if (poItems.length === 0) {
            poTableBody.innerHTML = `
                <tr id="emptyRow">
                    <td colspan="7" style="text-align: center; color: #64748b; padding: 20px;">Chưa có sản phẩm nào trong phiếu.</td>
                </tr>
            `;
            poTotalAmountEl.textContent = '0đ';
            return;
        }

        let totalAmount = 0;
        poItems.forEach((item, index) => {
            const subtotal = item.quantity * item.unit_price;
            totalAmount += subtotal;
            
            poTableBody.innerHTML += `
                <tr>
                    <td>${index + 1}</td>
                    <td>
                        <strong>${item.name}</strong><br>
                        <small style="color:#64748b;">SKU: ${item.sku}</small>
                    </td>
                    <td>${item.color} - ${item.size}</td>
                    <td>${item.quantity}</td>
                    <td>${formatCurrency(item.unit_price)}</td>
                    <td style="font-weight: 500;">${formatCurrency(subtotal)}</td>
                    <td>
                        <button class="btn-icon btn-remove-item" data-index="${index}" style="color: var(--danger); border:none; background:transparent; cursor:pointer; font-size:16px;">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        poTotalAmountEl.textContent = formatCurrency(totalAmount);

        // Bind remove events
        document.querySelectorAll('.btn-remove-item').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = parseInt(this.getAttribute('data-index'));
                poItems.splice(idx, 1);
                renderTable();
            });
        });
    };

    renderTable(); // Initial render

    if (btnAddItem) {
        btnAddItem.addEventListener('click', () => {
            if (!selectVariant.value) {
                alert('Vui lòng chọn một sản phẩm (phân loại)!');
                return;
            }

            const option = selectVariant.options[selectVariant.selectedIndex];
            const variantId = selectVariant.value;
            const name = option.getAttribute('data-name');
            const sku = option.getAttribute('data-sku');
            const color = option.getAttribute('data-color');
            const size = option.getAttribute('data-size');
            const qty = parseInt(inputQty.value) || 0;
            const price = parseFloat(inputPrice.value) || 0;

            if (qty <= 0) {
                alert('Số lượng phải lớn hơn 0');
                return;
            }

            const existingIndex = poItems.findIndex(i => i.variant_id == variantId);
            if (existingIndex !== -1) {
                poItems[existingIndex].quantity += qty;
                poItems[existingIndex].unit_price = price; 
            } else {
                poItems.push({
                    variant_id: variantId,
                    name: name,
                    sku: sku,
                    color: color,
                    size: size,
                    quantity: qty,
                    unit_price: price
                });
            }

            selectVariant.value = '';
            inputQty.value = '1';
            inputPrice.value = '0';

            renderTable();
        });
    }

    if (btnSubmitPO) {
        btnSubmitPO.addEventListener('click', async () => {
            if (poItems.length === 0) {
                alert('Vui lòng thêm ít nhất một sản phẩm vào phiếu nhập!');
                return;
            }
            if (!poSupplierSelect.value) {
                alert('Vui lòng chọn nhà cung cấp!');
                return;
            }

            if (!confirm('Hệ thống sẽ Đảo ngược Tồn kho cũ và Áp dụng Tồn kho mới. Bạn chắc chắn chứ?')) {
                return;
            }

            const totalAmount = poItems.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);

            const payload = {
                id: <?= $po['id'] ?>,
                supplier_id: poSupplierSelect.value,
                total_amount: totalAmount,
                items: poItems
            };

            try {
                const response = await fetch('/fashion-shop/api/admin/purchase_orders/update.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    window.location.href = '?page=purchase_orders';
                } else {
                    alert('Lỗi: ' + result.message);
                }
            } catch (error) {
                alert('Lỗi kết nối máy chủ!');
            }
        });
    }
});
</script>
