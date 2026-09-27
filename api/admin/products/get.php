<?php
require_once __DIR__ . '/../../../app/models/Product.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID']);
    exit;
}

$productModel = new Product();
$product = $productModel->getById($id);

if ($product) {
    echo json_encode(['success' => true, 'data' => $product]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
}
