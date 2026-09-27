document.addEventListener('DOMContentLoaded', () => {
    // --- Supplier Modal Logic ---
    const supplierModal = document.getElementById('supplierModal');
    const btnOpenSupplier = document.getElementById('btnOpenSupplierModal');
    const btnCloseSupplier = document.getElementById('btnCloseSupplierModal');
    const btnSubmitSupplier = document.getElementById('btnSubmitSupplier');
    const supplierForm = document.getElementById('supplierForm');
    const poSupplierSelect = document.getElementById('poSupplier');

    if (btnOpenSupplier && supplierModal) {
        btnOpenSupplier.addEventListener('click', () => {
            supplierForm.reset();
            supplierModal.classList.add('active');
        });

        btnCloseSupplier.addEventListener('click', () => {
            supplierModal.classList.remove('active');
        });

        btnSubmitSupplier.addEventListener('click', async () => {
            const formData = new FormData(supplierForm);
            try {
                const response = await fetch('/fashion-shop/api/admin/suppliers/create.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    // Add new supplier to select and select it
                    const newOption = document.createElement('option');
                    newOption.value = result.id;
                    newOption.textContent = result.name;
                    poSupplierSelect.appendChild(newOption);
                    poSupplierSelect.value = result.id;
                    
                    supplierModal.classList.remove('active');
                } else {
                    alert('Lỗi: ' + result.message);
                }
            } catch (error) {
                alert('Lỗi kết nối máy chủ!');
            }
        });
    }

    // --- Purchase Order Items Logic ---
    const selectVariant = document.getElementById('selectVariant');
    const inputQty = document.getElementById('inputQty');
    const inputPrice = document.getElementById('inputPrice');
    const btnAddItem = document.getElementById('btnAddItem');
    const poTableBody = document.getElementById('poTableBody');
    const poTotalAmountEl = document.getElementById('poTotalAmount');
    const btnSubmitPO = document.getElementById('btnSubmitPO');

    let poItems = [];

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

            // Check if variant already exists in list, just update quantity
            const existingIndex = poItems.findIndex(i => i.variant_id === variantId);
            if (existingIndex !== -1) {
                poItems[existingIndex].quantity += qty;
                // If they entered a new price, maybe we overwrite it? Let's overwrite for simplicity.
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

            // Reset inputs
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

            if (!confirm('Bạn có chắc chắn muốn hoàn tất phiếu nhập này? Tồn kho sẽ được cộng dồn và không thể hoàn tác!')) {
                return;
            }

            const totalAmount = poItems.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);

            const payload = {
                supplier_id: poSupplierSelect.value,
                total_amount: totalAmount,
                items: poItems
            };

            try {
                const response = await fetch('/fashion-shop/api/admin/purchase_orders/create.php', {
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
