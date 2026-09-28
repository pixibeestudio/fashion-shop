<?php
require_once __DIR__ . '/../app/config/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Tạo 1 vài khách hàng ảo nếu chưa có
    $db->exec("INSERT IGNORE INTO customers (id, full_name, email, password_hash, status) VALUES 
        (991, 'Nguyễn Văn A', 'a@fashionshop.com', '123456', 'active'),
        (992, 'Trần Thị B', 'b@fashionshop.com', '123456', 'active'),
        (993, 'Lê Văn C', 'c@fashionshop.com', '123456', 'active')
    ");

    // Lấy ID 1 vài sản phẩm có sẵn (giả sử có sẵn trong products)
    $stmt = $db->query("SELECT id, name FROM products LIMIT 5");
    $products = $stmt->fetchAll();
    
    if (empty($products)) {
        die("Chưa có sản phẩm nào trong DB để tạo mock data đơn hàng!\n");
    }

    // Tạo đơn hàng ảo trong 30 ngày qua
    $now = time();
    $day = 24 * 60 * 60;
    
    for ($i = 0; $i < 30; $i++) {
        $orderDate = date('Y-m-d H:i:s', $now - ($i * $day) - rand(0, $day/2));
        
        $customer_id = 991 + rand(0, 2);
        $order_number = 'ORD' . date('Ymd', strtotime($orderDate)) . rand(100, 999);
        
        // Random 1-3 items
        $num_items = rand(1, 3);
        $total = 0;
        $items = [];
        for ($j = 0; $j < $num_items; $j++) {
            $p = $products[rand(0, count($products) - 1)];
            $qty = rand(1, 3);
            $price = rand(100, 500) * 1000;
            $subtotal = $qty * $price;
            $total += $subtotal;
            $items[] = [
                'product_id' => $p['id'],
                'product_name' => $p['name'],
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $subtotal
            ];
        }
        
        $status_arr = ['pending', 'processing', 'shipped', 'delivered', 'delivered', 'delivered', 'cancelled'];
        $status = $status_arr[rand(0, count($status_arr) - 1)];
        $payment_status = ($status === 'delivered') ? 'paid' : 'unpaid';

        // Insert order
        $stmtOrder = $db->prepare("INSERT INTO orders (customer_id, order_number, subtotal, total, payment_status, shipping_status, shipping_address, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtOrder->execute([$customer_id, $order_number, $total, $total, $payment_status, $status, 'Hà Nội', $orderDate, $orderDate]);
        $order_id = $db->lastInsertId();

        // Insert order items
        foreach ($items as $item) {
            $stmtItem = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, sku, unit_price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtItem->execute([$order_id, $item['product_id'], $item['product_name'], 'SKU-' . rand(1000,9999), $item['price'], $item['qty'], $item['subtotal']]);
        }
    }

    echo "Khởi tạo dữ liệu Dashboard (Mock Data Orders) thành công!\n";

} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
}
