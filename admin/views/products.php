<?php
require_once __DIR__ . '/../../app/controllers/ProductController.php';
$productController = new ProductController();
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$pagination = $productController->paginate($page);
$products = $pagination['data'];
?>
<div class="page-header">
    <div class="page-title">Quản lý Sản phẩm</div>
    <!-- Nút thêm sản phẩm sẽ trỏ sang 1 trang riêng thay vì modal -->
    <a href="?page=product_create" class="btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;"><i class="fas fa-plus"></i> Thêm Sản Phẩm</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Mã SKU (Các biến thể)</th>
                <th>Hình ảnh</th>
                <th>Tên sản phẩm</th>
                <th>Danh mục</th>
                <th>Phân loại (Variants)</th>
                <th>Tổng tồn kho</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
            <tr>
                <td colspan="9" style="text-align: center; color: #64748b; padding: 20px;">Chưa có sản phẩm nào</td>
            </tr>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><b>#PROD-<?= htmlspecialchars($p['id']) ?></b></td>
                    <td>
                        <?php 
                        $skus = $p['skus'] ? explode(', ', $p['skus']) : [];
                        if (count($skus) > 0) {
                            echo '<span style="color:var(--primary); font-family: monospace;">' . htmlspecialchars($skus[0]) . '</span>';
                            if (count($skus) > 1) {
                                echo '<br><small style="color:var(--text-muted);">+' . (count($skus) - 1) . ' mã khác</small>';
                            }
                        } else {
                            echo '<span style="color:var(--text-muted);">- Chưa có -</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <?php if ($p['primary_image']): ?>
                            <img src="<?= htmlspecialchars($p['primary_image']) ?>" alt="img" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                        <?php else: ?>
                            <div style="width: 50px; height: 50px; background: #e2e8f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #94a3b8;"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight: 500;"><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= htmlspecialchars($p['category_name'] ?? 'Không có') ?></td>
                    <td>
                        <?php if ($p['variant_count'] > 0): ?>
                            <span class="tag" style="background: #e0e7ff; color: #4338ca;"><?= $p['variant_count'] ?> loại</span>
                        <?php else: ?>
                            <span class="tag gray">Chưa có</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($p['total_stock'] > 0): ?>
                            <span style="font-weight: 600; color: #10b981;"><?= $p['total_stock'] ?></span>
                        <?php else: ?>
                            <span style="color: #ef4444; font-weight: 600;">Hết hàng (0)</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                            switch($p['status']) {
                                case 'active': echo '<span class="tag" style="background: #dcfce7; color: #166534;">Hiển thị</span>'; break;
                                case 'draft': echo '<span class="tag gray">Bản nháp</span>'; break;
                                case 'archived': echo '<span class="tag" style="background: #fef3c7; color: #92400e;">Đã lưu trữ</span>'; break;
                                case 'out_of_stock': echo '<span class="tag" style="background: #fee2e2; color: #991b1b;">Hết hàng</span>'; break;
                            }
                        ?>
                    </td>
                    <td style="white-space: nowrap;">
                        <button class="btn-icon btn-view-product" data-id="<?= $p['id'] ?>" title="Xem chi tiết" style="color: #64748b; border:none; background:transparent; cursor:pointer; font-size:16px; margin-right: 12px;"><i class="fas fa-eye"></i></button>
                        <a href="?page=product_edit&id=<?= $p['id'] ?>" class="btn-icon" title="Sửa sản phẩm" style="color: var(--primary); text-decoration:none; margin-right: 12px; font-size:16px;"><i class="fas fa-pencil-alt"></i></a>
                        <button class="btn-icon btn-delete-product" data-id="<?= $p['id'] ?>" title="Xóa sản phẩm" style="color: var(--danger); border:none; background:transparent; cursor:pointer; font-size:16px;"><i class="fas fa-trash-alt"></i></button>
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
        <a href="?page=products&p=<?= $i ?>" class="page-link <?= $i === $pagination['current_page'] ? 'active' : '' ?>" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; text-decoration: none; color: <?= $i === $pagination['current_page'] ? 'white' : 'var(--text-color)' ?>; background: <?= $i === $pagination['current_page'] ? 'var(--primary)' : 'white' ?>;"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<!-- Modal Xem Chi Tiết -->
<div class="modal-overlay" id="viewProductModal">
    <div class="modal-content" style="max-width: 800px; width: 90%;">
        <div class="modal-header">
            <h2>Chi tiết Sản Phẩm</h2>
        </div>
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 300px;">
                    <div style="margin-bottom: 10px;"><strong>ID:</strong> <span id="vpId"></span></div>
                    <div style="margin-bottom: 10px;"><strong>Tên:</strong> <span id="vpName"></span></div>
                    <div style="margin-bottom: 10px;"><strong>Slug:</strong> <span id="vpSlug"></span></div>
                    <div style="margin-bottom: 10px;"><strong>Trạng thái:</strong> <span id="vpStatus"></span></div>
                    <div style="margin-bottom: 10px;"><strong>Mô tả:</strong> <p id="vpDesc" style="margin-top:5px; color:#64748b; font-size:14px;"></p></div>
                </div>
                <div style="flex: 1; min-width: 300px;">
                    <strong>Hình ảnh:</strong>
                    <div id="vpImages" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;"></div>
                </div>
            </div>
            
            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
            
            <strong>Phân loại (Variants):</strong>
            <div class="table-container" style="margin-top: 10px;">
                <table>
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Màu sắc</th>
                            <th>Kích cỡ</th>
                            <th>Giá</th>
                            <th>Tồn kho</th>
                        </tr>
                    </thead>
                    <tbody id="vpVariants">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btnCloseViewProductModal">Đóng</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const deleteBtns = document.querySelectorAll('.btn-delete-product');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', async function() {
            if (confirm('Bạn có chắc chắn muốn xóa cứng sản phẩm này? Mọi biến thể và hình ảnh liên quan sẽ bị xóa vĩnh viễn!')) {
                const id = this.getAttribute('data-id');
                try {
                    const response = await fetch('/fashion-shop/api/admin/products/delete.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    const result = await response.json();
                    
                    if (result.success) {
                        alert(result.message);
                        location.reload(); // Tải lại trang để cập nhật danh sách
                    } else {
                        alert('Lỗi: ' + result.message);
                    }
                } catch (error) {
                    alert('Lỗi kết nối máy chủ!');
                }
            }
        });
    });

    // View Modal Logic
    const viewModal = document.getElementById('viewProductModal');
    document.querySelectorAll('.btn-view-product').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            try {
                const response = await fetch(`/fashion-shop/api/admin/products/get.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const p = result.data;
                    document.getElementById('vpId').textContent = '#PROD-' + p.id;
                    document.getElementById('vpName').textContent = p.name;
                    document.getElementById('vpSlug').textContent = p.slug;
                    document.getElementById('vpStatus').textContent = p.status;
                    document.getElementById('vpDesc').textContent = p.description || 'Không có mô tả';
                    
                    // Render images
                    const imgContainer = document.getElementById('vpImages');
                    imgContainer.innerHTML = '';
                    if (p.images && p.images.length > 0) {
                        p.images.forEach(img => {
                            imgContainer.innerHTML += `<img src="${img.image_url}" style="width: 70px; height: 70px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0;">`;
                        });
                    } else {
                        imgContainer.innerHTML = '<span style="color:#94a3b8;">Không có ảnh</span>';
                    }

                    // Render variants
                    const varBody = document.getElementById('vpVariants');
                    varBody.innerHTML = '';
                    if (p.variants && p.variants.length > 0) {
                        p.variants.forEach(v => {
                            varBody.innerHTML += `
                                <tr>
                                    <td>${v.sku}</td>
                                    <td>${v.color}</td>
                                    <td>${v.size}</td>
                                    <td>${Number(v.price).toLocaleString()}đ</td>
                                    <td>${v.stock_quantity}</td>
                                </tr>
                            `;
                        });
                    } else {
                        varBody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#94a3b8;">Không có biến thể</td></tr>';
                    }

                    viewModal.classList.add('active');
                } else {
                    alert('Lỗi: ' + result.message);
                }
            } catch (error) {
                alert('Lỗi kết nối máy chủ!');
            }
        });
    });

    document.getElementById('btnCloseViewProductModal').addEventListener('click', () => {
        viewModal.classList.remove('active');
    });
});
</script>
