<?php
require_once __DIR__ . '/../app/config/Database.php';

$db = Database::getInstance()->getConnection();

try {
    // 1. Disable FK checks temporarily for truncating
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // 2. Truncate tables
    $db->exec("TRUNCATE TABLE role_permissions;");
    $db->exec("TRUNCATE TABLE permissions;");
    $db->exec("TRUNCATE TABLE users;");
    $db->exec("TRUNCATE TABLE roles;");

    // 3. Remove admin from customers table
    $db->exec("DELETE FROM customers WHERE email LIKE '%admin%';");

    // 4. Create 3 Standard Roles
    $db->exec("INSERT INTO roles (id, name) VALUES (1, 'Admin'), (2, 'Thủ kho'), (3, 'Thu ngân');");

    // 5. Re-enable FK checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // 6. Create Default Admin User
    $adminPassword = password_hash('Admin@123!', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (role_id, full_name, email, password_hash, phone, status) VALUES (?, ?, ?, ?, ?, 'active')");
    $stmt->execute([
        1, // Admin Role ID
        'Hệ Thống Quản Trị',
        'admin@fashionshop.com',
        $adminPassword,
        '0999999999'
    ]);

    // 7. (Optional) Create mock users for Thủ kho and Thu ngân
    $thukhoPass = password_hash('Thukho@123!', PASSWORD_DEFAULT);
    $stmt->execute([2, 'Trần Thủ Kho', 'thukho@fashionshop.com', $thukhoPass, '0988888888']);
    
    $thunganPass = password_hash('Thungan@123!', PASSWORD_DEFAULT);
    $stmt->execute([3, 'Lê Thu Ngân', 'thungan@fashionshop.com', $thunganPass, '0977777777']);

    echo "Dọn dẹp hệ thống và khởi tạo thành công!\n";
    echo "Tài khoản Admin:\n";
    echo "Email: admin@fashionshop.com\n";
    echo "Pass: Admin@123!\n";

} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
}
