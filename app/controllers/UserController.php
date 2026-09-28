<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Role.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../helpers/session.php';

class UserController {
    private $userModel;
    private $roleModel;

    public function __construct() {
        $this->userModel = new User();
        $this->roleModel = new Role();
    }

    public function paginate($page = 1) {
        return $this->userModel->paginate($page, 10);
    }

    public function getRoles() {
        return $this->roleModel->getAll();
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            $fullName = Validator::sanitize($data['full_name'] ?? '');
            $email = Validator::sanitize($data['email'] ?? '');
            $phone = Validator::sanitize($data['phone'] ?? '');
            $roleId = $data['role_id'] ?? '';
            $password = $data['password'] ?? '';

            if (empty($fullName) || empty($email) || empty($phone) || empty($roleId) || empty($password)) {
                $this->jsonResponse(false, 'Vui lòng nhập đầy đủ thông tin bắt buộc.');
            }

            if ($roleId == 1) {
                $this->jsonResponse(false, 'Không thể tạo thêm tài khoản Admin. Hệ thống chỉ cho phép 1 Admin duy nhất!');
            }

            if (!Validator::isEmail($email)) {
                $this->jsonResponse(false, 'Định dạng email không hợp lệ.');
            }

            if (!Validator::isPhone($phone)) {
                $this->jsonResponse(false, 'Số điện thoại không hợp lệ (cần 10 số, chuẩn Việt Nam).');
            }

            if (!Validator::isStrongPassword($password)) {
                $this->jsonResponse(false, 'Mật khẩu phải từ 8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt.');
            }

            if ($this->userModel->checkEmailExists($email)) {
                $this->jsonResponse(false, 'Email này đã tồn tại trong hệ thống.');
            }

            if ($this->userModel->checkPhoneExists($phone)) {
                $this->jsonResponse(false, 'Số điện thoại này đã tồn tại trong hệ thống.');
            }

            if ($this->userModel->create([
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'role_id' => $roleId,
                'password' => $password
            ])) {
                $this->jsonResponse(true, 'Thêm nhân viên thành công!');
            } else {
                $this->jsonResponse(false, 'Lỗi hệ thống khi thêm nhân viên.');
            }
        }
    }

    public function updateInfo() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $id = $data['id'] ?? '';
            $fullName = Validator::sanitize($data['full_name'] ?? '');
            $phone = Validator::sanitize($data['phone'] ?? '');
            $email = Validator::sanitize($data['email'] ?? '');
            $roleId = $data['role_id'] ?? '';

            if (empty($id) || empty($fullName) || empty($email) || empty($phone) || empty($roleId)) {
                $this->jsonResponse(false, 'Vui lòng điền đầy đủ các thông tin.');
            }

            // Check if they are trying to assign Admin role to another user
            if ($roleId == 1) {
                // If it's updating the existing Admin, it's fine, but we can't change someone else to Admin.
                // We should check the current role of the user being updated.
                $currentUser = $this->userModel->findById($id);
                if ($currentUser && $currentUser['role_id'] != 1) {
                    $this->jsonResponse(false, 'Không thể cấp quyền Admin cho tài khoản khác!');
                }
            } else {
                // Prevent removing Admin role from the only Admin
                $currentUser = $this->userModel->findById($id);
                if ($currentUser && $currentUser['role_id'] == 1) {
                    $this->jsonResponse(false, 'Không thể tước quyền của tài khoản Admin gốc!');
                }
            }

            if (!Validator::isEmail($email)) {
                $this->jsonResponse(false, 'Định dạng email không hợp lệ.');
            }

            if (!Validator::isPhone($phone)) {
                $this->jsonResponse(false, 'Số điện thoại không hợp lệ.');
            }

            if ($this->userModel->checkEmailExists($email, $id)) {
                $this->jsonResponse(false, 'Email này đã được sử dụng bởi người khác.');
            }

            if ($this->userModel->checkPhoneExists($phone, $id)) {
                $this->jsonResponse(false, 'Số điện thoại này đã được sử dụng bởi người khác.');
            }

            if ($this->userModel->updateInfo($id, $roleId, $fullName, $phone, $email)) {
                $this->jsonResponse(true, 'Cập nhật thông tin thành công!');
            } else {
                $this->jsonResponse(false, 'Lỗi hệ thống khi cập nhật.');
            }
        }
    }

    public function toggleStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $id = $data['id'] ?? '';

            $currentAdminId = $_SESSION['admin_id'] ?? null;

            if (empty($id)) {
                $this->jsonResponse(false, 'Không xác định được nhân viên.');
            }

            if ($id == $currentAdminId) {
                $this->jsonResponse(false, 'Bạn không thể tự khóa tài khoản của chính mình!');
            }

            if ($this->userModel->toggleStatus($id)) {
                $this->jsonResponse(true, 'Cập nhật trạng thái thành công!');
            } else {
                $this->jsonResponse(false, 'Lỗi hệ thống khi cập nhật.');
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
            }

            if (!Validator::isStrongPassword($newPassword)) {
                $this->jsonResponse(false, 'Mật khẩu phải từ 8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt.');
            }

            if ($this->userModel->resetPassword($id, $newPassword)) {
                $this->jsonResponse(true, 'Đã đặt lại mật khẩu thành công!');
            } else {
                $this->jsonResponse(false, 'Lỗi hệ thống khi đặt lại mật khẩu.');
            }
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $id = $data['id'] ?? '';

            $currentAdminId = $_SESSION['admin_id'] ?? null;

            if (empty($id)) {
                $this->jsonResponse(false, 'Không xác định được nhân viên.');
            }

            if ($id == $currentAdminId) {
                $this->jsonResponse(false, 'Bạn không thể tự xóa tài khoản của chính mình!');
            }

            $user = $this->userModel->findById($id);
            if ($user && $user['role_id'] == 1) {
                $this->jsonResponse(false, 'Không thể xóa tài khoản Admin gốc!');
            }

            $result = $this->userModel->delete($id);
            if ($result === true) {
                $this->jsonResponse(true, 'Đã xóa nhân viên thành công!');
            } else if ($result === 'constraint') {
                $this->jsonResponse(false, 'Không thể xóa nhân viên này vì họ đã tham gia xử lý các chứng từ (vd: Nhập kho). Vui lòng sử dụng tính năng Khóa tài khoản.');
            } else {
                $this->jsonResponse(false, 'Lỗi hệ thống khi xóa.');
            }
        }
    }

    private function jsonResponse($success, $message, $data = null) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
        exit;
    }
}
