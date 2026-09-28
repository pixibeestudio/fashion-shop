<?php
require_once __DIR__ . '/../app/config/Database.php';

$db = Database::getInstance()->getConnection();

try {
    $db->beginTransaction();

    // 1. Check and create a dummy customer
    $stmt = $db->query("SELECT id FROM customers LIMIT 1");
    $customer = $stmt->fetch();
    
    if (!$customer) {
        // Create tier first
        $db->exec("INSERT IGNORE INTO customer_tiers (id, name, min_spend, discount_percent) VALUES (1, 'Thành viên mới', 0, 0)");
        
        $stmt = $db->prepare("INSERT INTO customers (full_name, email, phone, password_hash, tier_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Nguyễn Văn Khách Hàng', 'khachhang@example.com', '0987654321', password_hash('123456', PASSWORD_DEFAULT), 1]);
        $customerId = $db->lastInsertId();
    } else {
        $customerId = $customer['id'];
    }

    // 2. Fetch some random product variants
    $stmt = $db->query("SELECT pv.id, pv.product_id, pv.sku, pv.color, pv.size, pv.price, p.name 
                        FROM product_variants pv 
                        JOIN products p ON pv.product_id = p.id 
                        LIMIT 3");
    $variants = $stmt->fetchAll();

    if (empty($variants)) {
        echo "Lỗi: Không có biến thể sản phẩm nào trong database để tạo đơn hàng ảo.\n";
        $db->rollBack();
        exit;
    }

    // 3. Create 5 mock orders
    $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    $paymentStatuses = ['unpaid', 'paid', 'paid', 'paid', 'refunded'];
    
    for ($i = 0; $i < 5; $i++) {
        $orderNumber = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
        $shippingStatus = $statuses[$i];
        $paymentStatus = $paymentStatuses[$i];
        $address = "123 Đường Số " . rand(1, 10) . ", Phường 1, Quận 1, TP. HCM";
        
        // Randomize subtotal and items
        $numItems = rand(1, 3);
        $subtotal = 0;
        $orderItemsData = [];
        
        for ($j = 0; $j < $numItems; $j++) {
            $v = $variants[array_rand($variants)];
            $qty = rand(1, 3);
            $price = $v['price'];
            $itemTotal = $qty * $price;
            $subtotal += $itemTotal;
            
            $orderItemsData[] = [
                'product_id' => $v['product_id'],
                'variant_id' => $v['id'],
                'name' => $v['name'],
                'sku' => $v['sku'],
                'color' => $v['color'],
                'size' => $v['size'],
                'price' => $price,
                'qty' => $qty,
                'itemTotal' => $itemTotal
            ];
        }
        
        $shippingFee = 30000;
        $discount = 0;
        $total = $subtotal + $shippingFee - $discount;

        // Insert Order
        $sqlOrder = "INSERT INTO orders (customer_id, order_number, subtotal, shipping_fee, discount, total, payment_method, payment_status, shipping_status, shipping_address, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtOrder = $db->prepare($sqlOrder);
        // Vary creation dates
        $createdAt = date('Y-m-d H:i:s', strtotime('-' . rand(0, 10) . ' days'));
        
        $stmtOrder->execute([
            $customerId,
            $orderNumber,
            $subtotal,
            $shippingFee,
            $discount,
            $total,
            'cod',
            $paymentStatus,
            $shippingStatus,
            $address,
            $createdAt
        ]);
        
        $orderId = $db->lastInsertId();

        // Insert Order Items
        $sqlItem = "INSERT INTO order_items (order_id, product_id, product_variant_id, product_name, sku, color, size, unit_price, quantity, subtotal) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtItem = $db->prepare($sqlItem);
        
        foreach ($orderItemsData as $item) {
            $stmtItem->execute([
                $orderId,
                $item['product_id'],
                $item['variant_id'],
                $item['name'],
                $item['sku'],
                $item['color'],
                $item['size'],
                $item['price'],
                $item['qty'],
                $item['itemTotal']
            ]);
        }
    }

    $db->commit();
    echo "Tạo dữ liệu đơn hàng mẫu thành công!\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "Lỗi: " . $e->getMessage() . "\n";
}
