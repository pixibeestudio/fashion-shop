<?php
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/helpers/session.php';

// Lấy tên người dùng từ Session (Admin hoặc Customer)
$userName = $_SESSION['admin_name'] ?? $_SESSION['customer_name'] ?? 'Admin Manager';
$initial = strtoupper(mb_substr($userName, 0, 1, 'UTF-8'));

$page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Fashion Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/fashion-shop/assets/css/admin.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">FashionShop</div>
        <div class="menu" id="adminMenu">
            <a href="index.php?page=dashboard" class="menu-item <?= $page === 'dashboard' ? 'active' : '' ?>">📊 Tổng quan</a>
            <a href="index.php?page=orders" class="menu-item <?= $page === 'orders' ? 'active' : '' ?>">📦 Đơn hàng</a>
            <a href="index.php?page=products" class="menu-item <?= $page === 'products' ? 'active' : '' ?>">👕 Sản phẩm</a>
            <a href="index.php?page=categories" class="menu-item <?= $page === 'categories' ? 'active' : '' ?>">📂 Danh mục</a>
            <a href="index.php?page=purchase_orders" class="menu-item <?= ($page === 'purchase_orders' || $page === 'purchase_order_create') ? 'active' : '' ?>">📥 Nhập kho</a>
            <a href="index.php?page=customers" class="menu-item <?= $page === 'customers' ? 'active' : '' ?>">👥 Khách hàng</a>
            <a href="index.php?page=promotions" class="menu-item <?= $page === 'promotions' ? 'active' : '' ?>">🎁 Khuyến mãi</a>
            <a href="index.php?page=system" class="menu-item <?= $page === 'system' ? 'active' : '' ?>">⚙️ Hệ thống</a>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main">
        <div class="header">
            <div class="header-search">
                🔍 <input type="text" placeholder="Tìm kiếm..." id="headerSearchInput">
            </div>
            <div class="user-profile" id="userProfileBtn">
                <div style="text-align: right;">
                    <span><?= htmlspecialchars($userName) ?></span>
                    <small>Online</small>
                </div>
                <div class="avatar"><?= htmlspecialchars($initial) ?></div>
                
                <!-- Dropdown Menu Đăng xuất -->
                <div id="userDropdown" class="user-dropdown">
                    <a href="/fashion-shop/api/auth/logout.php">🚪 Đăng xuất</a>
                </div>
            </div>
        </div>
        
        <div class="content" id="mainContent">
            <?php
                $viewPath = __DIR__ . "/views/{$page}.php";
                if (file_exists($viewPath)) {
                    include $viewPath;
                } else {
                    echo "<p style='color:#64748b; margin-top:20px;'>Giao diện chức năng <b>{$page}</b> đang được xây dựng...</p>";
                }
            ?>
        </div>
    </div>

    <!-- Common JS -->
    <script src="/fashion-shop/assets/js/admin.js"></script>
</body>
</html>
