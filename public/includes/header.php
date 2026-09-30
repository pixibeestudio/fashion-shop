<?php
require_once __DIR__ . '/../../app/helpers/session.php';
$customerName = $_SESSION['customer_name'] ?? null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Fashion Shop - Thời Trang Chính Hãng' ?></title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/fashion-shop/assets/css/storefront.css">
</head>
<body>

    <!-- Header -->
    <div class="store-header-wrapper">
        <header class="store-header">
            <a href="/fashion-shop/public/index.php" class="store-logo">FASHION<span>SHOP</span></a>
        
        <nav class="store-nav">
            <a href="/fashion-shop/public/index.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>">Trang chủ</a>
            <a href="/fashion-shop/public/category.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'category.php') ? 'active' : '' ?>">Bộ sưu tập</a>
            <a href="/fashion-shop/public/category.php?cat=ao-nam">Áo Nam</a>
            <a href="/fashion-shop/public/category.php?cat=quan-nu">Quần Nữ</a>
            <a href="/fashion-shop/public/category.php?sale=1" style="color: var(--danger); font-weight: 600;">Sale Off</a>
        </nav>
        
        <div class="store-search">
            <form action="/fashion-shop/public/category.php" method="GET">
                <input type="text" name="q" placeholder="Tìm kiếm áo thun, quần jean...">
            </form>
        </div>
        
        <div class="store-actions">
            <?php if ($customerName): ?>
                <a href="/fashion-shop/public/profile.php" class="action-icon" title="Tài khoản của bạn" style="width: auto; padding: 0 16px; border-radius: 20px; font-size: 14px; font-weight: 600; gap: 8px;">
                    <i class="far fa-user-circle" style="font-size: 18px;"></i>
                    <?= htmlspecialchars($customerName) ?>
                </a>
                <a href="/fashion-shop/api/auth/logout.php" class="action-icon" title="Đăng xuất"><i class="fas fa-sign-out-alt"></i></a>
            <?php else: ?>
                <a href="/fashion-shop/public/login.php" class="action-icon" title="Đăng nhập"><i class="far fa-user"></i></a>
            <?php endif; ?>
            
            <a href="/fashion-shop/public/profile.php" class="action-icon" title="Dò đơn hàng"><i class="fas fa-box"></i></a>
            
            <a href="/fashion-shop/public/cart.php" class="action-icon" title="Giỏ hàng">
                <i class="fas fa-shopping-cart"></i>
                <div class="cart-badge" id="cartCountBadge">0</div>
            </a>
        </div>
        </header>
    </div>

    <div class="main-content">
