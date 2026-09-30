<?php
require_once __DIR__ . '/../app/config/Database.php';

$pageTitle = "Fashion Shop - Trang Chủ";
require_once __DIR__ . '/includes/header.php';

// Fetch Flash Sale Products from Database
$db = Database::getInstance()->getConnection();
// Lấy danh sách sản phẩm hiển thị trên trang chủ (lấy 4 sản phẩm)
$stmt = $db->query("SELECT p.id, p.name, 
                   (SELECT price FROM product_variants WHERE product_id = p.id ORDER BY price ASC LIMIT 1) as price,
                   (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as main_image,
                   (SELECT GROUP_CONCAT(DISTINCT color SEPARATOR ',') FROM product_variants WHERE product_id = p.id) as colors
                   FROM products p WHERE p.status = 'active' ORDER BY p.id DESC LIMIT 4");
$recentProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

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


?>

        <!-- Hero Banner -->
        <div class="hero-banner">
            <div class="hero-text">
                <h1>Bộ Sưu Tập<br>Mới 2026</h1>
                <p>Phong cách thanh lịch, chất liệu giữ nhiệt cao cấp. Khám phá ngay những thiết kế mới nhất dành riêng cho mùa đông năm nay.</p>
                <a href="/fashion-shop/public/category.php" class="btn-primary">Mua Ngay <i class="fas fa-arrow-right" style="margin-left: 8px;"></i></a>
            </div>
            <div class="hero-mockup-img">
                <i class="fas fa-tshirt" style="color: #4F46E5;"></i>
            </div>
        </div>

        <!-- Flash Sale Section -->
        <div class="section-container">
            <div class="section-header">
                <div class="section-title">
                    Sản Phẩm Mới <i class="fas fa-bolt" style="color: #F59E0B;"></i>
                </div>
                <a href="/fashion-shop/public/category.php" class="view-all">Xem tất cả <i class="fas fa-chevron-right" style="font-size: 12px;"></i></a>
            </div>
            
            <div class="product-grid">
                <?php if (!empty($recentProducts)): ?>
                    <?php foreach ($recentProducts as $prod): 
                        // Cấu hình ảnh hiển thị
                        $imageUrl = !empty($prod['main_image']) ? $prod['main_image'] : null;
                    ?>
                        <div class="product-card" onclick="window.location.href='/fashion-shop/public/product.php?id=<?= $prod['id'] ?>'">
                            <div class="product-image" <?= $imageUrl ? 'style="background-image: url('.$imageUrl.'); background-size: cover; background-position: center;"' : '' ?>>
                                <?php if (!$imageUrl): ?>
                                    <i class="fas fa-image" style="color: #9CA3AF;"></i>
                                <?php endif; ?>
                                <div class="badge-sale">MỚI</div>
                            </div>
                            <div class="product-info">
                                <div class="product-category">Thời trang</div>
                                <div class="product-name" title="<?= htmlspecialchars($prod['name']) ?>"><?= htmlspecialchars($prod['name']) ?></div>
                                <div class="product-price-row">
                                    <span class="product-price"><?= number_format($prod['price'], 0, ',', '.') ?> ₫</span>
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
                    <p style="grid-column: span 4; text-align: center; color: #6B7280;">Chưa có sản phẩm nào.</p>
                <?php endif; ?>
            </div>
        </div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
