<?php
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/helpers/escape.php';
require_once __DIR__ . '/../app/helpers/session.php';

$csrf_token = csrf_token();
$error = get_flash_message('error');
$success = get_flash_message('success');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập & Đăng ký - Fashion Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <style>
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 500; }
        .alert-error { background-color: #FEE2E2; color: #B91C1C; border: 1px solid #F87171; }
        .alert-success { background-color: #D1FAE5; color: #047857; border: 1px solid #34D399; }
    </style>
</head>
<body>

    <!-- Notification Toast if needed -->
    <?php if ($error || $success): ?>
    <div style="position: absolute; top: 20px; left: 50%; transform: translateX(-50%); z-index: 1000; width: 400px; text-align: center;">
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="auth-container" id="authContainer">
        
        <!-- ==========================================
             MÀN HÌNH ĐĂNG KÝ (BÊN TRÁI ẨN)
        =========================================== -->
        <div class="form-container sign-up-container">
            <div class="auth-form">
                <div class="logo">FASHION<span>SHOP</span></div>
                
                <div class="form-header" style="margin-bottom: 24px;">
                    <h1>Đăng Ký Tài Khoản</h1>
                    <p>Điền thông tin bên dưới để tạo tài khoản mới</p>
                </div>

                <form action="/fashion-shop/api/auth/register.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                    <div class="form-group">
                        <label class="form-label">Họ và Tên</label>
                        <input type="text" name="full_name" class="form-input" placeholder="Ví dụ: Nguyễn Văn A" required>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone" class="form-input" placeholder="SĐT dùng để đăng nhập" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email (Tùy chọn)</label>
                            <input type="email" name="email" class="form-input" placeholder="example@email.com">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Xác nhận mật khẩu</label>
                            <input type="password" name="password_confirm" class="form-input" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" style="margin-top: 8px;">Tạo Tài Khoản Khách Hàng</button>
                </form>

                <div class="auth-switch">
                    Đã có tài khoản? <span id="signInBtn">Đăng nhập tại đây</span>
                </div>
            </div>
        </div>

        <!-- ==========================================
             MÀN HÌNH ĐĂNG NHẬP (BÊN TRÁI HIỆN THỊ)
        =========================================== -->
        <div class="form-container sign-in-container">
            <div class="auth-form">
                <div class="logo">FASHION<span>SHOP</span></div>
                
                <div class="form-header">
                    <h1>Chào mừng trở lại! 👋</h1>
                    <p>Vui lòng đăng nhập vào tài khoản của bạn</p>
                </div>

                <form action="/fashion-shop/api/auth/login.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                    <div class="form-group">
                        <label class="form-label">Email hoặc Số điện thoại</label>
                        <input type="text" name="username" class="form-input" placeholder="Ví dụ: 0987654321" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember"> Ghi nhớ đăng nhập
                        </label>
                        <a href="#" class="forgot-link">Quên mật khẩu?</a>
                    </div>

                    <button type="submit" class="btn-primary">Đăng Nhập</button>
                </form>

                <div class="auth-switch">
                    Chưa có tài khoản? <span id="signUpBtn">Đăng ký ngay</span>
                </div>
            </div>
        </div>

        <!-- ==========================================
             OVERLAY BANNER (HIỆU ỨNG TRƯỢT)
        =========================================== -->
        <div class="overlay-container">
            <div class="overlay">
                <!-- Banner cho trang Đăng nhập -->
                <div class="overlay-panel overlay-left">
                    <div class="banner-content">
                        <div class="banner-title">Tham gia<br>Cùng chúng tôi.</div>
                        <div class="banner-desc">Tạo tài khoản ngay hôm nay để tích điểm thành viên, nhận ngay chiết khấu và theo dõi lịch sử mua hàng dễ dàng.</div>
                    </div>
                </div>
                <!-- Banner cho trang Đăng ký -->
                <div class="overlay-panel overlay-right">
                    <div class="banner-content">
                        <div class="banner-title">Khám phá<br>Phong cách mới.</div>
                        <div class="banner-desc">Đăng nhập để trải nghiệm mua sắm tuyệt vời, quản lý đơn hàng và nhận ưu đãi độc quyền dành riêng cho bạn.</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="../assets/js/auth.js"></script>
</body>
</html>
