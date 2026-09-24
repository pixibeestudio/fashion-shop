<?php
require_once __DIR__ . '/../app/helpers/session.php';
$customerName = $_SESSION['customer_name'] ?? null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fashion Shop - Trang Chủ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .container { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); text-align: center; }
        h1 { color: #1e293b; margin-bottom: 10px; }
        p { color: #64748b; margin-bottom: 24px; }
        .btn { background: #0f172a; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: 500; }
        .btn-outline { background: transparent; color: #0f172a; border: 1px solid #0f172a; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: 500; margin-left: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Fashion Shop</h1>
        <?php if ($customerName): ?>
            <p>Xin chào Khách hàng <b><?= htmlspecialchars($customerName) ?></b>!</p>
            <p><i>Giao diện Storefront (Trang chủ mua hàng) đang được xây dựng...</i></p>
            <a href="/fashion-shop/api/auth/logout.php" class="btn">Đăng xuất</a>
        <?php else: ?>
            <p>Chào mừng bạn đến với Fashion Shop.</p>
            <p><i>Giao diện Storefront (Trang chủ mua hàng) đang được xây dựng...</i></p>
            <a href="/fashion-shop/public/login.php" class="btn">Đăng nhập</a>
        <?php endif; ?>
    </div>
</body>
</html>
