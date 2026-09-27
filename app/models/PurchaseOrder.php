<?php
require_once __DIR__ . '/../config/Database.php';

class PurchaseOrder {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function paginate($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $countStmt = $this->db->query("SELECT COUNT(*) FROM purchase_orders");
        $total = $countStmt->fetchColumn();

        $sql = "SELECT po.*, s.name as supplier_name, u.full_name as user_name 
                FROM purchase_orders po
                LEFT JOIN suppliers s ON po.supplier_id = s.id
                LEFT JOIN users u ON po.user_id = u.id
                ORDER BY po.id DESC
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
        // Fetch PO header
        $sql = "SELECT po.*, s.name as supplier_name, s.phone as supplier_phone, s.address as supplier_address, u.full_name as user_name 
                FROM purchase_orders po
                LEFT JOIN suppliers s ON po.supplier_id = s.id
                LEFT JOIN users u ON po.user_id = u.id
                WHERE po.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $po = $stmt->fetch();

        if (!$po) return null;

        // Fetch Items with product details
        $sqlItems = "SELECT poi.*, pv.sku, pv.color, pv.size, p.name as product_name
                     FROM purchase_order_items poi
                     JOIN product_variants pv ON poi.product_variant_id = pv.id
                     JOIN products p ON pv.product_id = p.id
                     WHERE poi.purchase_order_id = :id";
        $stmtItems = $this->db->prepare($sqlItems);
        $stmtItems->execute(['id' => $id]);
        $po['items'] = $stmtItems->fetchAll();

        return $po;
    }

    public function create($supplierId, $userId, $totalAmount, $items) {
        try {
            $this->db->beginTransaction();

            // 1. Insert Purchase Order
            $sqlPo = "INSERT INTO purchase_orders (supplier_id, user_id, total_amount, status) VALUES (:sid, :uid, :total, 'completed')";
            $stmtPo = $this->db->prepare($sqlPo);
            $stmtPo->execute([
                'sid' => $supplierId,
                'uid' => $userId,
                'total' => $totalAmount
            ]);
            $poId = $this->db->lastInsertId();

            // 2. Insert Items and Update Stock
            $sqlItem = "INSERT INTO purchase_order_items (purchase_order_id, product_variant_id, quantity, unit_price) VALUES (:poid, :vid, :qty, :price)";
            $stmtItem = $this->db->prepare($sqlItem);

            $sqlStock = "UPDATE product_variants SET stock_quantity = stock_quantity + :qty WHERE id = :vid";
            $stmtStock = $this->db->prepare($sqlStock);

            foreach ($items as $item) {
                // Insert item log
                $stmtItem->execute([
                    'poid' => $poId,
                    'vid' => $item['variant_id'],
                    'qty' => $item['quantity'],
                    'price' => $item['unit_price']
                ]);

                // Auto-sync stock
                $stmtStock->execute([
                    'qty' => $item['quantity'],
                    'vid' => $item['variant_id']
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function delete($id) {
        try {
            $this->db->beginTransaction();

            // Fetch current items to revert stock
            $stmt = $this->db->prepare("SELECT product_variant_id, quantity FROM purchase_order_items WHERE purchase_order_id = :id");
            $stmt->execute(['id' => $id]);
            $items = $stmt->fetchAll();

            $stmtStock = $this->db->prepare("UPDATE product_variants SET stock_quantity = stock_quantity - :qty WHERE id = :vid");
            foreach ($items as $item) {
                $stmtStock->execute([
                    'qty' => $item['quantity'],
                    'vid' => $item['product_variant_id']
                ]);
            }

            // purchase_order_items has ON DELETE CASCADE in schema, but we can delete manually just in case
            $this->db->prepare("DELETE FROM purchase_order_items WHERE purchase_order_id = :id")->execute(['id' => $id]);
            $this->db->prepare("DELETE FROM purchase_orders WHERE id = :id")->execute(['id' => $id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function update($id, $supplierId, $totalAmount, $newItems) {
        try {
            $this->db->beginTransaction();

            // 1. Revert old stock
            $stmtOld = $this->db->prepare("SELECT product_variant_id, quantity FROM purchase_order_items WHERE purchase_order_id = :id");
            $stmtOld->execute(['id' => $id]);
            $oldItems = $stmtOld->fetchAll();

            $stmtRevert = $this->db->prepare("UPDATE product_variants SET stock_quantity = stock_quantity - :qty WHERE id = :vid");
            foreach ($oldItems as $old) {
                $stmtRevert->execute([
                    'qty' => $old['quantity'],
                    'vid' => $old['product_variant_id']
                ]);
            }

            // 2. Delete old items
            $this->db->prepare("DELETE FROM purchase_order_items WHERE purchase_order_id = :id")->execute(['id' => $id]);

            // 3. Update PO Header
            $stmtPo = $this->db->prepare("UPDATE purchase_orders SET supplier_id = :sid, total_amount = :total WHERE id = :id");
            $stmtPo->execute([
                'sid' => $supplierId,
                'total' => $totalAmount,
                'id' => $id
            ]);

            // 4. Insert new items and apply new stock
            $stmtInsert = $this->db->prepare("INSERT INTO purchase_order_items (purchase_order_id, product_variant_id, quantity, unit_price) VALUES (:poid, :vid, :qty, :price)");
            $stmtApply = $this->db->prepare("UPDATE product_variants SET stock_quantity = stock_quantity + :qty WHERE id = :vid");

            foreach ($newItems as $item) {
                $stmtInsert->execute([
                    'poid' => $id,
                    'vid' => $item['variant_id'],
                    'qty' => $item['quantity'],
                    'price' => $item['unit_price']
                ]);

                $stmtApply->execute([
                    'qty' => $item['quantity'],
                    'vid' => $item['variant_id']
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
