<?php
require_once __DIR__ . '/../models/Customer.php';

class CustomerController {
    private $customerModel;

    public function __construct() {
        $this->customerModel = new Customer();
    }

    public function paginate($page = 1) {
        return $this->customerModel->paginate($page, 10);
    }

    public function getCustomer($id) {
        $customer = $this->customerModel->getById($id);
        if ($customer) {
            $this->jsonResponse(true, 'Thành công', $customer);
        } else {
            $this->jsonResponse(false, 'Không tìm thấy khách hàng.');
        }
    }

    public function toggleStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $id = $data['id'] ?? '';

            if (empty($id)) {
                $this->jsonResponse(false, 'Không xác định được khách hàng.');
                return;
            }

            if ($this->customerModel->toggleStatus($id)) {
                $this->jsonResponse(true, 'Cập nhật trạng thái thành công!');
            } else {
                $this->jsonResponse(false, 'Lỗi hệ thống khi cập nhật.');
            }
        }
    }

    public function updateInfo() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $id = $data['id'] ?? '';
            $fullName = $data['full_name'] ?? '';
            $phone = $data['phone'] ?? '';
            $email = $data['email'] ?? '';

            if (empty($id) || empty($fullName)) {
                $this->jsonResponse(false, 'Họ tên không được để trống.');
                return;
            }

            if ($this->customerModel->updateInfo($id, $fullName, $phone, $email)) {
                $this->jsonResponse(true, 'Cập nhật thông tin khách hàng thành công!');
            } else {
                $this->jsonResponse(false, 'Lỗi hệ thống khi cập nhật (Email hoặc SĐT có thể đã tồn tại).');
            }
        }
    }

    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $id = $data['id'] ?? '';
            $newPassword = $data['password'] ?? '';

            if (empty($id) || empty($newPassword)) {
                $this->jsonResponse(false, 'Mật khẩu không được để trống.');
                return;
            }

            if ($this->customerModel->resetPassword($id, $newPassword)) {
                $this->jsonResponse(true, 'Đã đặt lại mật khẩu thành công!');
            } else {
                $this->jsonResponse(false, 'Lỗi hệ thống khi đặt lại mật khẩu.');
            }
        }
    }

    private function jsonResponse($success, $message, $data = null) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
        exit;
    }
}
