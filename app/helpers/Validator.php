<?php

class Validator {
    /**
     * Data Validation: Kiểm tra định dạng số điện thoại chuẩn Việt Nam (10 số, bắt đầu bằng 03, 05, 07, 08, 09)
     */
    public static function isPhone($phone) {
        return preg_match('/^(0[3|5|7|8|9])+([0-9]{8})$/', $phone);
    }

    /**
     * Data Validation: Kiểm tra định dạng Email chuẩn
     */
    public static function isEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Business Rule: Mật khẩu tối thiểu 8 ký tự, gồm ít nhất 1 chữ hoa, 1 chữ thường, 1 số và 1 ký tự đặc biệt
     */
    public static function isStrongPassword($password) {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
    }

    /**
     * Business Rule: Không cho phép khách hàng đặt tên giả mạo Admin
     */
    public static function isReservedName($name) {
        $reserved = ['admin', 'administrator', 'quản trị', 'quan tri', 'system', 'root'];
        $nameLower = mb_strtolower($name, 'UTF-8');
        foreach ($reserved as $word) {
            if (strpos($nameLower, $word) !== false) {
                return true; // Tên chứa từ khóa cấm
            }
        }
        return false;
    }

    /**
     * Sanitization / Output Encoding: Ngăn chặn XSS
     */
    public static function sanitize($input) {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = self::sanitize($value);
            }
            return $input;
        }
        return htmlspecialchars(trim((string)$input), ENT_QUOTES, 'UTF-8');
    }
}
