<?php
require_once __DIR__ . '/../app/helpers/session.php';
$customerName = $_SESSION['customer_name'] ?? null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fashion Shop - Thời Trang Chính Hãng</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/fashion-shop/assets/css/storefront.css">
</head>
<body>

    <!-- Header -->
    <header class="store-header">
        <a href="/fashion-shop/public/index.php" class="store-logo">FASHION<span>SHOP</span></a>
        
        <nav class="store-nav">
            <a href="#" class="active">Trang chủ</a>
            <a href="#">Bộ sưu tập</a>
            <a href="#">Áo Nam</a>
            <a href="#">Quần Nữ</a>
            <a href="#" style="color: var(--danger); font-weight: 600;">Sale Off</a>
        </nav>
        
        <div class="store-search">
            <input type="text" placeholder="Tìm kiếm áo thun, quần jean...">
        </div>
        
        <div class="store-actions">
            <?php if ($customerName): ?>
                <a href="#" class="action-icon" title="Tài khoản của bạn" style="width: auto; padding: 0 16px; border-radius: 20px; font-size: 14px; font-weight: 600; gap: 8px;">
                    <i class="far fa-user-circle" style="font-size: 18px;"></i>
                    <?= htmlspecialchars($customerName) ?>
                </a>
                <a href="/fashion-shop/api/auth/logout.php" class="action-icon" title="Đăng xuất"><i class="fas fa-sign-out-alt"></i></a>
            <?php else: ?>
                <a href="/fashion-shop/public/login.php" class="action-icon" title="Đăng nhập"><i class="far fa-user"></i></a>
            <?php endif; ?>
            
            <a href="#" class="action-icon" title="Dò đơn hàng"><i class="fas fa-box"></i></a>
            
            <a href="#" class="action-icon" title="Giỏ hàng">
                <i class="fas fa-shopping-cart"></i>
                <div class="cart-badge">2</div>
            </a>
        </div>
    </header>

    <div class="main-content">
        <!-- Hero Banner -->
        <div class="hero-banner">
            <div class="hero-text">
                <h1>Bộ Sưu Tập<br>Mới 2026</h1>
                <p>Phong cách thanh lịch, chất liệu giữ nhiệt cao cấp. Khám phá ngay những thiết kế mới nhất dành riêng cho mùa đông năm nay.</p>
                <a href="#" class="btn-primary">Mua Ngay <i class="fas fa-arrow-right" style="margin-left: 8px;"></i></a>
            </div>
            <div class="hero-mockup-img">
                <i class="fas fa-tshirt" style="color: #4F46E5;"></i>
            </div>
        </div>

        <!-- Flash Sale Section -->
        <div class="section-container">
            <div class="section-header">
                <div class="section-title">
                    Flash Sale <i class="fas fa-bolt" style="color: #F59E0B;"></i>
                </div>
                <a href="#" class="view-all">Xem tất cả <i class="fas fa-chevron-right" style="font-size: 12px;"></i></a>
            </div>
            
            <div class="product-grid">
                <!-- Mock Product 1 -->
                <div class="product-card">
                    <div class="product-image">
                        <i class="fas fa-tshirt" style="color: #6B7280;"></i>
                        <div class="badge-sale">-30%</div>
                    </div>
                    <div class="product-info">
                        <div class="product-category">Áo Nam</div>
                        <div class="product-name">Áo Polo Trơn Basic Cao Cấp</div>
                        <div class="product-price-row">
                            <span class="product-price">175.000 ₫</span>
                            <span class="product-old-price">250.000 ₫</span>
                        </div>
                        <div class="color-swatches">
                            <div class="swatch" style="background: #ffffff;" title="Trắng"></div>
                            <div class="swatch" style="background: #000000;" title="Đen"></div>
                            <div class="swatch" style="background: #1D4ED8;" title="Xanh Navy"></div>
                        </div>
                    </div>
                </div>

                <!-- Mock Product 2 -->
                <div class="product-card">
                    <div class="product-image">
                        <i class="fas fa-socks" style="color: #6B7280;"></i>
                        <div class="badge-sale">-20%</div>
                    </div>
                    <div class="product-info">
                        <div class="product-category">Quần Nữ</div>
                        <div class="product-name">Quần Jean Ống Rộng Ulzzang</div>
                        <div class="product-price-row">
                            <span class="product-price">360.000 ₫</span>
                            <span class="product-old-price">450.000 ₫</span>
                        </div>
                        <div class="color-swatches">
                            <div class="swatch" style="background: #60A5FA;"></div>
                            <div class="swatch" style="background: #1E3A8A;"></div>
                        </div>
                    </div>
                </div>

                <!-- Mock Product 3 -->
                <div class="product-card">
                    <div class="product-image">
                        <i class="fas fa-shopping-bag" style="color: #6B7280;"></i>
                    </div>
                    <div class="product-info">
                        <div class="product-category">Túi Xách</div>
                        <div class="product-name">Túi Đeo Chéo Thời Trang</div>
                        <div class="product-price-row">
                            <span class="product-price">350.000 ₫</span>
                        </div>
                        <div class="color-swatches">
                            <div class="swatch" style="background: #FCA5A5;"></div>
                            <div class="swatch" style="background: #FEF08A;"></div>
                        </div>
                    </div>
                </div>

                <!-- Mock Product 4 -->
                <div class="product-card">
                    <div class="product-image">
                        <i class="fas fa-vest" style="color: #6B7280;"></i>
                    </div>
                    <div class="product-info">
                        <div class="product-category">Áo Khoác</div>
                        <div class="product-name">Áo Khoác Gió Chống Nước</div>
                        <div class="product-price-row">
                            <span class="product-price">550.000 ₫</span>
                        </div>
                        <div class="color-swatches">
                            <div class="swatch" style="background: #000000;"></div>
                            <div class="swatch" style="background: #9CA3AF;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="store-footer">
        <div class="footer-col" style="grid-column: span 1;">
            <div style="font-size: 26px; font-weight: 800; color: white; margin-bottom: 24px; letter-spacing: -0.5px;">FASHION<span style="color: var(--primary)">SHOP</span></div>
            <p style="line-height: 1.7; margin-bottom: 24px; color: #9CA3AF;">Thương hiệu thời trang mang đến phong cách trẻ trung, hiện đại và chất lượng hàng đầu. Chúng tôi tự hào đồng hành cùng phong cách của bạn mỗi ngày.</p>
            <div style="display: flex; gap: 16px; font-size: 20px;">
                <a href="#" style="color: #9CA3AF; transition: 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#9CA3AF'"><i class="fab fa-facebook"></i></a>
                <a href="#" style="color: #9CA3AF; transition: 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#9CA3AF'"><i class="fab fa-instagram"></i></a>
                <a href="#" style="color: #9CA3AF; transition: 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#9CA3AF'"><i class="fab fa-tiktok"></i></a>
                <a href="#" style="color: #9CA3AF; transition: 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#9CA3AF'"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        <div class="footer-col">
            <h4>Về chúng tôi</h4>
            <ul>
                <li>Câu chuyện thương hiệu</li>
                <li>Hệ thống cửa hàng</li>
                <li>Tuyển dụng</li>
                <li>Tin tức thời trang</li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Chính sách</h4>
            <ul>
                <li>Chính sách đổi trả 30 ngày</li>
                <li>Chính sách thẻ thành viên</li>
                <li>Chính sách bảo mật</li>
                <li>Kiểm tra đơn hàng</li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Liên hệ</h4>
            <ul>
                <li><i class="fas fa-phone-alt" style="margin-right: 8px;"></i> Hotline: 1900 1234</li>
                <li><i class="fas fa-envelope" style="margin-right: 8px;"></i> cskh@fashionshop.vn</li>
                <li><i class="fas fa-map-marker-alt" style="margin-right: 8px;"></i> 123 Nguyễn Văn Linh, HN</li>
            </ul>
        </div>
    </footer>
    <div class="footer-bottom">
        &copy; 2026 Fashion Shop. All rights reserved.
    </div>

</body>
</html>
