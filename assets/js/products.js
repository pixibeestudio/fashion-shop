document.addEventListener('DOMContentLoaded', () => {
    // Helper function: Remove Vietnamese accents
    const removeAccents = (str) => {
        return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D');
    };

    // Helper function: Generate SKU
    const generateSKU = (name, color, size) => {
        if (!name) return '';
        // Extract first letter of each word for abbreviation (e.g. "Áo thun nam" -> "ATN")
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

    const updateAllSKUs = () => {
        if (!prodNameInput || !variantsBody) return;
        const productName = prodNameInput.value;
        const rows = variantsBody.querySelectorAll('tr');
        rows.forEach(row => {
            const colorInput = row.querySelector('input[name="variants[color][]"]');
            const sizeInput = row.querySelector('input[name="variants[size][]"]');
            const skuInput = row.querySelector('input[name="variants[sku][]"]');
            
            // Nếu sku chưa bị sửa bằng tay (dataset.manual chưa set) thì tự động sinh
            if (skuInput && colorInput && sizeInput && !skuInput.dataset.manual) {
                skuInput.value = generateSKU(productName, colorInput.value, sizeInput.value);
            }
        });
    };

    // 1. Auto Slug & Auto SKU on Product Name change
    if (prodNameInput && prodSlugInput) {
        prodNameInput.addEventListener('input', function() {
            // Update Slug
            let title = this.value.toLowerCase();
            title = removeAccents(title);
            title = title.replace(/([^a-z0-9-\s])/g, '');
            title = title.replace(/(\s+)/g, '-');
            title = title.replace(/-+/g, '-');
            title = title.replace(/^-+|-+$/g, '');
            prodSlugInput.value = title;

            // Update SKUs
            updateAllSKUs();
        });
    }

    // 2. Dynamic Variant Rows & Auto SKU on Color/Size change
    const btnAddVariant = document.getElementById('btnAddVariant');
    
    if (btnAddVariant && variantsBody) {
        const createVariantRow = () => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <input type="hidden" name="variants[variant_id][]" value="">
                <td><input type="text" name="variants[sku][]" class="variant-input" placeholder="Tự động..." required></td>
                <td><input type="text" name="variants[color][]" class="variant-input" placeholder="Ví dụ: Đen" required></td>
                <td><input type="text" name="variants[size][]" class="variant-input" placeholder="Ví dụ: M" required></td>
                <td><input type="number" name="variants[price][]" class="variant-input" placeholder="0" min="0" required></td>
                <td><input type="number" name="variants[stock_quantity][]" class="variant-input" placeholder="0" value="0" readonly style="background:#f1f5f9; color:#94a3b8; cursor:not-allowed;" title="Số lượng được tự động cập nhật qua module Nhập kho"></td>
                <td style="text-align: center;"><button type="button" class="btn-icon btn-remove-variant" style="color: var(--danger); border: none; background: transparent; cursor: pointer;"><i class="fas fa-trash-alt"></i></button></td>
            `;
            variantsBody.appendChild(tr);
            updateAllSKUs(); // Generate SKU for the new row immediately
        };

        // Initialize with one row if empty
        if (variantsBody.children.length === 0) {
            createVariantRow();
        }

        btnAddVariant.addEventListener('click', () => {
            createVariantRow();
        });

        // Event delegation for Variant inputs (Color, Size) & Delete button
        variantsBody.addEventListener('input', (e) => {
            // Update SKUs if Color or Size changes
            if (e.target.name === 'variants[color][]' || e.target.name === 'variants[size][]') {
                updateAllSKUs();
            }
            // Mark SKU as manual if user edits it directly
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
                } else {
                    alert("Sản phẩm phải có ít nhất 1 biến thể!");
                }
            }
        });
    }

    // 3. Image Previews
    const prodImages = document.getElementById('prodImages');
    const imagePreview = document.getElementById('imagePreview');

    if (prodImages && imagePreview) {
        prodImages.addEventListener('change', function() {
            imagePreview.innerHTML = ''; // Clear old previews
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

                    // Badge cho ảnh đại diện
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

    // 4. Handle Form Submit via Fetch API
    const productCreateForm = document.getElementById('productCreateForm');
    if (productCreateForm) {
        productCreateForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
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
