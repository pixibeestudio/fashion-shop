<?php
require_once __DIR__ . '/../../app/controllers/CategoryController.php';
$catController = new CategoryController();
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$pagination = $catController->paginate($page);
$categories = $pagination['data'];
$parents = $catController->getParents();
?>
<div class="page-header">
    <div class="page-title">Quản lý Danh mục</div>
    <button class="btn-primary" id="btnOpenCategoryModal"><i class="fas fa-plus"></i> Thêm Danh Mục</button>
</div>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên danh mục</th>
                <th>Đường dẫn (Slug)</th>
                <th>Danh mục cha</th>
                <th>Số sản phẩm</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody id="categoryTableBody">
            <?php if (empty($categories)): ?>
            <tr>
                <td colspan="6" style="text-align: center; color: #64748b; padding: 20px;">Chưa có dữ liệu danh mục</td>
            </tr>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><b>#CAT-<?= htmlspecialchars($cat['id']) ?></b></td>
                    <td><?= htmlspecialchars($cat['name']) ?></td>
                    <td><?= htmlspecialchars($cat['slug']) ?></td>
                    <td>
                        <?php if ($cat['parent_name']): ?>
                            <span class="tag gray"><?= htmlspecialchars($cat['parent_name']) ?></span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td style="font-weight: 600;"><?= htmlspecialchars($cat['product_count']) ?></td>
                    <td style="white-space: nowrap;">
                        <button class="btn-icon btn-view" data-id="<?= $cat['id'] ?>" title="Xem chi tiết" style="color: #64748b; border:none; background:transparent; cursor:pointer; font-size:16px; margin-right: 12px;"><i class="fas fa-eye"></i></button>
                        <button class="btn-icon btn-edit" data-id="<?= $cat['id'] ?>" title="Sửa danh mục" style="color: var(--primary); border:none; background:transparent; cursor:pointer; font-size:16px; margin-right: 12px;"><i class="fas fa-pencil-alt"></i></button>
                        <button class="btn-icon btn-delete" data-id="<?= $cat['id'] ?>" title="Xóa danh mục" style="color: var(--danger); border:none; background:transparent; cursor:pointer; font-size:16px;"><i class="fas fa-trash-alt"></i></button>
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
        <a href="?page=categories&p=<?= $i ?>" class="page-link <?= $i === $pagination['current_page'] ? 'active' : '' ?>" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; text-decoration: none; color: <?= $i === $pagination['current_page'] ? 'white' : 'var(--text-color)' ?>; background: <?= $i === $pagination['current_page'] ? 'var(--primary)' : 'white' ?>;"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<!-- Modal Thêm/Sửa Danh Mục -->
<div class="modal-overlay" id="categoryModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Thêm Danh Mục Mới</h2>
            <p>Điền thông tin bên dưới để tạo danh mục sản phẩm</p>
        </div>
        <div class="modal-body">
            <form id="categoryForm">
                <!-- Hidden input for Update -->
                <input type="hidden" id="catId" name="id">

                <div class="form-group">
                    <label>Tên danh mục</label>
                    <input type="text" id="catName" name="name" placeholder="Ví dụ: Áo Sơ Mi Nam" required>
                </div>
                <div class="form-group">
                    <label>Đường dẫn (Slug)</label>
                    <input type="text" id="catSlug" name="slug" placeholder="Ví dụ: ao-so-mi-nam" required>
                </div>
                <div class="form-group">
                    <label>Danh mục cha (Tùy chọn)</label>
                    <select id="catParent" name="parent_id">
                        <option value="">-- Không có (Danh mục gốc) --</option>
                        <?php foreach ($parents as $p): ?>
                            <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mô tả (Tùy chọn)</label>
                    <textarea id="catDesc" name="description" rows="3" maxlength="500" style="resize: vertical;" placeholder="Mô tả ngắn gọn về danh mục..."></textarea>
                    <div style="text-align: right; font-size: 12px; color: var(--text-muted); margin-top: 4px;" id="charCount">0/500</div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btnCloseCategoryModal">Hủy</button>
            <button type="submit" class="btn-submit" id="btnSubmitForm" form="categoryForm">Thêm Danh Mục</button>
        </div>
    </div>
</div>

<!-- Modal Xem Chi Tiết -->
<div class="modal-overlay" id="viewCategoryModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Chi tiết Danh Mục</h2>
        </div>
        <div class="modal-body">
            <div style="margin-bottom: 15px;"><strong>ID:</strong> <span id="viewCatId"></span></div>
            <div style="margin-bottom: 15px;"><strong>Tên danh mục:</strong> <span id="viewCatName"></span></div>
            <div style="margin-bottom: 15px;"><strong>Đường dẫn (Slug):</strong> <span id="viewCatSlug"></span></div>
            <div style="margin-bottom: 15px;"><strong>Danh mục cha:</strong> <span id="viewCatParent"></span></div>
            <div style="margin-bottom: 15px;"><strong>Mô tả:</strong> <p id="viewCatDesc" style="margin-top: 5px; color: #64748b; line-height: 1.5;"></p></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btnCloseViewCatModal">Đóng</button>
        </div>
    </div>
</div>

<!-- Script riêng cho chức năng Categories -->
<script src="/fashion-shop/assets/js/categories.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Logic cho nút Xem chi tiết
    const viewCatModal = document.getElementById('viewCategoryModal');
    document.querySelectorAll('.btn-view').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            try {
                const response = await fetch(`/fashion-shop/api/admin/categories/get.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const cat = result.category;
                    document.getElementById('viewCatId').textContent = '#CAT-' + cat.id;
                    document.getElementById('viewCatName').textContent = cat.name;
                    document.getElementById('viewCatSlug').textContent = cat.slug;
                    document.getElementById('viewCatParent').textContent = cat.parent_id ? 'Có (ID: ' + cat.parent_id + ')' : 'Không (Danh mục gốc)';
                    document.getElementById('viewCatDesc').textContent = cat.description || 'Không có mô tả.';
                    
                    viewCatModal.classList.add('active');
                } else {
                    alert('Lỗi: ' + result.message);
                }
            } catch (error) {
                alert('Lỗi kết nối máy chủ!');
            }
        });
    });

    document.getElementById('btnCloseViewCatModal').addEventListener('click', () => {
        viewCatModal.classList.remove('active');
    });
});
</script>
