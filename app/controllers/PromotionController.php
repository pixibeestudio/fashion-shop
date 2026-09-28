<?php
require_once __DIR__ . '/../models/Promotion.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../helpers/session.php';

class PromotionController {
    private $promotionModel;

    public function __construct() {
        $this->promotionModel = new Promotion();
    }

    public function paginate($page = 1) {
        return $this->promotionModel->paginate($page, 10);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            $code = strtoupper(Validator::sanitize($data['code'] ?? ''));
            $discountType = $data['discount_type'] ?? '';
            $discountValue = (float)($data['discount_value'] ?? 0);
            $startDate = str_replace('T', ' ', $data['start_date'] ?? '');
            $endDate = str_replace('T', ' ', $data['end_date'] ?? '');

            if (empty($code) || empty($discountType) || $discountValue <= 0 || empty($startDate) || empty($endDate)) {
                $this->jsonResponse(false, 'Vui lòng điền đầy đủ và chính xác thông tin.');
            }

            if (!in_array($discountType, ['percent', 'fixed'])) {
                $this->jsonResponse(false, 'Loại giảm giá không hợp lệ.');
            }

            if ($discountType === 'percent' && $discountValue > 100) {
                $this->jsonResponse(false, 'Khuyến mãi theo % không được vượt quá 100%.');
            }

            if (strtotime($startDate) >= strtotime($endDate)) {
                $this->jsonResponse(false, 'Ngày kết thúc phải lớn hơn ngày bắt đầu.');
            }

            if ($this->promotionModel->checkCodeExists($code)) {
                $this->jsonResponse(false, 'Mã giảm giá này đã tồn tại trong hệ thống.');
            }

            if ($this->promotionModel->create([
                'code' => $code,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'start_date' => $startDate,
                'end_date' => $endDate
            ])) {
                $this->jsonResponse(true, 'Tạo mã giảm giá thành công!');
            } else {
                $this->jsonResponse(false, 'Lỗi hệ thống khi tạo khuyến mãi.');
            }
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            $id = $data['id'] ?? '';
            $code = strtoupper(Validator::sanitize($data['code'] ?? ''));
            $discountType = $data['discount_type'] ?? '';
            $discountValue = (float)($data['discount_value'] ?? 0);
            $startDate = str_replace('T', ' ', $data['start_date'] ?? '');
            $endDate = str_replace('T', ' ', $data['end_date'] ?? '');

            if (empty($id) || empty($code) || empty($discountType) || $discountValue <= 0 || empty($startDate) || empty($endDate)) {
                $this->jsonResponse(false, 'Vui lòng điền đầy đủ và chính xác thông tin.');
            }

            if (!in_array($discountType, ['percent', 'fixed'])) {
                $this->jsonResponse(false, 'Loại giảm giá không hợp lệ.');
            }

            if ($discountType === 'percent' && $discountValue > 100) {
                $this->jsonResponse(false, 'Khuyến mãi theo % không được vượt quá 100%.');
            }

            if (strtotime($startDate) >= strtotime($endDate)) {
                $this->jsonResponse(false, 'Ngày kết thúc phải lớn hơn ngày bắt đầu.');
            }

            if ($this->promotionModel->checkCodeExists($code, $id)) {
                $this->jsonResponse(false, 'Mã giảm giá này đã bị trùng với một mã khác.');
            }

            if ($this->promotionModel->update($id, [
                'code' => $code,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'start_date' => $startDate,
                'end_date' => $endDate
            ])) {
                $this->jsonResponse(true, 'Cập nhật mã giảm giá thành công!');
            } else {
                $this->jsonResponse(false, 'Lỗi hệ thống khi cập nhật khuyến mãi.');
            }
        }
    }

    public function toggleStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $id = $data['id'] ?? '';

            if (empty($id)) {
                $this->jsonResponse(false, 'Không xác định được mã khuyến mãi.');
            }

            if ($this->promotionModel->toggleStatus($id)) {
                $this->jsonResponse(true, 'Cập nhật trạng thái thành công!');
            } else {
                $this->jsonResponse(false, 'Lỗi hệ thống khi cập nhật.');
            }
        }
    }

    private function jsonResponse($success, $message, $data = null) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
        exit;
    }
}
