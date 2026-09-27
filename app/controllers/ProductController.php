<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../helpers/Validator.php';

class ProductController {
    private $productModel;

    public function __construct() {
        $this->productModel = new Product();
    }

    // Display the list of products
    public function index() {
        return $this->productModel->getAll();
    }

    public function paginate($page = 1) {
        return $this->productModel->paginate($page, 10);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = Validator::sanitize($_POST['name'] ?? '');
            $slug = Validator::sanitize($_POST['slug'] ?? '');
            $description = Validator::sanitize($_POST['description'] ?? '');
            $status = Validator::sanitize($_POST['status'] ?? 'draft');
            $categoryId = $_POST['category_id'] ?? null;
            $variants = $_POST['variants'] ?? [];

            // 1. Validate Basic Info
            if (empty($name) || empty($slug)) {
                $this->jsonResponse(false, 'Tên sản phẩm và Đường dẫn không được để trống.');
                return;
            }

            if ($this->productModel->slugExists($slug)) {
                $this->jsonResponse(false, 'Đường dẫn (Slug) này đã tồn tại. Vui lòng chọn tên khác.');
                return;
            }

            // 2. Validate Variants
            if (empty($variants['sku']) || count($variants['sku']) === 0) {
                $this->jsonResponse(false, 'Sản phẩm phải có ít nhất 1 biến thể.');
                return;
            }

            // Check SKU uniqueness
            $skus = $variants['sku'];
            if (count($skus) !== count(array_unique($skus))) {
                $this->jsonResponse(false, 'Các mã SKU trong cùng một sản phẩm không được trùng nhau.');
                return;
            }
            foreach ($skus as $sku) {
                if ($this->productModel->skuExists($sku)) {
                    $this->jsonResponse(false, "Mã SKU '{$sku}' đã tồn tại trong hệ thống.");
                    return;
                }
            }

            // 3. Handle Image Uploads
            $uploadedImages = [];
            if (!empty($_FILES['images']['name'][0])) {
                $uploadDir = __DIR__ . '/../../public/uploads/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $totalFiles = count($_FILES['images']['name']);
                if ($totalFiles > 5) {
                    $this->jsonResponse(false, 'Chỉ được phép tải lên tối đa 5 ảnh.');
                    return;
                }

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                for ($i = 0; $i < $totalFiles; $i++) {
                    $tmpName = $_FILES['images']['tmp_name'][$i];
                    $fileName = $_FILES['images']['name'][$i];
                    $fileType = $_FILES['images']['type'][$i];
                    
                    if (in_array($fileType, $allowedTypes)) {
                        // Generate unique filename
                        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                        $newName = uniqid('prod_') . '.' . $ext;
                        $destination = $uploadDir . $newName;
                        
                        if (move_uploaded_file($tmpName, $destination)) {
                            // Store relative path for DB
                            $uploadedImages[] = '/fashion-shop/public/uploads/products/' . $newName;
                        }
                    }
                }
            }

            // 4. Save to Database
            $productData = [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'category_id' => $categoryId,
                'status' => $status
            ];

            if ($this->productModel->create($productData, $variants, $uploadedImages)) {
                $this->jsonResponse(true, 'Thêm sản phẩm thành công!');
            } else {
                $this->jsonResponse(false, 'Lỗi hệ thống! Không thể lưu sản phẩm vào cơ sở dữ liệu.');
            }
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            if (!$id) {
                $this->jsonResponse(false, 'Không tìm thấy ID sản phẩm.');
                return;
            }

            $name = Validator::sanitize($_POST['name'] ?? '');
            $slug = Validator::sanitize($_POST['slug'] ?? '');
            $description = Validator::sanitize($_POST['description'] ?? '');
            $status = Validator::sanitize($_POST['status'] ?? 'draft');
            $categoryId = $_POST['category_id'] ?? null;
            $variants = $_POST['variants'] ?? [];
            $keepImageIds = $_POST['keep_images'] ?? [];

            // 1. Validate Basic Info
            if (empty($name) || empty($slug)) {
                $this->jsonResponse(false, 'Tên sản phẩm và Đường dẫn không được để trống.');
                return;
            }

            // Check slug uniqueness (excluding current product)
            $existingProduct = $this->productModel->getById($id);
            if ($existingProduct['slug'] !== $slug && $this->productModel->slugExists($slug)) {
                $this->jsonResponse(false, 'Đường dẫn (Slug) này đã tồn tại. Vui lòng chọn tên khác.');
                return;
            }

            // 2. Validate Variants
            if (empty($variants['sku']) || count($variants['sku']) === 0) {
                $this->jsonResponse(false, 'Sản phẩm phải có ít nhất 1 biến thể.');
                return;
            }

            // Check SKU uniqueness
            $skus = $variants['sku'];
            if (count($skus) !== count(array_unique($skus))) {
                $this->jsonResponse(false, 'Các mã SKU trong cùng một sản phẩm không được trùng nhau.');
                return;
            }
            
            // Need to check if SKU belongs to another product
            // To simplify, we check if SKU exists, and if it does, it MUST belong to the current product variants
            $existingVariantSkus = array_column($existingProduct['variants'], 'sku');
            foreach ($skus as $sku) {
                if (!in_array($sku, $existingVariantSkus) && $this->productModel->skuExists($sku)) {
                    $this->jsonResponse(false, "Mã SKU '{$sku}' đã tồn tại ở một sản phẩm khác.");
                    return;
                }
            }

            // 3. Handle Image Uploads
            $uploadedImages = [];
            if (!empty($_FILES['images']['name'][0])) {
                $uploadDir = __DIR__ . '/../../public/uploads/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $totalNewFiles = count($_FILES['images']['name']);
                $totalKept = count($keepImageIds);
                
                if (($totalNewFiles + $totalKept) > 5) {
                    $this->jsonResponse(false, 'Sản phẩm chỉ được phép có tối đa 5 ảnh.');
                    return;
                }

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                for ($i = 0; $i < $totalNewFiles; $i++) {
                    $tmpName = $_FILES['images']['tmp_name'][$i];
                    $fileName = $_FILES['images']['name'][$i];
                    $fileType = $_FILES['images']['type'][$i];
                    
                    if (in_array($fileType, $allowedTypes)) {
                        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                        $newName = uniqid('prod_') . '.' . $ext;
                        $destination = $uploadDir . $newName;
                        
                        if (move_uploaded_file($tmpName, $destination)) {
                            $uploadedImages[] = '/fashion-shop/public/uploads/products/' . $newName;
                        }
                    }
                }
            } else {
                if (count($keepImageIds) === 0) {
                    // No new images, no kept images
                    // $this->jsonResponse(false, 'Sản phẩm phải có ít nhất 1 ảnh.');
                    // return;
                }
            }

            // 4. Save to Database
            $productData = [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'category_id' => $categoryId,
                'status' => $status
            ];

            if ($this->productModel->update($id, $productData, $variants, $uploadedImages, $keepImageIds)) {
                $this->jsonResponse(true, 'Cập nhật sản phẩm thành công!');
            } else {
                $this->jsonResponse(false, 'Lỗi hệ thống! Không thể cập nhật sản phẩm.');
            }
        }
    }

    private function jsonResponse($success, $message) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get ID from raw JSON or POST
            $data = json_decode(file_get_contents("php://input"), true);
            $id = $data['id'] ?? null;
            
            if (!$id) {
                $this->jsonResponse(false, 'Không tìm thấy ID sản phẩm.');
                return;
            }

            if ($this->productModel->delete($id)) {
                $this->jsonResponse(true, 'Xóa sản phẩm thành công!');
            } else {
                $this->jsonResponse(false, 'Lỗi! Không thể xóa sản phẩm này.');
            }
        }
    }
}
