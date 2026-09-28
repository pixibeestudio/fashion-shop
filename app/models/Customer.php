<?php
require_once __DIR__ . '/../config/Database.php';

class Customer {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByPhoneOrEmail($username) {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE phone = :phone OR email = :email LIMIT 1");
        $stmt->execute([
            'phone' => $username,
            'email' => $username
        ]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO customers (full_name, phone, email, password_hash, status) 
            VALUES (:full_name, :phone, :email, :password_hash, 'active')
        ");
        return $stmt->execute([
            'full_name' => $data['full_name'],
            'phone' => $data['phone'],
            'email' => empty($data['email']) ? null : $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT)
        ]);
    }

    public function checkPhoneExists($phone) {
        $stmt = $this->db->prepare("SELECT id FROM customers WHERE phone = :phone LIMIT 1");
        $stmt->execute(['phone' => $phone]);
        return $stmt->fetch() !== false;
    }
    
    public function checkEmailExists($email) {
        if (empty($email)) return false;
        $stmt = $this->db->prepare("SELECT id FROM customers WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() !== false;
    }

    public function paginate($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $countStmt = $this->db->query("SELECT COUNT(*) FROM customers");
        $total = $countStmt->fetchColumn();

        $sql = "SELECT id, full_name, email, phone, status, created_at 
                FROM customers 
                ORDER BY id DESC 
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
        $stmt = $this->db->prepare("SELECT id, full_name, email, phone, status, created_at FROM customers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $customer = $stmt->fetch();

        if ($customer) {
            $stmtAddr = $this->db->prepare("SELECT * FROM customer_addresses WHERE customer_id = :id ORDER BY is_default DESC, id DESC");
            $stmtAddr->execute(['id' => $id]);
            $customer['addresses'] = $stmtAddr->fetchAll();
        }
        
        return $customer;
    }

    public function toggleStatus($id) {
        try {
            $stmt = $this->db->prepare("SELECT status FROM customers WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $currentStatus = $stmt->fetchColumn();

            if (!$currentStatus) return false;

            $newStatus = ($currentStatus === 'active') ? 'banned' : 'active';
            $update = $this->db->prepare("UPDATE customers SET status = :status WHERE id = :id");
            return $update->execute(['status' => $newStatus, 'id' => $id]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function updateInfo($id, $fullName, $phone, $email) {
        try {
            $stmt = $this->db->prepare("UPDATE customers SET full_name = :fname, phone = :phone, email = :email WHERE id = :id");
            return $stmt->execute([
                'fname' => $fullName,
                'phone' => $phone,
                'email' => $email,
                'id' => $id
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function resetPassword($id, $newPassword) {
        try {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE customers SET password_hash = :hash WHERE id = :id");
            return $stmt->execute([
                'hash' => $hash,
                'id' => $id
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}
