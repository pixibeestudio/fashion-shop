<?php
require_once __DIR__ . '/../models/Order.php';

class OrderController {
    private $orderModel;

    public function __construct() {
        $this->orderModel = new Order();
    }

    public function paginate($page = 1) {
        return $this->orderModel->paginate($page, 10);
    }

    public function getOrder($id) {
        $order = $this->orderModel->getById($id);
        if ($order) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $order]);
        } else {
            $this->jsonResponse(false, 'Không tìm thấy đơn hàng.');
        }
        exit;
    }

    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            $id = $data['id'] ?? '';
            $paymentStatus = $data['payment_status'] ?? '';
            $shippingStatus = $data['shipping_status'] ?? '';
            $notes = $data['notes'] ?? '';

            if (empty($id) || empty($paymentStatus) || empty($shippingStatus)) {
                $this->jsonResponse(false, 'Dữ liệu không hợp lệ.');
                return;
            }

            if ($this->orderModel->updateStatus($id, $paymentStatus, $shippingStatus, $notes)) {
                $this->jsonResponse(true, 'Cập nhật trạng thái đơn hàng thành công!');
            } else {
                $this->jsonResponse(false, 'Lỗi hệ thống khi cập nhật.');
            }
        }
    }

    private function jsonResponse($success, $message) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }
}
