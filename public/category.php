<?php
require_once __DIR__ . '/../app/config/Database.php';

$pageTitle = "Danh mục Sản Phẩm - Fashion Shop";
require_once __DIR__ . '/includes/header.php';

$db = Database::getInstance()->getConnection();

// Lọc đơn giản
$search = $_GET['q'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$stock = $_GET['stock'] ?? '';
$priceFilter = $_GET['price'] ?? '';
$sizeFilter = $_GET['size'] ?? '';

$sql = "SELECT p.id, p.name, 
        (SELECT price FROM product_variants WHERE product_id = p.id ORDER BY price ASC LIMIT 1) as price,
        (SELECT sale_price FROM product_variants WHERE product_id = p.id AND sale_price > 0 ORDER BY sale_price ASC LIMIT 1) as sale_price,
        (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as main_image,
        (SELECT SUM(stock_quantity) FROM product_variants WHERE product_id = p.id) as total_stock,
        (SELECT GROUP_CONCAT(DISTINCT color SEPARATOR ',') FROM product_variants WHERE product_id = p.id) as colors
        FROM products p WHERE p.status = 'active'";
$params = [];

// Filter by category slug
$catSlug = $_GET['cat'] ?? '';
if (!empty($catSlug)) {
    // We want to find the category ID and its children
    $stmtCat = $db->prepare("SELECT id FROM categories WHERE slug = :slug");
    $stmtCat->execute(['slug' => $catSlug]);
    $catRow = $stmtCat->fetch(PDO::FETCH_ASSOC);
    if ($catRow) {
        $catId = $catRow['id'];
        $sql .= " AND (p.category_id = :catId1 OR p.category_id IN (SELECT id FROM categories WHERE parent_id = :catId2))";
        $params['catId1'] = $catId;
        $params['catId2'] = $catId;
    }
}

// Filter by sale
$sale = $_GET['sale'] ?? '';
if ($sale == '1') {
    $sql .= " AND EXISTS (SELECT 1 FROM product_variants WHERE product_id = p.id AND sale_price IS NOT NULL AND sale_price > 0)";
}

function getColorHex($colorName) {
    $map = [
        'trắng' => '#ffffff', 'đen' => '#000000', 'đỏ' => '#ef4444', 
        'xanh' => '#3b82f6', 'vàng' => '#eab308', 'xám' => '#9ca3af',
        'nâu' => '#78350f', 'hồng' => '#ec4899', 'be' => '#f5f5dc',
        'white' => '#ffffff', 'black' => '#000000', 'red' => '#ef4444',
        'blue' => '#3b82f6', 'yellow' => '#eab308', 'gray' => '#9ca3af',
        'brown' => '#78350f', 'pink' => '#ec4899', 'beige' => '#f5f5dc'
    ];
    $key = mb_strtolower(trim($colorName), 'UTF-8');
    return $map[$key] ?? '#cccccc';
}

if (!empty($search)) {
    $sql .= " AND p.name LIKE :search";
    $params['search'] = "%{$search}%";
}

if ($stock === '1') {
    $sql .= " AND (SELECT SUM(stock_quantity) FROM product_variants WHERE product_id = p.id) > 0";
}

if (!empty($priceFilter)) {
    if ($priceFilter == 'under_200') {
        $sql .= " AND (SELECT price FROM product_variants WHERE product_id = p.id ORDER BY price ASC LIMIT 1) < 200000";
    } elseif ($priceFilter == '200_500') {
        $sql .= " AND (SELECT price FROM product_variants WHERE product_id = p.id ORDER BY price ASC LIMIT 1) BETWEEN 200000 AND 500000";
    } elseif ($priceFilter == 'over_500') {
        $sql .= " AND (SELECT price FROM product_variants WHERE product_id = p.id ORDER BY price ASC LIMIT 1) > 500000";
    }
}

if (!empty($sizeFilter)) {
    $sql .= " AND p.id IN (SELECT product_id FROM product_variants WHERE size = :size)";
    $params['size'] = $sizeFilter;
}

if ($sort === 'price_asc') {
    $sql .= " ORDER BY price ASC";
} elseif ($sort === 'price_desc') {
    $sql .= " ORDER BY price DESC";
} else {
    $sql .= " ORDER BY p.id DESC";
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<style>
/* Grid Layout specific for category page */
.category-layout { display: flex; padding: 40px 60px; gap: 40px; }
.filter-sidebar { width: 250px; flex-shrink: 0; }
.filter-group { margin-bottom: 32px; border-bottom: 1px solid var(--border); padding-bottom: 24px;}
.filter-group:last-child { border-bottom: none; }
.filter-title { font-weight: 600; font-size: 16px; color: var(--text-main); margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.5px;}
.checkbox-label { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; cursor: pointer; color: var(--text-muted); font-size: 14px;}
.checkbox-label input { width: 16px; height: 16px; cursor: pointer;}
.checkbox-label:hover { color: var(--text-main); }
.tag { padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; border: 1px solid var(--border); cursor: pointer; transition: 0.2s; }
.tag:hover { border-color: var(--text-main); color: var(--text-main); }
.tag.active { background: var(--text-main); color: white; border-color: var(--text-main); }
</style>

<div style="background: #F9FAFB; padding: 30px 60px; border-bottom: 1px solid var(--border);">
    <div style="font-size: 14px; color: var(--text-muted); margin-bottom: 8px;">
        <a href="/fashion-shop/public/index.php">Trang chủ</a> / <span style="color: var(--text-main); font-weight: 500;">Danh mục</span>
    </div>
    <h1 style="font-size: 28px; font-weight: 700; color: var(--text-main);">
        <?= !empty($search) ? "Kết quả tìm kiếm cho: '" . htmlspecialchars($search) . "'" : "Tất cả sản phẩm" ?> 
        <span style="font-size: 16px; color: var(--text-muted); font-weight: 400;">(<?= count($products) ?> SP)</span>
    </h1>
</div>

<div class="category-layout">
    <!-- SIDEBAR BỘ LỌC -->
    <div class="filter-sidebar">
        <form action="" method="GET" id="filterForm">
            <?php if(!empty($search)): ?><input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>"><?php endif; ?>
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <input type="hidden" name="size" id="sizeInput" value="<?= htmlspecialchars($sizeFilter) ?>">

            <div class="filter-group">
                <div class="filter-title">Trạng thái</div>
                <label class="checkbox-label"><input type="checkbox" name="stock" value="1" <?= $stock == '1' ? 'checked' : '' ?> onchange="document.getElementById('filterForm').submit()"> Còn hàng</label>
            </div>
            
            <div class="filter-group">
                <div class="filter-title">Khoảng giá</div>
                <label class="checkbox-label"><input type="radio" name="price" value="" <?= empty($priceFilter) ? 'checked' : '' ?> onchange="document.getElementById('filterForm').submit()"> Tất cả</label>
                <label class="checkbox-label"><input type="radio" name="price" value="under_200" <?= $priceFilter == 'under_200' ? 'checked' : '' ?> onchange="document.getElementById('filterForm').submit()"> Dưới 200,000 ₫</label>
                <label class="checkbox-label"><input type="radio" name="price" value="200_500" <?= $priceFilter == '200_500' ? 'checked' : '' ?> onchange="document.getElementById('filterForm').submit()"> Từ 200,000 ₫ - 500,000 ₫</label>
                <label class="checkbox-label"><input type="radio" name="price" value="over_500" <?= $priceFilter == 'over_500' ? 'checked' : '' ?> onchange="document.getElementById('filterForm').submit()"> Trên 500,000 ₫</label>
            </div>

            <div class="filter-group">
                <div class="filter-title">Kích thước (Size)</div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <?php 
                    $sizes = ['S', 'M', 'L', 'XL', 'XXL']; 
                    foreach($sizes as $s): 
                    ?>
                        <div class="tag <?= $sizeFilter == $s ? 'active' : '' ?>" onclick="document.getElementById('sizeInput').value='<?= $sizeFilter == $s ? '' : $s ?>'; document.getElementById('filterForm').submit();"><?= $s ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- LƯỚI SẢN PHẨM BÊN PHẢI -->
    <div style="flex: 1;">
        <div style="display: flex; justify-content: flex-end; margin-bottom: 24px;">
            <form action="" method="GET" id="sortForm">
                <?php if (!empty($search)): ?>
                    <input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>">
                <?php endif; ?>
                <?php if (!empty($stock)): ?>
                    <input type="hidden" name="stock" value="<?= htmlspecialchars($stock) ?>">
                <?php endif; ?>
                <?php if (!empty($priceFilter)): ?>
                    <input type="hidden" name="price" value="<?= htmlspecialchars($priceFilter) ?>">
                <?php endif; ?>
                <?php if (!empty($sizeFilter)): ?>
                    <input type="hidden" name="size" value="<?= htmlspecialchars($sizeFilter) ?>">
                <?php endif; ?>
                <select name="sort" onchange="document.getElementById('sortForm').submit()" style="padding: 10px 16px; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white; font-weight: 500;">
                    <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Sắp xếp: Mới nhất</option>
                    <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Giá: Thấp đến Cao</option>
                    <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Giá: Cao xuống Thấp</option>
                </select>
            </form>
        </div>
        
        <div class="product-grid" style="grid-template-columns: repeat(3, 1fr);">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $prod): 
                    $imageUrl = !empty($prod['main_image']) ? $prod['main_image'] : null;
                ?>
                    <div class="product-card" onclick="window.location.href='/fashion-shop/public/product.php?id=<?= $prod['id'] ?>'">
                        <div class="product-image" <?= $imageUrl ? 'style="background-image: url('.$imageUrl.'); background-size: cover; background-position: center;"' : '' ?>>
                            <?php if (!$imageUrl): ?>
                                <i class="fas fa-image" style="color: #9CA3AF;"></i>
                            <?php endif; ?>
                            <?php if (!empty($prod['sale_price']) && $prod['sale_price'] > 0): ?>
                                <div class="badge-sale">SALE</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <div class="product-name" title="<?= htmlspecialchars($prod['name']) ?>"><?= htmlspecialchars($prod['name']) ?></div>
                            <div class="product-price-row">
                                <?php if (!empty($prod['sale_price']) && $prod['sale_price'] > 0): ?>
                                    <span class="product-price"><?= number_format($prod['sale_price'], 0, ',', '.') ?> ₫</span>
                                    <span class="product-price" style="text-decoration: line-through; color: #9CA3AF; font-size: 14px; font-weight: normal; margin-left: 8px;"><?= number_format($prod['price'], 0, ',', '.') ?> ₫</span>
                                <?php else: ?>
                                    <span class="product-price"><?= number_format($prod['price'], 0, ',', '.') ?> ₫</span>
                                <?php endif; ?>
                            </div>
                            <div class="color-swatches">
                                <?php 
                                if(!empty($prod['colors'])) {
                                    $colors = explode(',', $prod['colors']);
                                    foreach($colors as $c) {
                                        $c = trim($c);
                                        if (empty($c)) continue;
                                        $hex = getColorHex($c);
                                        echo '<div class="swatch" style="background: '.$hex.';" title="'.htmlspecialchars($c).'"></div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: span 3; padding: 40px; text-align: center; background: white; border-radius: 12px; border: 1px dashed var(--border);">
                    <i class="fas fa-box-open" style="font-size: 40px; color: #cbd5e1; margin-bottom: 16px;"></i>
                    <p style="color: var(--text-muted); font-size: 16px;">Không tìm thấy sản phẩm nào phù hợp.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
