<?php
// Đảm bảo trả về JSON ngay cả khi có fatal error
header('Content-Type: application/json');

// Bắt lỗi PHP thay vì để trả về HTML lỗi
set_error_handler(function($errno, $errstr) {
    echo json_encode(['success' => false, 'message' => "PHP Error: $errstr"]);
    exit;
});

try {
    require_once __DIR__ . '/../../../app/controllers/ProductController.php';
    $controller = new ProductController();
    $controller->delete();
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
