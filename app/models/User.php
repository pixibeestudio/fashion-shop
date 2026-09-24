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
}
