document.addEventListener('DOMContentLoaded', () => {
    // ============================================================
    // HELPER FUNCTIONS
    // ============================================================
    const removeAccents = (str) => {
        return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D');
    };

    const generateSKU = (name, color, size) => {
        if (!name) return '';
        let nameAbbr = name.split(' ')
            .filter(w => w.trim() !== '')
            .map(w => w.charAt(0))
            .join('')
            .toUpperCase();
        
        nameAbbr = removeAccents(nameAbbr);
        let colorCode = color ? removeAccents(color).toUpperCase().replace(/\s+/g, '') : '';
        let sizeCode = size ? removeAccents(size).toUpperCase().replace(/\s+/g, '') : '';

        let sku = nameAbbr;
        if (colorCode) sku += '-' + colorCode;
        if (sizeCode) sku += '-' + sizeCode;
        
        return sku;
    };

    const prodNameInput = document.getElementById('prodName');
    const prodSlugInput = document.getElementById('prodSlug');
    const variantsBody = document.getElementById('variantsBody');

    // ============================================================
    // UPDATE VARIANT COUNT BADGE
    // ============================================================
    const updateVariantCount = () => {
        const countEl = document.getElementById('variantCount');
        if (countEl && variantsBody) {
            const count = variantsBody.querySelectorAll('tr').length;
            countEl.textContent = `(${count} biến thể)`;
        }
    };

    // ============================================================
    // UPDATE ALL SKUs AUTOMATICALLY
    // ============================================================
    const updateAllSKUs = () => {
        if (!prodNameInput || !variantsBody) return;
        const productName = prodNameInput.value;
        const rows = variantsBody.querySelectorAll('tr');
        rows.forEach(row => {
            const colorInput = row.querySelector('input[name="variants[color][]"]');
            const sizeInput = row.querySelector('input[name="variants[size][]"]');
            const skuInput = row.querySelector('input[name="variants[sku][]"]');
            
            if (skuInput && colorInput && sizeInput && !skuInput.dataset.manual) {
                skuInput.value = generateSKU(productName, colorInput.value, sizeInput.value);
            }
        });
    };

    // ============================================================
    // 1. AUTO SLUG & AUTO SKU ON PRODUCT NAME CHANGE
    // ============================================================
    if (prodNameInput && prodSlugInput) {
        prodNameInput.addEventListener('input', function() {
            let title = this.value.toLowerCase();
            title = removeAccents(title);
            title = title.replace(/([^a-z0-9-\s])/g, '');
            title = title.replace(/(\s+)/g, '-');
            title = title.replace(/-+/g, '-');
            title = title.replace(/^-+|-+$/g, '');
            prodSlugInput.value = title;
            updateAllSKUs();
        });
    }

    // ============================================================
    // 2. CREATE VARIANT ROW
    // ============================================================
    const createVariantRow = (color = '', size = '', price = '') => {
        if (!variantsBody) return;
        const tr = document.createElement('tr');
        const productName = prodNameInput ? prodNameInput.value : '';
        const autoSKU = generateSKU(productName, color, size);
        
        tr.innerHTML = `
            <input type="hidden" name="variants[variant_id][]" value="">
            <td><input type="text" name="variants[sku][]" class="variant-input" placeholder="Tự động..." value="${autoSKU}" required></td>
            <td><input type="text" name="variants[color][]" class="variant-input" placeholder="Ví dụ: Đen" value="${color}" required></td>
            <td><input type="text" name="variants[size][]" class="variant-input" placeholder="Ví dụ: M" value="${size}" required></td>
            <td><input type="number" name="variants[price][]" class="variant-input" placeholder="0" min="0" value="${price}" required></td>
            <td><input type="number" name="variants[stock_quantity][]" class="variant-input" placeholder="0" value="0" readonly style="background:#f1f5f9; color:#94a3b8; cursor:not-allowed;" title="Số lượng được tự động cập nhật qua module Nhập kho"></td>
            <td style="text-align: center;"><button type="button" class="btn-icon btn-remove-variant" style="color: var(--danger); border: none; background: transparent; cursor: pointer;"><i class="fas fa-trash-alt"></i></button></td>
        `;
        variantsBody.appendChild(tr);
        updateVariantCount();
    };

    // ============================================================
    // 3. VARIANT GENERATOR - TẠO NHANH TỔ HỢP BIẾN THỂ
    // ============================================================
    const btnGenerateVariants = document.getElementById('btnGenerateVariants');
    if (btnGenerateVariants) {
        btnGenerateVariants.addEventListener('click', () => {
            const colorsInput = document.getElementById('variantColors');
            const sizesInput = document.getElementById('variantSizes');
            const defaultPriceInput = document.getElementById('variantDefaultPrice');

            const colorsRaw = colorsInput ? colorsInput.value.trim() : '';
            const sizesRaw = sizesInput ? sizesInput.value.trim() : '';
            const defaultPrice = defaultPriceInput ? defaultPriceInput.value.trim() : '';

            if (!colorsRaw || !sizesRaw) {
                alert('Vui lòng nhập ít nhất 1 màu sắc và 1 kích thước.');
                return;
            }

            const colors = colorsRaw.split(',').map(c => c.trim()).filter(c => c !== '');
            const sizes = sizesRaw.split(',').map(s => s.trim()).filter(s => s !== '');

            if (colors.length === 0 || sizes.length === 0) {
                alert('Vui lòng nhập ít nhất 1 màu sắc và 1 kích thước.');
                return;
            }

            // Check for duplicates with existing rows
            const existingCombos = new Set();
            variantsBody.querySelectorAll('tr').forEach(row => {
                const c = row.querySelector('input[name="variants[color][]"]')?.value.trim().toLowerCase();
                const s = row.querySelector('input[name="variants[size][]"]')?.value.trim().toLowerCase();
                if (c && s) existingCombos.add(`${c}|${s}`);
            });

            let addedCount = 0;
            let skippedCount = 0;

            colors.forEach(color => {
                sizes.forEach(size => {
                    const key = `${color.toLowerCase()}|${size.toLowerCase()}`;
                    if (existingCombos.has(key)) {
                        skippedCount++;
                    } else {
                        createVariantRow(color, size, defaultPrice);
                        existingCombos.add(key);
                        addedCount++;
                    }
                });
            });

            let message = `Đã tạo ${addedCount} biến thể mới.`;
            if (skippedCount > 0) {
                message += ` (Bỏ qua ${skippedCount} tổ hợp đã tồn tại)`;
            }
            alert(message);
        });
    }

    // ============================================================
    // 4. MANUAL ADD / REMOVE VARIANT ROWS
    // ============================================================
    const btnAddVariant = document.getElementById('btnAddVariant');
    if (btnAddVariant && variantsBody) {
        btnAddVariant.addEventListener('click', () => {
            createVariantRow();
        });

        // Event delegation: SKU auto-update & delete
        variantsBody.addEventListener('input', (e) => {
            if (e.target.name === 'variants[color][]' || e.target.name === 'variants[size][]') {
                updateAllSKUs();
            }
            if (e.target.name === 'variants[sku][]') {
                e.target.dataset.manual = 'true'; 
            }
        });

        variantsBody.addEventListener('click', (e) => {
            const btnRemove = e.target.closest('.btn-remove-variant');
            if (btnRemove) {
                const rowCount = variantsBody.querySelectorAll('tr').length;
                if (rowCount > 1) {
                    btnRemove.closest('tr').remove();
                    updateVariantCount();
                } else {
                    alert("Sản phẩm phải có ít nhất 1 biến thể!");
                }
            }
        });
    }

    // ============================================================
    // 5. IMAGE PREVIEWS
    // ============================================================
    const prodImages = document.getElementById('prodImages');
    const imagePreview = document.getElementById('imagePreview');

    if (prodImages && imagePreview) {
        prodImages.addEventListener('change', function() {
            imagePreview.innerHTML = '';
            const files = Array.from(this.files);
            
            if (files.length > 5) {
                alert("Bạn chỉ có thể tải lên tối đa 5 ảnh.");
                this.value = '';
                return;
            }

            files.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'preview-img-container';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    
                    div.appendChild(img);

                    if (index === 0) {
                        const badge = document.createElement('span');
                        badge.style.cssText = "position: absolute; top: 0; left: 0; background: var(--primary); color: white; font-size: 10px; padding: 2px 4px; border-bottom-right-radius: 4px; z-index: 1;";
                        badge.textContent = "Chính";
                        div.appendChild(badge);
                    }
                    
                    imagePreview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        });
    }

    // ============================================================
    // 6. HANDLE FORM SUBMIT (CREATE)
    // ============================================================
    const productCreateForm = document.getElementById('productCreateForm');
    if (productCreateForm) {
        productCreateForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Validate at least 1 variant
            const variantRows = variantsBody ? variantsBody.querySelectorAll('tr').length : 0;
            if (variantRows === 0) {
                alert('Sản phẩm phải có ít nhất 1 biến thể. Hãy tạo biến thể trước khi lưu.');
                return;
            }

            const btnSubmit = document.getElementById('btnSubmitProduct');
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
            btnSubmit.disabled = true;

            const formData = new FormData(productCreateForm);

            try {
                const response = await fetch('/fashion-shop/api/admin/products/create.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    window.location.href = '?page=products';
                } else {
                    alert("Lỗi: " + result.message);
                }
            } catch (error) {
                alert("Đã xảy ra lỗi kết nối với máy chủ.");
                console.error(error);
            } finally {
                btnSubmit.innerHTML = '<i class="fas fa-save"></i> Lưu Sản Phẩm';
                btnSubmit.disabled = false;
            }
        });
    }
});
