<?php
require_once __DIR__ . '/../app/config/Database.php';

$productId = $_GET['id'] ?? 0;
$db = Database::getInstance()->getConnection();

// Lấy thông tin sản phẩm
$stmt = $db->prepare("SELECT p.id, p.name, p.slug, p.status, p.description,
                     c.name as category_name
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id
                     WHERE p.id = :id AND p.status = 'active'");
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: /fashion-shop/public/index.php");
    exit;
}

// Lấy tất cả hình ảnh
$stmtImgs = $db->prepare("SELECT image_url FROM product_images WHERE product_id = :id ORDER BY is_primary DESC, id ASC");
$stmtImgs->execute(['id' => $productId]);
$images = $stmtImgs->fetchAll(PDO::FETCH_COLUMN);
$mainImage = !empty($images[0]) ? $images[0] : null;

// Lấy TẤT CẢ biến thể - MỖI DÒNG LÀ 1 TỔ HỢP (màu + size) RIÊNG BIỆT
$stmtVars = $db->prepare("SELECT id, sku, color, size, price, stock_quantity FROM product_variants WHERE product_id = :id ORDER BY color, size");
$stmtVars->execute(['id' => $productId]);
$variants = $stmtVars->fetchAll(PDO::FETCH_ASSOC);

// Tách danh sách màu và size DUY NHẤT từ các biến thể
$colors = [];
$sizes = [];
foreach ($variants as $v) {
    $c = trim($v['color']);
    $s = trim($v['size']);
    if (!empty($c) && !in_array($c, $colors)) $colors[] = $c;
    if (!empty($s) && !in_array($s, $sizes)) $sizes[] = $s;
}

// Lấy giá thấp nhất để hiển thị mặc định
$minPrice = PHP_INT_MAX;
$maxPrice = 0;
foreach ($variants as $v) {
    if ($v['price'] < $minPrice) $minPrice = $v['price'];
    if ($v['price'] > $maxPrice) $maxPrice = $v['price'];
}

$pageTitle = htmlspecialchars($product['name']) . " - Fashion Shop";
require_once __DIR__ . '/includes/header.php';
?>
<style>
.product-detail-layout { display: flex; gap: 60px; padding: 40px 60px; }
.product-gallery { flex: 1; display: flex; flex-direction: column; gap: 16px; }
.main-image { width: 100%; height: 550px; background: var(--bg); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 80px; overflow: hidden; border: 1px solid var(--border);}
.thumbnail-list { display: flex; gap: 16px; overflow-x: auto;}
.thumbnail { width: 80px; height: 80px; background: var(--bg); border-radius: 8px; cursor: pointer; border: 2px solid transparent; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; transition: 0.2s;}
.thumbnail.active { border-color: var(--primary); }
.thumbnail img, .main-image img { width: 100%; height: 100%; object-fit: cover; }

.product-info-detail { flex: 1; display: flex; flex-direction: column; }
.breadcrumb { font-size: 14px; color: var(--text-muted); margin-bottom: 16px; }
.breadcrumb a:hover { color: var(--primary); }
.detail-title { font-size: 32px; font-weight: 800; color: var(--text-main); margin-bottom: 8px; line-height: 1.3;}
.detail-sku { font-size: 14px; color: var(--text-muted); margin-bottom: 24px; }
.detail-price { font-size: 32px; font-weight: 700; color: var(--primary); margin-bottom: 24px; display: flex; align-items: center; gap: 16px;}
.detail-desc { font-size: 15px; color: #4B5563; line-height: 1.7; margin-bottom: 32px; padding-bottom: 32px; border-bottom: 1px solid var(--border); }

.variant-group { margin-bottom: 24px; }
.variant-label { font-weight: 600; color: var(--text-main); font-size: 15px; margin-bottom: 12px; }
.variant-options { display: flex; gap: 12px; flex-wrap: wrap; }
.variant-btn { padding: 10px 24px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface); cursor: pointer; font-weight: 500; transition: 0.2s;}
.variant-btn:hover { border-color: var(--primary); }
.variant-btn.active { border-color: var(--primary); color: var(--primary); background: #EEF2FF; border-width: 2px; padding: 9px 23px; }
.variant-btn.disabled { opacity: 0.35; cursor: not-allowed; pointer-events: none; text-decoration: line-through; }

.qty-control { display: flex; align-items: center; gap: 16px; background: var(--bg); padding: 8px 16px; border-radius: 8px; width: fit-content; font-size: 16px; user-select: none;}
.qty-control span { cursor: pointer; padding: 0 10px; font-weight: bold; color: var(--text-main); transition: 0.2s;}
.qty-control span:hover { color: var(--primary); }

.stock-status { font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 6px;}
.stock-status.in-stock { color: var(--success); }
.stock-status.out-of-stock { color: var(--danger); }
</style>

<div class="product-detail-layout">
    <!-- CỘT TRÁI: ẢNH -->
    <div class="product-gallery">
        <div class="main-image">
            <?php if ($mainImage): ?>
                <img src="<?= $mainImage ?>" alt="Product" id="mainProductImage">
            <?php else: ?>
                <i class="fas fa-image" style="color: #9CA3AF;"></i>
            <?php endif; ?>
        </div>
        <?php if (!empty($images)): ?>
        <div class="thumbnail-list">
            <?php foreach($images as $idx => $img): ?>
            <div class="thumbnail <?= $idx === 0 ? 'active' : '' ?>" onclick="changeMainImage(this, '<?= $img ?>')">
                <img src="<?= $img ?>" alt="Thumb">
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- CỘT PHẢI: THÔNG TIN -->
    <div class="product-info-detail">
        <div class="breadcrumb">
            <a href="/fashion-shop/public/index.php">Trang chủ</a> / 
            <a href="/fashion-shop/public/category.php">Sản phẩm</a> / 
            <span style="color: var(--text-main); font-weight: 500;"><?= htmlspecialchars($product['name']) ?></span>
        </div>
        <h1 class="detail-title"><?= htmlspecialchars($product['name']) ?></h1>
        
        <div class="detail-sku">
            Mã sản phẩm: <span id="displaySKU">--</span> | 
            Trạng thái: <?= $product['status'] == 'active' ? 'Đang kinh doanh' : 'Ngừng kinh doanh' ?>
        </div>
        
        <div class="detail-price" id="displayPrice">
            <?php if ($minPrice == $maxPrice): ?>
                <?= number_format($minPrice, 0, ',', '.') ?> ₫
            <?php else: ?>
                <?= number_format($minPrice, 0, ',', '.') ?> ₫ - <?= number_format($maxPrice, 0, ',', '.') ?> ₫
            <?php endif; ?>
        </div>
        
        <div class="detail-desc">
            <?= nl2br(htmlspecialchars($product['description'] ?: 'Chưa có mô tả chi tiết cho sản phẩm này.')) ?>
        </div>
        
        <?php if (!empty($colors)): ?>
        <div class="variant-group">
            <div class="variant-label">Màu sắc: <span id="selectedColorText" style="font-weight: 400; color: var(--text-muted);">-- Chọn --</span></div>
            <div class="variant-options" id="colorOptions">
                <?php foreach($colors as $color): ?>
                <div class="variant-btn" data-color="<?= htmlspecialchars($color) ?>" onclick="selectColor(this)"><?= htmlspecialchars($color) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($sizes)): ?>
        <div class="variant-group">
            <div class="variant-label">Kích thước (Size): <span id="selectedSizeText" style="font-weight: 400; color: var(--text-muted);">-- Chọn --</span></div>
            <div class="variant-options" id="sizeOptions">
                <?php foreach($sizes as $size): ?>
                <div class="variant-btn" data-size="<?= htmlspecialchars($size) ?>" onclick="selectSize(this)"><?= htmlspecialchars($size) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="variant-group" style="display: flex; align-items: center; gap: 24px; margin-top: 10px;">
            <div style="font-weight: 600;">Số lượng:</div>
            <div class="qty-control">
                <span id="btnMinus">-</span> 
                <span id="qtyValue">1</span> 
                <span id="btnPlus">+</span>
            </div>
            <div class="stock-status in-stock" id="stockStatus">
                <i class="fas fa-check-circle"></i> <span id="stockText">Vui lòng chọn phân loại</span>
            </div>
        </div>

        <div style="display: flex; gap: 16px; margin-top: 32px;">
            <button class="btn-outline" id="btnAddToCart" style="flex: 1; height: 56px; font-size: 16px;" disabled>
                <i class="fas fa-cart-plus" style="margin-right: 8px;"></i> Thêm vào giỏ
            </button>
            <button class="btn-primary" id="btnBuyNow" style="flex: 1; height: 56px; font-size: 16px;" disabled>Mua Ngay</button>
        </div>
        
        <div style="margin-top: 32px; padding: 20px; background: var(--bg); border-radius: 8px; font-size: 14px; color: #4B5563; display: flex; flex-direction: column; gap: 12px; border: 1px solid var(--border);">
            <div style="display: flex; gap: 12px; align-items: center;"><i class="fas fa-truck" style="color: var(--primary); font-size: 18px; width: 24px;"></i> Giao hàng toàn quốc từ 2-4 ngày</div>
            <div style="display: flex; gap: 12px; align-items: center;"><i class="fas fa-undo" style="color: var(--primary); font-size: 18px; width: 24px;"></i> Đổi trả miễn phí trong 30 ngày (nếu lỗi NSX)</div>
            <div style="display: flex; gap: 12px; align-items: center;"><i class="fas fa-shield-alt" style="color: var(--primary); font-size: 18px; width: 24px;"></i> Thanh toán an toàn khi nhận hàng (COD)</div>
        </div>
    </div>
</div>

<script>
    // ========================================================
    // DỮ LIỆU BIẾN THỂ TỪ DATABASE (Truyền từ PHP sang JS)
    // ========================================================
    const variantsData = <?= json_encode($variants) ?>;
    
    let selectedColor = null;
    let selectedSize = null;
    let qty = 1;
    let currentVariant = null;

    // ========================================================
    // THAY ĐỔI ẢNH CHÍNH KHI CLICK THUMBNAIL
    // ========================================================
    function changeMainImage(element, src) {
        const mainImg = document.getElementById('mainProductImage');
        if (mainImg) mainImg.src = src;
        document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
        element.classList.add('active');
    }

    // ========================================================
    // CHỌN MÀU SẮC
    // ========================================================
    function selectColor(el) {
        document.querySelectorAll('#colorOptions .variant-btn').forEach(b => b.classList.remove('active'));
        el.classList.add('active');
        selectedColor = el.dataset.color;
        document.getElementById('selectedColorText').textContent = selectedColor;
        
        // Cập nhật size nào khả dụng cho màu đã chọn
        updateAvailableSizes();
        updateSelectedVariant();
    }

    // ========================================================
    // CHỌN KÍCH THƯỚC
    // ========================================================
    function selectSize(el) {
        if (el.classList.contains('disabled')) return;
        document.querySelectorAll('#sizeOptions .variant-btn').forEach(b => b.classList.remove('active'));
        el.classList.add('active');
        selectedSize = el.dataset.size;
        document.getElementById('selectedSizeText').textContent = selectedSize;
        
        // Cập nhật màu nào khả dụng cho size đã chọn
        updateAvailableColors();
        updateSelectedVariant();
    }

    // ========================================================
    // CẬP NHẬT SIZE KHẢ DỤNG (dựa trên màu đã chọn)
    // ========================================================
    function updateAvailableSizes() {
        if (!selectedColor) return;
        const sizeButtons = document.querySelectorAll('#sizeOptions .variant-btn');
        const availableSizes = variantsData
            .filter(v => v.color.trim() === selectedColor && parseInt(v.stock_quantity) > 0)
            .map(v => v.size.trim());
        
        sizeButtons.forEach(btn => {
            const s = btn.dataset.size;
            // Kiểm tra xem tổ hợp này có tồn tại trong DB không
            const exists = variantsData.some(v => v.color.trim() === selectedColor && v.size.trim() === s);
            if (!exists) {
                btn.classList.add('disabled');
                btn.classList.remove('active');
            } else {
                btn.classList.remove('disabled');
            }
        });
    }

    // ========================================================
    // CẬP NHẬT MÀU KHẢ DỤNG (dựa trên size đã chọn)
    // ========================================================
    function updateAvailableColors() {
        if (!selectedSize) return;
        const colorButtons = document.querySelectorAll('#colorOptions .variant-btn');
        
        colorButtons.forEach(btn => {
            const c = btn.dataset.color;
            const exists = variantsData.some(v => v.size.trim() === selectedSize && v.color.trim() === c);
            if (!exists) {
                btn.classList.add('disabled');
                btn.classList.remove('active');
            } else {
                btn.classList.remove('disabled');
            }
        });
    }

    // ========================================================
    // CẬP NHẬT GIÁ + TỒN KHO KHI CHỌN XONG CẢ MÀU VÀ SIZE
    // ========================================================
    function updateSelectedVariant() {
        if (!selectedColor || !selectedSize) {
            currentVariant = null;
            return;
        }

        // Tìm biến thể khớp chính xác
        currentVariant = variantsData.find(v => 
            v.color.trim() === selectedColor && v.size.trim() === selectedSize
        );

        const priceEl = document.getElementById('displayPrice');
        const skuEl = document.getElementById('displaySKU');
        const stockStatusEl = document.getElementById('stockStatus');
        const stockTextEl = document.getElementById('stockText');
        const btnAddToCart = document.getElementById('btnAddToCart');
        const btnBuyNow = document.getElementById('btnBuyNow');

        if (currentVariant) {
            // Cập nhật giá
            const formattedPrice = parseInt(currentVariant.price).toLocaleString('vi-VN');
            priceEl.textContent = formattedPrice + ' ₫';
            
            // Cập nhật SKU
            skuEl.textContent = currentVariant.sku;

            // Cập nhật tồn kho
            const stock = parseInt(currentVariant.stock_quantity);
            if (stock > 0) {
                stockStatusEl.className = 'stock-status in-stock';
                stockTextEl.innerHTML = `<i class="fas fa-check-circle"></i> Còn ${stock} sản phẩm`;
                btnAddToCart.disabled = false;
                btnBuyNow.disabled = false;
            } else {
                stockStatusEl.className = 'stock-status out-of-stock';
                stockTextEl.innerHTML = `<i class="fas fa-times-circle"></i> Hết hàng`;
                btnAddToCart.disabled = true;
                btnBuyNow.disabled = true;
            }

            // Reset quantity
            qty = 1;
            document.getElementById('qtyValue').innerText = qty;
        } else {
            priceEl.textContent = 'Không tìm thấy biến thể';
            stockTextEl.textContent = 'Tổ hợp này không tồn tại';
            stockStatusEl.className = 'stock-status out-of-stock';
            btnAddToCart.disabled = true;
            btnBuyNow.disabled = true;
        }
    }

    // ========================================================
    // SỐ LƯỢNG +/-
    // ========================================================
    document.getElementById('btnMinus').addEventListener('click', () => {
        if(qty > 1) { qty--; document.getElementById('qtyValue').innerText = qty; }
    });
    document.getElementById('btnPlus').addEventListener('click', () => {
        if (currentVariant) {
            const maxStock = parseInt(currentVariant.stock_quantity);
            if (qty < maxStock) {
                qty++; 
                document.getElementById('qtyValue').innerText = qty;
            }
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
