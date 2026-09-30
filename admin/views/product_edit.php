<?php
require_once __DIR__ . '/../../app/controllers/CategoryController.php';
require_once __DIR__ . '/../../app/models/Product.php';

$catController = new CategoryController();
$categories = $catController->index();

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "Không tìm thấy ID sản phẩm.";
    exit;
}

$productModel = new Product();
$product = $productModel->getById($id);

if (!$product) {
    echo "Sản phẩm không tồn tại.";
    exit;
}
?>
<div class="page-header">
    <div class="page-title">
        <a href="?page=products" style="color: var(--text-muted); text-decoration: none;"><i class="fas fa-arrow-left"></i> Quay lại</a>
        <span style="margin: 0 10px; color: #cbd5e1;">|</span>
        Sửa Sản Phẩm: <span style="color:var(--primary);">#PROD-<?= $product['id'] ?></span>
    </div>
</div>

<form id="productEditForm" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $product['id'] ?>">
    
    <div class="card-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
        
        <!-- Cột Trái -->
        <div class="card-column">
            <!-- Khối 1: Thông tin cơ bản -->
            <div class="card" style="background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px;">
                <h3 style="margin-top:0; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px;">Thông tin chung</h3>
                <div class="form-group">
                    <label>Tên sản phẩm <span style="color:red">*</span></label>
                    <input type="text" id="prodName" name="name" required value="<?= htmlspecialchars($product['name']) ?>">
                </div>
                <div class="form-group">
                    <label>Đường dẫn (Slug) <span style="color:red">*</span></label>
                    <input type="text" id="prodSlug" name="slug" required value="<?= htmlspecialchars($product['slug']) ?>">
                </div>
                <div class="form-group">
                    <label>Mô tả chi tiết</label>
                    <textarea id="prodDesc" name="description" rows="6" style="resize:vertical;"><?= htmlspecialchars($product['description']) ?></textarea>
                </div>
            </div>

            <!-- Khối 2: Tạo biến thể nhanh -->
            <div class="card" style="background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px;">
                <h3 style="margin-top:0; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px;">
                    <i class="fas fa-magic" style="color: var(--primary); margin-right: 6px;"></i>Thêm biến thể nhanh
                </h3>
                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
                    Nhập thêm màu/size mới (phân cách bằng dấu phẩy). Hệ thống sẽ tạo các tổ hợp mới và bỏ qua các tổ hợp đã có.
                </p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label>Danh sách Màu sắc</label>
                        <input type="text" id="variantColors" placeholder="Ví dụ: Trắng, Đen, Đỏ" style="width:100%;">
                    </div>
                    <div class="form-group">
                        <label>Danh sách Kích thước</label>
                        <input type="text" id="variantSizes" placeholder="Ví dụ: S, M, L, XL" style="width:100%;">
                    </div>
                </div>
                <div style="display: flex; gap: 12px; align-items: center; margin-top: 8px;">
                    <div class="form-group" style="flex:1; margin-bottom:0;">
                        <label>Giá mặc định (VNĐ)</label>
                        <input type="number" id="variantDefaultPrice" placeholder="200000" min="0" style="width:100%;">
                    </div>
                    <button type="button" id="btnGenerateVariants" class="btn-primary" style="padding: 10px 20px; font-size: 14px; margin-top: 22px; white-space: nowrap;">
                        <i class="fas fa-cogs"></i> Tạo biến thể
                    </button>
                </div>
            </div>

            <!-- Khối 3: Bảng biến thể hiện có -->
            <div class="card" style="background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px;">
                    <h3 style="margin: 0;">Danh sách biến thể <span id="variantCount" style="font-size: 14px; color: var(--text-muted); font-weight: 400;">(<?= count($product['variants']) ?> biến thể)</span></h3>
                    <button type="button" id="btnAddVariant" class="btn-primary" style="padding: 6px 12px; font-size: 14px;"><i class="fas fa-plus"></i> Thêm 1 dòng</button>
                </div>
                
                <div class="table-container">
                    <table id="variantsTable">
                        <thead>
                            <tr>
                                <th>SKU <span style="color:red">*</span></th>
                                <th>Màu sắc <span style="color:red">*</span></th>
                                <th>Kích cỡ <span style="color:red">*</span></th>
                                <th>Giá bán (VNĐ) <span style="color:red">*</span></th>
                                <th>Tồn kho (Tự động)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="variantsBody">
                            <?php foreach ($product['variants'] as $v): ?>
                            <tr>
                                <input type="hidden" name="variants[variant_id][]" value="<?= $v['id'] ?>">
                                <td><input type="text" name="variants[sku][]" class="variant-input" data-manual="true" value="<?= htmlspecialchars($v['sku']) ?>" required></td>
                                <td><input type="text" name="variants[color][]" class="variant-input" value="<?= htmlspecialchars($v['color']) ?>" required></td>
                                <td><input type="text" name="variants[size][]" class="variant-input" value="<?= htmlspecialchars($v['size']) ?>" required></td>
                                <td><input type="number" name="variants[price][]" class="variant-input" value="<?= htmlspecialchars($v['price']) ?>" min="0" required></td>
                                <td><input type="number" name="variants[stock_quantity][]" class="variant-input" style="background:#f1f5f9; color:#94a3b8; cursor:not-allowed;" value="<?= htmlspecialchars($v['stock_quantity']) ?>" readonly title="Số lượng được tự động cập nhật qua module Nhập kho"></td>
                                <td style="text-align: center;"><button type="button" class="btn-icon btn-remove-variant" style="color: var(--danger); border: none; background: transparent; cursor: pointer;"><i class="fas fa-trash-alt"></i></button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 12px; line-height: 1.6;">
                    <i class="fas fa-info-circle" style="color: var(--primary);"></i>
                    <strong>Lưu ý:</strong> Mỗi dòng là <strong>một biến thể riêng biệt</strong> (1 màu + 1 size). 
                    Ví dụ: "Trắng - M", "Trắng - L", "Đen - M"... Mỗi biến thể có SKU, giá bán và tồn kho riêng.
                </p>
            </div>
        </div>

        <!-- Cột Phải -->
        <div class="card-column">
            <!-- Khối Trạng thái & Danh mục -->
            <div class="card" style="background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px;">
                <h3 style="margin-top:0; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px;">Phân loại & Trạng thái</h3>
                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="status">
                        <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Hiển thị (Active)</option>
                        <option value="draft" <?= $product['status'] === 'draft' ? 'selected' : '' ?>>Bản nháp (Draft)</option>
                        <option value="archived" <?= $product['status'] === 'archived' ? 'selected' : '' ?>>Đã lưu trữ</option>
                        <option value="out_of_stock" <?= $product['status'] === 'out_of_stock' ? 'selected' : '' ?>>Hết hàng</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Danh mục</label>
                    <select name="category_id">
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Khối Hình ảnh -->
            <div class="card" style="background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin-top:0; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px;">Hình ảnh sản phẩm</h3>
                
                <div id="existingImages" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 15px;">
                    <?php foreach ($product['images'] as $img): ?>
                        <div class="preview-img-container existing-img" data-id="<?= $img['id'] ?>">
                            <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="img">
                            <input type="hidden" name="keep_images[]" value="<?= $img['id'] ?>">
                            <button type="button" class="btn-remove-img" style="position: absolute; top:2px; right:2px; background:rgba(0,0,0,0.5); color:white; border:none; border-radius:50%; width:20px; height:20px; cursor:pointer; font-size:10px;"><i class="fas fa-times"></i></button>
                            <?php if ($img['is_primary']): ?>
                                <span style="position: absolute; top: 0; left: 0; background: var(--primary); color: white; font-size: 10px; padding: 2px 4px; border-bottom-right-radius: 4px; z-index: 1;">Chính</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-group">
                    <label>Tải thêm ảnh mới</label>
                    <input type="file" id="prodImages" name="images[]" multiple accept="image/*" style="padding: 10px; border: 1px dashed #cbd5e1; width: 100%; border-radius: 4px; background: #f8fafc; cursor: pointer;">
                </div>
                <div id="imagePreview" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
                    <!-- JS sẽ render ảnh preview mới ở đây -->
                </div>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">* Lưu ý: Ảnh đầu tiên tải lên hoặc giữ lại sẽ tự động được chọn làm ảnh đại diện. Tối đa 5 ảnh tổng cộng.</p>
            </div>
            
            <div style="margin-top: 24px; text-align: right;">
                <button type="submit" id="btnSubmitProduct" class="btn-primary" style="width: 100%; font-size: 16px; padding: 12px;"><i class="fas fa-save"></i> Cập Nhật Sản Phẩm</button>
            </div>
        </div>

    </div>
</form>

<style>
    .variant-input {
        width: 100%;
        padding: 8px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        font-family: inherit;
        font-size: 13px;
    }
    .variant-input:focus {
        border-color: var(--primary);
        outline: none;
    }
    .preview-img-container {
        position: relative;
        width: 70px;
        height: 70px;
        border-radius: 4px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .preview-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

<!-- Sử dụng chung logic của products.js (sinh slug, tự gen SKU, preview ảnh) -->
<!-- Nhưng thay đổi endpoint submit và xử lý xóa ảnh cũ -->
<script src="/fashion-shop/assets/js/products.js?v=<?= time() ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Override form submit for Update
    const productEditForm = document.getElementById('productEditForm');
    if (productEditForm) {
        // Gỡ sự kiện submit cũ từ products.js bằng cách clone form hoặc overwrite logic
        // Tuy nhiên do products.js bind bằng ID 'productCreateForm', form này mang ID 'productEditForm'
        // Nên products.js không gắn sự kiện submit vào form này. An toàn!
        productEditForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btnSubmit = document.getElementById('btnSubmitProduct');
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
            btnSubmit.disabled = true;

            const formData = new FormData(productEditForm);

            try {
                const response = await fetch('/fashion-shop/api/admin/products/update.php', {
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
                btnSubmit.innerHTML = '<i class="fas fa-save"></i> Cập Nhật Sản Phẩm';
                btnSubmit.disabled = false;
            }
        });
    }

    // Logic xóa ảnh cũ
    document.querySelectorAll('.btn-remove-img').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.existing-img').remove();
        });
    });
});
</script>
