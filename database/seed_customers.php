<?php
require_once __DIR__ . '/../app/config/Database.php';

$db = Database::getInstance()->getConnection();

$firstNames = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương'];
$lastNames = ['Anh', 'Bình', 'Châu', 'Dung', 'Em', 'Phong', 'Giang', 'Hải', 'Linh', 'Quang', 'Sơn', 'Tâm', 'Uyên', 'Vân', 'Yến', 'Minh', 'Khang', 'Nhi', 'Hùng', 'Thảo'];
$cities = ['Hồ Chí Minh', 'Hà Nội', 'Đà Nẵng', 'Cần Thơ', 'Hải Phòng'];
$districts = ['Quận 1', 'Quận 3', 'Quận 7', 'Bình Thạnh', 'Tân Bình', 'Cầu Giấy', 'Đống Đa', 'Hải Châu', 'Sơn Trà', 'Ninh Kiều'];

try {
    $db->beginTransaction();

    // 1. Ensure tier exists for foreign key
    $db->exec("INSERT IGNORE INTO customer_tiers (id, name, min_points, discount_percent) VALUES (1, 'Thành viên mới', 0, 0)");

    for ($i = 0; $i < 15; $i++) {
        // Generate random customer info
        $fn = $firstNames[array_rand($firstNames)];
        $ln = $lastNames[array_rand($lastNames)];
        $fullName = $fn . ' ' . $ln;
        // Make email unique
        $email = strtolower(str_replace(' ', '', $ln)) . $i . rand(100, 999) . '@example.com';
        $phone = '09' . rand(10000000, 99999999);
        $status = (rand(1, 10) > 8) ? 'banned' : 'active'; // 20% banned
        $points = rand(0, 500);

        // Created at randomized up to 30 days ago
        $createdAt = date('Y-m-d H:i:s', strtotime('-' . rand(0, 30) . ' days'));

        $stmt = $db->prepare("INSERT INTO customers (tier_id, full_name, email, password_hash, phone, points, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            1, // tier_id
            $fullName,
            $email,
            password_hash('123456', PASSWORD_DEFAULT),
            $phone,
            $points,
            $status,
            $createdAt
        ]);
        $customerId = $db->lastInsertId();

        // 2. Generate random addresses for customer
        $numAddresses = rand(1, 3); // 1 to 3 addresses
        for ($j = 0; $j < $numAddresses; $j++) {
            $isDefault = ($j === 0) ? 1 : 0; // First address is default
            $addressLine = rand(1, 500) . ' Đường ' . $firstNames[array_rand($firstNames)];
            $city = $cities[array_rand($cities)];
            $district = $districts[array_rand($districts)];

            $stmtAddr = $db->prepare("INSERT INTO customer_addresses (customer_id, is_default, receiver_name, phone, address_line, city, district, ward) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtAddr->execute([
                $customerId,
                $isDefault,
                $fullName,
                $phone, // Usually same as customer phone for mock
                $addressLine,
                $city,
                $district,
                'Phường ' . rand(1, 15)
            ]);
        }
    }

    $db->commit();
    echo "Tạo dữ liệu Khách hàng mẫu thành công!\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "Lỗi: " . $e->getMessage() . "\n";
}
