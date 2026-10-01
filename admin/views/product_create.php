<?php
require_once __DIR__ . '/../../app/controllers/CategoryController.php';
$catController = new CategoryController();
$categories = $catController->index();
?>
<div class="page-header">
    <div class="page-title">
        <a href="?page=products" style="color: var(--text-muted); text-decoration: none;"><i class="fas fa-arrow-left"></i> Quay lại</a>
        <span style="margin: 0 10px; color: #cbd5e1;">|</span>
        Thêm Sản Phẩm Mới
    </div>
</div>

<form id="productCreateForm" enctype="multipart/form-data">
    <div class="card-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
        
        <!-- Cột Trái -->
        <div class="card-column">
            <!-- Khối 1: Thông tin cơ bản -->
            <div class="card" style="background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px;">
                <h3 style="margin-top:0; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px;">Thông tin chung</h3>
                <div class="form-group">
                    <label>Tên sản phẩm <span style="color:red">*</span></label>
                    <input type="text" id="prodName" name="name" required placeholder="Ví dụ: Áo thun nam Basic">
                </div>
                <div class="form-group">
                    <label>Đường dẫn (Slug) <span style="color:red">*</span></label>
                    <input type="text" id="prodSlug" name="slug" required placeholder="ao-thun-nam-basic">
                </div>
                <div class="form-group">
                    <label>Mô tả chi tiết</label>
                    <textarea id="prodDesc" name="description" rows="6" style="resize:vertical;" placeholder="Mô tả chất liệu, kiểu dáng..."></textarea>
                </div>
            </div>

            <!-- Khối 2: Tạo biến thể -->
            <div class="card" style="background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px;">
                <h3 style="margin-top:0; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px;">
                    <i class="fas fa-magic" style="color: var(--primary); margin-right: 6px;"></i>Tạo biến thể nhanh
                </h3>
                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
                    Nhập danh sách màu sắc và kích thước (phân cách bằng dấu phẩy). Hệ thống sẽ tự động tạo ra tất cả tổ hợp biến thể cho bạn.
                </p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label>Danh sách Màu sắc <span style="color:red">*</span></label>
                        <input type="text" id="variantColors" placeholder="Ví dụ: Trắng, Đen, Đỏ" style="width:100%;">
                        <small style="color: var(--text-muted);">Mỗi màu cách nhau bằng dấu phẩy</small>
                    </div>
                    <div class="form-group">
                        <label>Danh sách Kích thước <span style="color:red">*</span></label>
                        <input type="text" id="variantSizes" placeholder="Ví dụ: S, M, L, XL" style="width:100%;">
                        <small style="color: var(--text-muted);">Mỗi size cách nhau bằng dấu phẩy</small>
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

            <!-- Khối 3: Bảng biến thể đã tạo -->
            <div class="card" style="background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px;">
                    <h3 style="margin: 0;">Danh sách biến thể <span id="variantCount" style="font-size: 14px; color: var(--text-muted); font-weight: 400;">(0 biến thể)</span></h3>
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
                                <th>Giá Sale (VNĐ)</th>
                                <th>Tồn kho (Tự động)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="variantsBody">
                            <!-- JS sẽ render dòng nhập liệu ở đây -->
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
                        <option value="active">Hiển thị (Active)</option>
                        <option value="draft">Bản nháp (Draft)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Danh mục</label>
                    <select name="category_id">
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Khối Hình ảnh -->
            <div class="card" style="background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin-top:0; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px;">Hình ảnh sản phẩm</h3>
                <div class="form-group">
                    <label>Tải ảnh lên (Nhiều ảnh)</label>
                    <input type="file" id="prodImages" name="images[]" multiple accept="image/*" style="padding: 10px; border: 1px dashed #cbd5e1; width: 100%; border-radius: 4px; background: #f8fafc; cursor: pointer;">
                </div>
                <div id="imagePreview" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
                    <!-- JS sẽ render ảnh preview ở đây -->
                </div>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">* Lưu ý: Ảnh đầu tiên tải lên sẽ tự động được chọn làm ảnh đại diện.</p>
            </div>
            
            <div style="margin-top: 24px; text-align: right;">
                <button type="submit" id="btnSubmitProduct" class="btn-primary" style="width: 100%; font-size: 16px; padding: 12px;"><i class="fas fa-save"></i> Lưu Sản Phẩm</button>
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

<script src="/fashion-shop/assets/js/products.js?v=<?= time() ?>"></script>
