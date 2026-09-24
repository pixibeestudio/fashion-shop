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
}
