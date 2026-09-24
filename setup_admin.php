<?php
// Tự động tạo tài khoản Admin (Chạy 1 lần duy nhất để test)
require_once __DIR__ . '/app/config/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Tạo Role 'super_admin'
    $stmt = $db->query("SELECT id FROM roles WHERE name = 'super_admin'");
    $role = $stmt->fetch();
    
    if (!$role) {
        $db->query("INSERT INTO roles (name) VALUES ('super_admin')");
        $roleId = $db->lastInsertId();
    } else {
        $roleId = $role['id'];
    }

    // 2. Tạo User Admin
    $email = 'admin@fashionshop.com';
    $phone = '0999999999';
    $password = 'Admin@1234';
    
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    
    if (!$stmt->fetch()) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $db->prepare("INSERT INTO users (role_id, full_name, email, phone, password_hash, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $insert->execute([$roleId, 'Hệ Thống Quản Trị', $email, $phone, $hash]);
        echo "Tạo tài khoản Admin thành công!\n";
        echo "Email: " . $email . "\n";
        echo "Password: " . $password . "\n";
    } else {
        echo "Tài khoản Admin (admin@fashionshop.com) đã tồn tại trong database.\n";
    }
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage();
}
