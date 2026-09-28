<?php
require_once __DIR__ . '/../config/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByPhoneOrEmail($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE phone = :phone OR email = :email LIMIT 1");
        $stmt->execute([
            'phone' => $username,
            'email' => $username
        ]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function paginate($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $countStmt = $this->db->query("SELECT COUNT(*) FROM users");
        $total = $countStmt->fetchColumn();

        $sql = "SELECT u.id, u.full_name, u.email, u.phone, u.status, u.created_at, u.role_id, r.name as role_name 
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                ORDER BY u.id DESC 
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

    public function checkEmailExists($email, $excludeId = null) {
        if (empty($email)) return false;
        $sql = "SELECT id FROM users WHERE email = :email";
        $params = ['email' => $email];
        if ($excludeId) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    public function checkPhoneExists($phone, $excludeId = null) {
        if (empty($phone)) return false;
        $sql = "SELECT id FROM users WHERE phone = :phone";
        $params = ['phone' => $phone];
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
            INSERT INTO users (role_id, full_name, email, phone, password_hash, status) 
            VALUES (:role_id, :full_name, :email, :phone, :password_hash, 'active')
        ");
        return $stmt->execute([
            'role_id' => $data['role_id'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT)
        ]);
    }

    public function updateInfo($id, $role_id, $fullName, $phone, $email) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET role_id = :role_id, full_name = :fname, phone = :phone, email = :email WHERE id = :id");
            return $stmt->execute([
                'role_id' => $role_id,
                'fname' => $fullName,
                'phone' => $phone,
                'email' => $email,
                'id' => $id
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function toggleStatus($id) {
        try {
            // Ngăn chặn admin tự khóa tài khoản của chính mình (sẽ xử lý ở Controller nhưng backup ở đây)
            $stmt = $this->db->prepare("SELECT status FROM users WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $currentStatus = $stmt->fetchColumn();

            if (!$currentStatus) return false;

            $newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';
            $update = $this->db->prepare("UPDATE users SET status = :status WHERE id = :id");
            return $update->execute(['status' => $newStatus, 'id' => $id]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function resetPassword($id, $newPassword) {
        try {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
            return $stmt->execute([
                'hash' => $hash,
                'id' => $id
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete($id) {
        try {
            // Kiểm tra xem nhân viên này đã được tham chiếu ở bảng purchase_orders chưa
            $stmt = $this->db->prepare("SELECT id FROM purchase_orders WHERE user_id = :id LIMIT 1");
            $stmt->execute(['id' => $id]);
            if ($stmt->fetch()) {
                return 'constraint'; // Bị dính khóa ngoại
            }

            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            // Kiểm tra mã lỗi SQLSTATE nếu bị dính khóa ngoại khác
            if ($e->getCode() == 23000) {
                return 'constraint';
            }
            return false;
        }
    }
}
