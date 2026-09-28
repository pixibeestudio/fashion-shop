<?php
require_once __DIR__ . '/../config/Database.php';

class Order {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function paginate($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $countStmt = $this->db->query("SELECT COUNT(*) FROM orders");
        $total = $countStmt->fetchColumn();

        $sql = "SELECT o.*, c.full_name, c.phone 
                FROM orders o
                LEFT JOIN customers c ON o.customer_id = c.id
                ORDER BY o.id DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ];
    }

    public function getById($id) {
        // Fetch Order header
        $sql = "SELECT o.*, c.full_name, c.email, c.phone 
                FROM orders o
                LEFT JOIN customers c ON o.customer_id = c.id
                WHERE o.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch();

        if (!$order) return null;

        // Fetch Items
        $sqlItems = "SELECT * FROM order_items WHERE order_id = :id";
        $stmtItems = $this->db->prepare($sqlItems);
        $stmtItems->execute(['id' => $id]);
        $order['items'] = $stmtItems->fetchAll();

        return $order;
    }

    public function updateStatus($id, $paymentStatus, $shippingStatus, $notes) {
        try {
            $this->db->beginTransaction();
            
            // Lấy trạng thái hiện tại
            $stmtGet = $this->db->prepare("SELECT shipping_status FROM orders WHERE id = :id");
            $stmtGet->execute(['id' => $id]);
            $currentStatus = $stmtGet->fetchColumn();

            // Nếu đơn hàng bị hủy từ trạng thái chưa hủy, ta cộng lại Tồn Kho
            if ($shippingStatus === 'cancelled' && $currentStatus !== 'cancelled') {
                $stmtItems = $this->db->prepare("SELECT product_variant_id, quantity FROM order_items WHERE order_id = :id");
                $stmtItems->execute(['id' => $id]);
                $items = $stmtItems->fetchAll();

                $stmtStock = $this->db->prepare("UPDATE product_variants SET stock_quantity = stock_quantity + :qty WHERE id = :vid");
                foreach ($items as $item) {
                    if ($item['product_variant_id']) {
                        $stmtStock->execute([
                            'qty' => $item['quantity'],
                            'vid' => $item['product_variant_id']
                        ]);
                    }
                }
            } 
            // Nếu đơn hàng từ Hủy chuyển sang trạng thái khác (Khôi phục), ta phải trừ lại Tồn Kho
            elseif ($currentStatus === 'cancelled' && $shippingStatus !== 'cancelled') {
                $stmtItems = $this->db->prepare("SELECT product_variant_id, quantity FROM order_items WHERE order_id = :id");
                $stmtItems->execute(['id' => $id]);
                $items = $stmtItems->fetchAll();

                $stmtStock = $this->db->prepare("UPDATE product_variants SET stock_quantity = stock_quantity - :qty WHERE id = :vid");
                foreach ($items as $item) {
                    if ($item['product_variant_id']) {
                        $stmtStock->execute([
                            'qty' => $item['quantity'],
                            'vid' => $item['product_variant_id']
                        ]);
                    }
                }
            }

            $sql = "UPDATE orders SET payment_status = :ps, shipping_status = :ss, notes = :notes WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'ps' => $paymentStatus,
                'ss' => $shippingStatus,
                'notes' => $notes,
                'id' => $id
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
