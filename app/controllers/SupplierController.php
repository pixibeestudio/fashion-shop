<?php
require_once __DIR__ . '/../models/Supplier.php';
require_once __DIR__ . '/../helpers/Validator.php';

class SupplierController {
    private $supplierModel;

    public function __construct() {
        $this->supplierModel = new Supplier();
    }

    public function index() {
        return $this->supplierModel->getAll();
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = Validator::sanitize($_POST['name'] ?? '');
            $phone = Validator::sanitize($_POST['phone'] ?? '');
            $address = Validator::sanitize($_POST['address'] ?? '');

            if (empty($name)) {
                $this->jsonResponse(false, 'Tên nhà cung cấp không được để trống.');
                return;
            }

            $data = [
                'name' => $name,
                'phone' => $phone,
                'address' => $address
            ];

            $newId = $this->supplierModel->create($data);
            if ($newId) {
                // Return success with new ID so the frontend can select it
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Thêm nhà cung cấp thành công!', 'id' => $newId, 'name' => $name]);
                exit;
            } else {
                $this->jsonResponse(false, 'Lỗi hệ thống! Không thể thêm nhà cung cấp.');
            }
        }
    }

    private function jsonResponse($success, $message) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }
}
