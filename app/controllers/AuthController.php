<?php
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/Validator.php';

class AuthController {
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        // Verify CSRF
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            set_flash_message('error', 'Lỗi xác thực (CSRF). Vui lòng tải lại trang và thử lại.');
            header('Location: /fashion-shop/public/login.php');
            exit;
        }

        // Sanitize input
        $fullName = Validator::sanitize($_POST['full_name'] ?? '');
        $phone = Validator::sanitize($_POST['phone'] ?? '');
        $email = Validator::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Input Validation
        if (empty($fullName) || empty($phone) || empty($password)) {
            set_flash_message('error', 'Vui lòng điền đầy đủ các thông tin bắt buộc.');
            header('Location: /fashion-shop/public/login.php');
            exit;
        }

        // Business Rules
        if (Validator::isReservedName($fullName)) {
            set_flash_message('error', 'Tên này không được phép sử dụng trong hệ thống.');
            header('Location: /fashion-shop/public/login.php');
            exit;
        }

        if (!Validator::isPhone($phone)) {
            set_flash_message('error', 'Số điện thoại không hợp lệ. Vui lòng nhập số điện thoại Việt Nam (10 số).');
            header('Location: /fashion-shop/public/login.php');
            exit;
        }

        if (!empty($email) && !Validator::isEmail($email)) {
            set_flash_message('error', 'Định dạng email không hợp lệ.');
            header('Location: /fashion-shop/public/login.php');
            exit;
        }

        if (!Validator::isStrongPassword($password)) {
            set_flash_message('error', 'Mật khẩu phải từ 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.');
            header('Location: /fashion-shop/public/login.php');
            exit;
        }

        if ($password !== $passwordConfirm) {
            set_flash_message('error', 'Mật khẩu xác nhận không khớp.');
            header('Location: /fashion-shop/public/login.php');
            exit;
        }

        $customerModel = new Customer();

        // Database Constraints Validation
        if ($customerModel->checkPhoneExists($phone)) {
            set_flash_message('error', 'Số điện thoại này đã được đăng ký.');
            header('Location: /fashion-shop/public/login.php');
            exit;
        }

        if ($customerModel->checkEmailExists($email)) {
            set_flash_message('error', 'Email này đã được đăng ký.');
            header('Location: /fashion-shop/public/login.php');
            exit;
        }

        // Create
        $success = $customerModel->create([
            'full_name' => $fullName,
            'phone' => $phone,
            'email' => $email,
            'password' => $password
        ]);

        if ($success) {
            set_flash_message('success', 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.');
            header('Location: /fashion-shop/public/login.php');
            exit;
        } else {
            set_flash_message('error', 'Lỗi hệ thống trong quá trình đăng ký. Vui lòng thử lại sau.');
            header('Location: /fashion-shop/public/login.php');
            exit;
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        // Verify CSRF
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            set_flash_message('error', 'Lỗi xác thực (CSRF). Vui lòng thử lại.');
            header('Location: /fashion-shop/public/login.php');
            exit;
        }

        $username = Validator::sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            set_flash_message('error', 'Vui lòng nhập tài khoản và mật khẩu.');
            header('Location: /fashion-shop/public/login.php');
            exit;
        }

        // 1. Kiểm tra bảng Users (Admin/Nhân viên) trước
        $userModel = new User();
        $admin = $userModel->findByPhoneOrEmail($username);

        if ($admin && password_verify($password, $admin['password_hash'])) {
            if ($admin['status'] !== 'active') {
                set_flash_message('error', 'Tài khoản quản trị của bạn đã bị khóa.');
                header('Location: /fashion-shop/public/login.php');
                exit;
            }
            // Đăng nhập Admin thành công
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['role_id'] = $admin['role_id'];
            
            header('Location: /fashion-shop/admin/index.php');
            exit;
        }

        // 2. Nếu không phải Admin, kiểm tra bảng Customers (Khách hàng)
        $customerModel = new Customer();
        $customer = $customerModel->findByPhoneOrEmail($username);

        if ($customer && password_verify($password, $customer['password_hash'])) {
            if ($customer['status'] !== 'active') {
                set_flash_message('error', 'Tài khoản của bạn đã bị khóa.');
                header('Location: /fashion-shop/public/login.php');
                exit;
            }

            // Đăng nhập Khách hàng thành công
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['full_name'];
            
            // Tạm thời điều hướng Khách hàng vào trang Admin theo yêu cầu test của bạn
            header('Location: /fashion-shop/admin/index.php');
            exit;
        }

        // Đăng nhập thất bại (Không khớp cả Admin và Customer)
        set_flash_message('error', 'Tài khoản hoặc mật khẩu không chính xác.');
        header('Location: /fashion-shop/public/login.php');
        exit;
    }
    
    public function logout() {
        session_destroy();
        session_start();
        set_flash_message('success', 'Bạn đã đăng xuất.');
        header('Location: /fashion-shop/public/login.php');
        exit;
    }
}
