<?php
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/CartController.php';
require_once __DIR__ . '/../config/Database.php';

class CheckoutController {
    private $db;
    private $cartController;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->cartController = new CartController();
    }

    public function process() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) $data = $_POST;

        $customerName = trim($data['customer_name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $address = trim($data['address'] ?? '');
        $email = trim($data['email'] ?? '');
        $note = trim($data['note'] ?? '');
        $paymentMethod = $data['payment_method'] ?? 'cod';
        $customerId = $_SESSION['customer_id'] ?? null;

        if (empty($customerName) || empty($phone) || empty($address)) {
            $this->jsonResponse(false, 'Vui lòng điền đầy đủ Họ tên, Số điện thoại và Địa chỉ.');
            return;
        }

        $cartData = $this->cartController->getCartItems();
        $items = $cartData['items'];
        $totalAmount = $cartData['total'];

        if (empty($items)) {
            $this->jsonResponse(false, 'Giỏ hàng trống.');
            return;
        }

        try {
            $this->db->beginTransaction();

            // 1. Double check stock for all items
            foreach ($items as $item) {
                $stmt = $this->db->prepare("SELECT stock_quantity FROM product_variants WHERE id = :id FOR UPDATE");
                $stmt->execute(['id' => $item['variant_id']]);
                $stock = $stmt->fetchColumn();

                if ($stock === false || $stock < $item['quantity']) {
                    throw new Exception("Sản phẩm '{$item['product_name']} ({$item['color']}-{$item['size']})' không đủ số lượng tồn kho (còn $stock).");
                }
            }

            // 2. Generate Order Code
            $orderCode = 'ORD-' . strtoupper(uniqid());
            $shippingFee = 30000; // Fixed shipping fee
            
            // Check session promo
            $promotionId = null;
            $discountAmount = 0;
            if (isset($_SESSION['promo']) && isset($_SESSION['promo']['discount_amount'])) {
                $promotionId = $_SESSION['promo']['id'];
                $discountAmount = $_SESSION['promo']['discount_amount'];
            }
            
            $finalAmount = $totalAmount + $shippingFee - $discountAmount;
            if ($finalAmount < 0) $finalAmount = 0;

            // 3. Insert Order
            $stmtOrder = $this->db->prepare("
                INSERT INTO orders (customer_id, promotion_id, order_number, subtotal, shipping_fee, discount, total, payment_method, shipping_address, notes)
                VALUES (:cid, :promo_id, :code, :subtotal, :shipping, :discount, :total, :payment, :address, :note)
            ");
            
            $stmtOrder->execute([
                'cid' => $customerId,
                'promo_id' => $promotionId,
                'code' => $orderCode,
                'subtotal' => $totalAmount,
                'shipping' => $shippingFee,
                'discount' => $discountAmount,
                'total' => $finalAmount,
                'payment' => $paymentMethod,
                'address' => $address,
                'note' => $note
            ]);
            
            $orderId = $this->db->lastInsertId();

            // 4. Insert Order Items & Reduce Stock
            $stmtItem = $this->db->prepare("
                INSERT INTO order_items (order_id, product_id, product_variant_id, product_name, sku, color, size, unit_price, quantity, subtotal)
                VALUES (:oid, :pid, :pvid, :name, :sku, :color, :size, :price, :qty, :subtotal)
            ");
            
            $stmtUpdateStock = $this->db->prepare("
                UPDATE product_variants SET stock_quantity = stock_quantity - :qty WHERE id = :pvid
            ");

            foreach ($items as $item) {
                // Get SKU if available (since we didn't fetch sku in cart controller initially, let's grab it)
                $stmtSku = $this->db->prepare("SELECT sku FROM product_variants WHERE id = :id");
                $stmtSku->execute(['id' => $item['variant_id']]);
                $sku = $stmtSku->fetchColumn();

                $stmtItem->execute([
                    'oid' => $orderId,
                    'pid' => $item['product_id'],
                    'pvid' => $item['variant_id'],
                    'name' => $item['product_name'],
                    'sku' => $sku ?: 'N/A',
                    'color' => $item['color'],
                    'size' => $item['size'],
                    'price' => $item['price'],
                    'qty' => $item['quantity'],
                    'subtotal' => $item['subtotal']
                ]);

                // Reduce stock
                $stmtUpdateStock->execute([
                    'qty' => $item['quantity'],
                    'pvid' => $item['variant_id']
                ]);
            }

            $this->db->commit();

            // Clear cart and promo
            $_SESSION['cart'] = [];
            unset($_SESSION['promo']);

            $this->jsonResponse(true, 'Đặt hàng thành công.', ['order_code' => $orderCode]);

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('[Checkout Error] ' . $e->getMessage());
            $this->jsonResponse(false, 'Lỗi: ' . $e->getMessage());
        }
    }

    private function jsonResponse($success, $message, $data = []) {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
        exit;
    }
}
