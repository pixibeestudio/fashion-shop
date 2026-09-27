<?php
require_once __DIR__ . '/../models/PurchaseOrder.php';
require_once __DIR__ . '/../models/Supplier.php';
require_once __DIR__ . '/../models/Product.php';

class PurchaseOrderController {
    private $poModel;

    public function __construct() {
        $this->poModel = new PurchaseOrder();
    }

    public function paginate($page = 1) {
        return $this->poModel->paginate($page, 10);
    }

    public function getPurchaseOrder($id) {
        $po = $this->poModel->getById($id);
        if ($po) {
            header('Content-Type: application/json');
            // Using result.data to be consistent with conventions
            echo json_encode(['success' => true, 'data' => $po]);
        } else {
            $this->jsonResponse(false, 'Không tìm thấy phiếu nhập.');
        }
        exit;
    }

    // Helper for rendering create view
    public function getCreateFormData() {
        $supplierModel = new Supplier();
        $suppliers = $supplierModel->getAll();

        // Lấy tất cả biến thể sản phẩm để load vào dropdown/table search
        $productModel = new Product();
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT pv.id, pv.sku, pv.color, pv.size, p.name 
                            FROM product_variants pv 
                            JOIN products p ON pv.product_id = p.id 
                            ORDER BY p.name ASC");
        $variants = $stmt->fetchAll();

        return [
            'suppliers' => $suppliers,
            'variants' => $variants
        ];
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Because we send JSON from Frontend via fetch
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            $supplierId = $data['supplier_id'] ?? '';
            $items = $data['items'] ?? [];
            $totalAmount = $data['total_amount'] ?? 0;
            $userId = 1; // Temporary fix as agreed

            if (empty($supplierId) || empty($items)) {
                $this->jsonResponse(false, 'Vui lòng chọn nhà cung cấp và ít nhất một sản phẩm.');
                return;
            }

            if ($this->poModel->create($supplierId, $userId, $totalAmount, $items)) {
                $this->jsonResponse(true, 'Tạo phiếu nhập kho và cập nhật Tồn kho thành công!');
            } else {
                $this->jsonResponse(false, 'Lỗi hệ thống. Vui lòng thử lại sau.');
            }
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            $id = $data['id'] ?? '';
            $supplierId = $data['supplier_id'] ?? '';
            $items = $data['items'] ?? [];
            $totalAmount = $data['total_amount'] ?? 0;

            if (empty($id) || empty($supplierId) || empty($items)) {
                $this->jsonResponse(false, 'Dữ liệu không hợp lệ.');
                return;
            }

            if ($this->poModel->update($id, $supplierId, $totalAmount, $items)) {
                $this->jsonResponse(true, 'Cập nhật phiếu nhập kho thành công!');
            } else {
                $this->jsonResponse(false, 'Lỗi hệ thống khi cập nhật.');
            }
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $id = $data['id'] ?? '';

            if (empty($id)) {
                $this->jsonResponse(false, 'Không xác định được mã phiếu.');
                return;
            }

            if ($this->poModel->delete($id)) {
                $this->jsonResponse(true, 'Xóa phiếu nhập kho và hoàn trả tồn kho thành công!');
            } else {
                $this->jsonResponse(false, 'Có lỗi xảy ra khi xóa phiếu.');
            }
        }
    }

    private function jsonResponse($success, $message) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }
}
