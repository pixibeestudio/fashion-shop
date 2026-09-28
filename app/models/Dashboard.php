<?php
require_once __DIR__ . '/../config/Database.php';

class Dashboard {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getKpis($startDate, $endDate) {
        // Revenue: Tính những đơn không bị huỷ (cancelled) và không bị hoàn tiền (refunded)
        $revSql = "SELECT SUM(total) as revenue FROM orders WHERE created_at >= :start AND created_at <= :end AND shipping_status != 'cancelled' AND payment_status != 'refunded'";
        $stmt = $this->db->prepare($revSql);
        $stmt->execute(['start' => $startDate, 'end' => $endDate]);
        $revenue = $stmt->fetchColumn() ?: 0;

        // Total orders
        $ordSql = "SELECT COUNT(*) as total_orders FROM orders WHERE created_at >= :start AND created_at <= :end AND shipping_status != 'cancelled'";
        $stmt = $this->db->prepare($ordSql);
        $stmt->execute(['start' => $startDate, 'end' => $endDate]);
        $totalOrders = $stmt->fetchColumn() ?: 0;

        // Pending orders
        $pendSql = "SELECT COUNT(*) as pending_orders FROM orders WHERE created_at >= :start AND created_at <= :end AND shipping_status IN ('pending', 'processing')";
        $stmt = $this->db->prepare($pendSql);
        $stmt->execute(['start' => $startDate, 'end' => $endDate]);
        $pendingOrders = $stmt->fetchColumn() ?: 0;

        // New customers
        $custSql = "SELECT COUNT(*) as new_customers FROM customers WHERE created_at >= :start AND created_at <= :end";
        $stmt = $this->db->prepare($custSql);
        $stmt->execute(['start' => $startDate, 'end' => $endDate]);
        $newCustomers = $stmt->fetchColumn() ?: 0;

        return [
            'revenue' => (float)$revenue,
            'total_orders' => (int)$totalOrders,
            'pending_orders' => (int)$pendingOrders,
            'new_customers' => (int)$newCustomers
        ];
    }

    public function getChartData($startDate, $endDate, $filterType) {
        // filterType = 'year' => group by Month
        // filterType = 'month' or 'week' or 'today' => group by Date
        
        if ($filterType === 'year' || $filterType === 'all') {
            $format = '%Y-%m'; // Nhóm theo tháng
        } else {
            $format = '%Y-%m-%d'; // Nhóm theo ngày
        }

        $sql = "
            SELECT DATE_FORMAT(created_at, :format) as label, SUM(total) as revenue 
            FROM orders 
            WHERE created_at >= :start AND created_at <= :end AND shipping_status != 'cancelled' AND payment_status != 'refunded'
            GROUP BY label 
            ORDER BY label ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['format' => $format, 'start' => $startDate, 'end' => $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopProducts($startDate, $endDate, $limit = 5) {
        $sql = "
            SELECT oi.product_name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as total_revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.created_at >= :start AND o.created_at <= :end AND o.shipping_status != 'cancelled'
            GROUP BY oi.product_name
            ORDER BY total_sold DESC
            LIMIT :limit
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':start', $startDate);
        $stmt->bindValue(':end', $endDate);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentOrders($limit = 5) {
        $sql = "
            SELECT o.id, o.order_number, c.full_name as customer_name, o.total, o.shipping_status, o.created_at
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            ORDER BY o.created_at DESC
            LIMIT :limit
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
