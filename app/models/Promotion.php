<?php
require_once __DIR__ . '/../config/Database.php';

class Promotion {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function paginate($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $countStmt = $this->db->query("SELECT COUNT(*) FROM promotions");
        $total = $countStmt->fetchColumn();

        $sql = "SELECT * FROM promotions ORDER BY id DESC LIMIT :limit OFFSET :offset";
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

    public function checkCodeExists($code, $excludeId = null) {
        if (empty($code)) return false;
        $sql = "SELECT id FROM promotions WHERE code = :code";
        $params = ['code' => $code];
        if ($excludeId) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO promotions (code, discount_type, discount_value, start_date, end_date, status) 
            VALUES (:code, :discount_type, :discount_value, :start_date, :end_date, 'active')
        ");
        return $stmt->execute([
            'code' => $data['code'],
            'discount_type' => $data['discount_type'],
            'discount_value' => $data['discount_value'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date']
        ]);
    }

    public function update($id, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE promotions 
                SET code = :code, discount_type = :discount_type, discount_value = :discount_value, start_date = :start_date, end_date = :end_date
                WHERE id = :id
            ");
            return $stmt->execute([
                'code' => $data['code'],
                'discount_type' => $data['discount_type'],
                'discount_value' => $data['discount_value'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'id' => $id
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function toggleStatus($id) {
        try {
            $stmt = $this->db->prepare("SELECT status FROM promotions WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $currentStatus = $stmt->fetchColumn();

            if (!$currentStatus) return false;

            // Chuyển đổi giữa active và disabled (nếu đang expired thì vẫn cho phép disabled hoặc quay lại, tuy nhiên tốt nhất là toggle giữa active/disabled, nếu quá hạn thì view tự render)
            $newStatus = ($currentStatus === 'disabled') ? 'active' : 'disabled';
            $update = $this->db->prepare("UPDATE promotions SET status = :status WHERE id = :id");
            return $update->execute(['status' => $newStatus, 'id' => $id]);
        } catch (Exception $e) {
            return false;
        }
    }
}
