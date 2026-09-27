<?php
require_once __DIR__ . '/../config/Database.php';

class Supplier {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM suppliers ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function create($data) {
        $sql = "INSERT INTO suppliers (name, phone, address) VALUES (:name, :phone, :address)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }
}
