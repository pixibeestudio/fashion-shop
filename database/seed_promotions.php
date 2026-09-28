<?php
require_once __DIR__ . '/../app/config/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Xóa dữ liệu cũ (reset)
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $db->exec("TRUNCATE TABLE promotions;");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    $now = new DateTime();
    $tomorrow = (new DateTime())->modify('+1 day');
    $nextMonth = (new DateTime())->modify('+1 month');
    $yesterday = (new DateTime())->modify('-1 day');
    $lastMonth = (new DateTime())->modify('-1 month');
    
    // 1. Đang diễn ra (Active)
    $stmt = $db->prepare("INSERT INTO promotions (code, discount_type, discount_value, start_date, end_date, status) VALUES (:code, :type, :val, :start, :end, 'active')");
    
    $stmt->execute([
        'code' => 'WELCOME20',
        'type' => 'percent',
        'val' => 20.00,
        'start' => $lastMonth->format('Y-m-d H:i:s'),
        'end' => $nextMonth->format('Y-m-d H:i:s')
    ]);

    // 2. Sắp tới (Upcoming - Active status nhưng start_date trong tương lai)
    $stmt->execute([
        'code' => 'SUMMER50K',
        'type' => 'fixed',
        'val' => 50000.00,
        'start' => $tomorrow->format('Y-m-d H:i:s'),
        'end' => $nextMonth->format('Y-m-d H:i:s')
    ]);

    // 3. Đã hết hạn (Expired - Active status nhưng end_date trong quá khứ)
    $stmt->execute([
        'code' => 'FLASH10',
        'type' => 'percent',
        'val' => 10.00,
        'start' => $lastMonth->format('Y-m-d H:i:s'),
        'end' => $yesterday->format('Y-m-d H:i:s')
    ]);

    // 4. Bị vô hiệu hóa (Disabled)
    $db->prepare("INSERT INTO promotions (code, discount_type, discount_value, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, 'disabled')")
       ->execute(['BLACKFRIDAY', 'percent', 50.00, $lastMonth->format('Y-m-d H:i:s'), $nextMonth->format('Y-m-d H:i:s')]);

    echo "Khởi tạo dữ liệu Promotions thành công!\n";

} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
}
