<?php
require_once __DIR__ . '/../config/Database.php';

class Category {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("
            SELECT c.*, p.name as parent_name, 
                   (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
            FROM categories c 
            LEFT JOIN categories p ON c.parent_id = p.id 
            ORDER BY c.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function paginate($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $countStmt = $this->db->query("SELECT COUNT(*) FROM categories");
        $total = $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT c.*, p.name as parent_name, 
                   (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
            FROM categories c 
            LEFT JOIN categories p ON c.parent_id = p.id 
            ORDER BY c.id DESC
            LIMIT :limit OFFSET :offset
        ");
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

    public function getParentCategories() {
        $stmt = $this->db->query("SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function create($data) {
        $sql = "INSERT INTO categories (name, slug, description, parent_id) VALUES (:name, :slug, :description, :parent_id)";
        $stmt = $this->db->prepare($sql);
        
        $parentId = !empty($data['parent_id']) ? $data['parent_id'] : null;

        return $stmt->execute([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'parent_id' => $parentId
        ]);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function update($data) {
        $sql = "UPDATE categories SET name = :name, slug = :slug, description = :description, parent_id = :parent_id WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $parentId = !empty($data['parent_id']) ? $data['parent_id'] : null;

        return $stmt->execute([
            'id' => $data['id'],
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'parent_id' => $parentId
        ]);
    }

    public function hasChildrenOrProducts($id) {
        // Check for child categories
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM categories WHERE parent_id = :id");
        $stmt->execute(['id' => $id]);
        $childCount = $stmt->fetchColumn();

        // Check for products
        $stmt2 = $this->db->prepare("SELECT COUNT(*) FROM products WHERE category_id = :id");
        $stmt2->execute(['id' => $id]);
        $productCount = $stmt2->fetchColumn();

        return ($childCount > 0 || $productCount > 0);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    public function slugExists($slug, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT id FROM categories WHERE slug = :slug AND id != :id");
            $stmt->execute(['slug' => $slug, 'id' => $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM categories WHERE slug = :slug");
            $stmt->execute(['slug' => $slug]);
        }
        return $stmt->fetchColumn() !== false;
    }
}
