<?php
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../helpers/Validator.php';

class CategoryController {
    private $categoryModel;

    public function __construct() {
        $this->categoryModel = new Category();
    }

    public function index() {
        return $this->categoryModel->getAll();
    }

    public function getParents() {
        return $this->categoryModel->getParentCategories();
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = Validator::sanitize($_POST['name'] ?? '');
            $slug = Validator::sanitize($_POST['slug'] ?? '');
            $description = Validator::sanitize($_POST['description'] ?? '');
            $parentId = $_POST['parent_id'] ?? '';

            if (empty($name) || empty($slug)) {
                $this->jsonResponse(false, 'Tên danh mục và Đường dẫn không được để trống.');
                return;
            }

            if ($this->categoryModel->slugExists($slug)) {
                $this->jsonResponse(false, 'Đường dẫn (Slug) này đã tồn tại. Vui lòng chọn tên khác.');
                return;
            }

            $data = [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'parent_id' => $parentId
            ];

            if ($this->categoryModel->create($data)) {
                $this->jsonResponse(true, 'Thêm danh mục thành công!');
            } else {
                $this->jsonResponse(false, 'Có lỗi xảy ra khi lưu vào cơ sở dữ liệu.');
            }
        }
    }

    public function getCategory($id) {
        $category = $this->categoryModel->getById($id);
        if ($category) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'category' => $category]);
        } else {
            $this->jsonResponse(false, 'Không tìm thấy danh mục.');
        }
        exit;
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            $name = Validator::sanitize($_POST['name'] ?? '');
            $slug = Validator::sanitize($_POST['slug'] ?? '');
            $description = Validator::sanitize($_POST['description'] ?? '');
            $parentId = $_POST['parent_id'] ?? '';

            if (empty($id) || empty($name) || empty($slug)) {
                $this->jsonResponse(false, 'Dữ liệu không hợp lệ.');
                return;
            }
            
            // Check if parent is set to itself
            if ($parentId == $id) {
                $this->jsonResponse(false, 'Danh mục cha không thể là chính nó.');
                return;
            }

            if ($this->categoryModel->slugExists($slug, $id)) {
                $this->jsonResponse(false, 'Đường dẫn (Slug) này đã tồn tại. Vui lòng chọn tên khác.');
                return;
            }

            $data = [
                'id' => $id,
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'parent_id' => $parentId
            ];

            if ($this->categoryModel->update($data)) {
                $this->jsonResponse(true, 'Cập nhật danh mục thành công!');
            } else {
                $this->jsonResponse(false, 'Có lỗi xảy ra khi lưu vào cơ sở dữ liệu.');
            }
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            if (empty($id)) {
                $this->jsonResponse(false, 'Không xác định được danh mục cần xóa.');
                return;
            }

            // Enforce Business Rule: Cannot delete if has children or products
            if ($this->categoryModel->hasChildrenOrProducts($id)) {
                $this->jsonResponse(false, 'Không thể xóa danh mục này vì đang chứa danh mục con hoặc sản phẩm bên trong.');
                return;
            }

            if ($this->categoryModel->delete($id)) {
                $this->jsonResponse(true, 'Xóa danh mục thành công!');
            } else {
                $this->jsonResponse(false, 'Có lỗi xảy ra trong quá trình xóa.');
            }
        }
    }

    private function jsonResponse($success, $message) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }
}
