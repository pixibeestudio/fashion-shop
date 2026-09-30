<?php
require_once __DIR__ . '/../config/Database.php';

class Product {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $sql = "SELECT p.id, p.name, p.status, c.name as category_name, 
                       (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                       (SELECT COUNT(*) FROM product_variants WHERE product_id = p.id) as variant_count,
                       (SELECT SUM(stock_quantity) FROM product_variants WHERE product_id = p.id) as total_stock,
                       (SELECT GROUP_CONCAT(sku SEPARATOR ', ') FROM product_variants WHERE product_id = p.id) as skus
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                ORDER BY p.id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function paginate($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $countStmt = $this->db->query("SELECT COUNT(*) FROM products");
        $total = $countStmt->fetchColumn();

        $sql = "SELECT p.id, p.name, p.status, c.name as category_name, 
                       (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                       (SELECT COUNT(*) FROM product_variants WHERE product_id = p.id) as variant_count,
                       (SELECT SUM(stock_quantity) FROM product_variants WHERE product_id = p.id) as total_stock,
                       (SELECT GROUP_CONCAT(sku SEPARATOR ', ') FROM product_variants WHERE product_id = p.id) as skus
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                ORDER BY p.id DESC
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

    public function slugExists($slug) {
        $stmt = $this->db->prepare("SELECT id FROM products WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetchColumn() !== false;
    }

    public function skuExists($sku) {
        $stmt = $this->db->prepare("SELECT id FROM product_variants WHERE sku = :sku");
        $stmt->execute(['sku' => $sku]);
        return $stmt->fetchColumn() !== false;
    }

    public function create($data, $variants, $images) {
        try {
            $this->db->beginTransaction();

            // 1. Insert Product
            $sql = "INSERT INTO products (category_id, name, slug, description, status) 
                    VALUES (:category_id, :name, :slug, :description, :status)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'category_id' => $data['category_id'] ?: null,
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'status' => $data['status']
            ]);
            $productId = $this->db->lastInsertId();

            // 2. Insert Variants
            if (!empty($variants['sku'])) {
                $sqlVariant = "INSERT INTO product_variants (product_id, sku, color, size, price, stock_quantity) 
                               VALUES (:product_id, :sku, :color, :size, :price, :stock_quantity)";
                $stmtVariant = $this->db->prepare($sqlVariant);

                for ($i = 0; $i < count($variants['sku']); $i++) {
                    $stmtVariant->execute([
                        'product_id' => $productId,
                        'sku' => $variants['sku'][$i],
                        'color' => $variants['color'][$i],
                        'size' => $variants['size'][$i],
                        'price' => $variants['price'][$i],
                        'stock_quantity' => $variants['stock_quantity'][$i] ?: 0
                    ]);
                }
            }

            // 3. Insert Images
            if (!empty($images)) {
                $sqlImage = "INSERT INTO product_images (product_id, image_url, is_primary, sort_order) 
                             VALUES (:product_id, :image_url, :is_primary, :sort_order)";
                $stmtImage = $this->db->prepare($sqlImage);

                foreach ($images as $index => $imageUrl) {
                    $stmtImage->execute([
                        'product_id' => $productId,
                        'image_url' => $imageUrl,
                        'is_primary' => ($index === 0) ? 1 : 0, // First image is primary
                        'sort_order' => $index
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            // In a real production app we'd log the error message
            // error_log($e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            // 1. Lấy danh sách ảnh trước khi xóa để xóa file vật lý sau
            $stmtImg = $this->db->prepare("SELECT image_url FROM product_images WHERE product_id = :id");
            $stmtImg->execute(['id' => $id]);
            $images = $stmtImg->fetchAll(PDO::FETCH_COLUMN);

            $this->db->beginTransaction();

            // 2. Xóa thủ công các bản ghi con (đề phòng DB không có ON DELETE CASCADE)
            $this->db->prepare("DELETE FROM product_images WHERE product_id = :id")->execute(['id' => $id]);
            $this->db->prepare("DELETE FROM product_variants WHERE product_id = :id")->execute(['id' => $id]);

            // 3. Xóa sản phẩm chính
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
            $result = $stmt->execute(['id' => $id]);

            $this->db->commit();

            // 4. Xóa file ảnh vật lý trên ổ cứng sau khi DB xóa thành công
            if ($result && !empty($images)) {
                foreach ($images as $imgUrl) {
                    // imgUrl dạng: /fashion-shop/public/uploads/products/prod_xxx.jpg
                    $relativePath = str_replace('/fashion-shop/', '', $imgUrl);
                    $absolutePath = __DIR__ . '/../../' . $relativePath;
                    if (file_exists($absolutePath)) {
                        unlink($absolutePath);
                    }
                }
            }

            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('[ProductModel::delete] ' . $e->getMessage());
            return false;
        }
    }


    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();
        if (!$product) return null;

        $stmtVars = $this->db->prepare("SELECT * FROM product_variants WHERE product_id = :id");
        $stmtVars->execute(['id' => $id]);
        $product['variants'] = $stmtVars->fetchAll();

        $stmtImgs = $this->db->prepare("SELECT * FROM product_images WHERE product_id = :id ORDER BY id ASC");
        $stmtImgs->execute(['id' => $id]);
        $product['images'] = $stmtImgs->fetchAll();

        return $product;
    }

    public function update($id, $data, $variants, $newImages, $keepImageIds) {
        try {
            $this->db->beginTransaction();

            // 1. Update Product
            $sql = "UPDATE products SET category_id = :category_id, name = :name, slug = :slug, description = :description, status = :status WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'category_id' => $data['category_id'] ?: null,
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'status' => $data['status'],
                'id' => $id
            ]);

            // 2. Manage Variants
            $stmt = $this->db->prepare("SELECT id FROM product_variants WHERE product_id = :id");
            $stmt->execute(['id' => $id]);
            $existingVariantIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $submittedVariantIds = [];
            if (!empty($variants['sku'])) {
                for ($i = 0; $i < count($variants['sku']); $i++) {
                    $vid = $variants['variant_id'][$i] ?? null;
                    if ($vid && in_array($vid, $existingVariantIds)) {
                        // Update
                        $sqlV = "UPDATE product_variants SET sku = :sku, color = :color, size = :size, price = :price, stock_quantity = :stock_quantity WHERE id = :vid";
                        $stmtV = $this->db->prepare($sqlV);
                        $stmtV->execute([
                            'sku' => $variants['sku'][$i],
                            'color' => $variants['color'][$i],
                            'size' => $variants['size'][$i],
                            'price' => $variants['price'][$i],
                            'stock_quantity' => $variants['stock_quantity'][$i] ?: 0,
                            'vid' => $vid
                        ]);
                        $submittedVariantIds[] = $vid;
                    } else {
                        // Insert
                        $sqlV = "INSERT INTO product_variants (product_id, sku, color, size, price, stock_quantity) VALUES (:pid, :sku, :color, :size, :price, :sq)";
                        $stmtV = $this->db->prepare($sqlV);
                        $stmtV->execute([
                            'pid' => $id,
                            'sku' => $variants['sku'][$i],
                            'color' => $variants['color'][$i],
                            'size' => $variants['size'][$i],
                            'price' => $variants['price'][$i],
                            'sq' => $variants['stock_quantity'][$i] ?: 0
                        ]);
                    }
                }
            }

            // Delete removed variants
            $variantsToDelete = array_diff($existingVariantIds, $submittedVariantIds);
            if (!empty($variantsToDelete)) {
                $placeholders = implode(',', array_fill(0, count($variantsToDelete), '?'));
                $stmtDel = $this->db->prepare("DELETE FROM product_variants WHERE id IN ($placeholders)");
                $stmtDel->execute(array_values($variantsToDelete));
            }

            // 3. Manage Images
            // Lấy URL của các ảnh bị xóa để xóa file vật lý
            if (empty($keepImageIds)) {
                $stmtImg = $this->db->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
                $stmtImg->execute([$id]);
                $imagesToDelete = $stmtImg->fetchAll(PDO::FETCH_COLUMN);
                
                $this->db->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$id]);
            } else {
                $placeholders = implode(',', array_fill(0, count($keepImageIds), '?'));
                $params = $keepImageIds;
                $params[] = $id;
                
                $stmtImg = $this->db->prepare("SELECT image_url FROM product_images WHERE id NOT IN ($placeholders) AND product_id = ?");
                $stmtImg->execute($params);
                $imagesToDelete = $stmtImg->fetchAll(PDO::FETCH_COLUMN);
                
                $this->db->prepare("DELETE FROM product_images WHERE id NOT IN ($placeholders) AND product_id = ?")->execute($params);
            }

            // Xóa file vật lý của các ảnh bị loại bỏ
            if (!empty($imagesToDelete)) {
                foreach ($imagesToDelete as $imgUrl) {
                    $relativePath = str_replace('/fashion-shop/', '', $imgUrl);
                    $absolutePath = __DIR__ . '/../../' . $relativePath;
                    if (file_exists($absolutePath)) {
                        unlink($absolutePath);
                    }
                }
            }

            // Insert new images
            if (!empty($newImages)) {
                $sqlImage = "INSERT INTO product_images (product_id, image_url, is_primary) VALUES (:pid, :url, 0)";
                $stmtImage = $this->db->prepare($sqlImage);
                foreach ($newImages as $url) {
                    $stmtImage->execute(['pid' => $id, 'url' => $url]);
                }
            }

            // Fix primary image (set first one as primary)
            $this->db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?")->execute([$id]);
            $this->db->prepare("UPDATE product_images SET is_primary = 1 WHERE product_id = ? ORDER BY id ASC LIMIT 1")->execute([$id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
