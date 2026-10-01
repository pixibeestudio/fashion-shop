<?php
require_once __DIR__ . '/../../app/helpers/session.php';
require_once __DIR__ . '/../../app/config/Database.php';
require_once __DIR__ . '/../../app/controllers/CartController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$code = trim($data['code'] ?? '');

if (empty($code)) {
    unset($_SESSION['promo']);
    echo json_encode(['success' => true, 'message' => 'Đã hủy mã', 'discount' => 0]);
    exit;
}

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM promotions WHERE code = :code");
$stmt->execute(['code' => $code]);
$promo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$promo) {
    echo json_encode(['success' => false, 'message' => 'Mã khuyến mãi không tồn tại.']);
    exit;
}

if ($promo['status'] === 'disabled') {
    echo json_encode(['success' => false, 'message' => 'Mã khuyến mãi này đã bị vô hiệu hóa.']);
    exit;
}

if ($promo['status'] === 'expired' || strtotime($promo['end_date']) < time()) {
    echo json_encode(['success' => false, 'message' => 'Mã khuyến mãi đã hết hạn sử dụng.']);
    exit;
}

if (strtotime($promo['start_date']) > time()) {
    echo json_encode(['success' => false, 'message' => 'Mã khuyến mãi chưa đến thời gian áp dụng.']);
    exit;
}

// Calculate discount amount based on Cart Subtotal
$cartController = new CartController();
$cartData = $cartController->getCartItems();
$subtotal = $cartData['total'];

if ($subtotal <= 0) {
    echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống.']);
    exit;
}

$discountAmount = 0;
if ($promo['discount_type'] === 'percent') {
    $discountAmount = $subtotal * ($promo['discount_value'] / 100);
} else {
    $discountAmount = $promo['discount_value'];
}

// Cap the discount so it doesn't exceed subtotal
if ($discountAmount > $subtotal) {
    $discountAmount = $subtotal;
}

// Save to session
$_SESSION['promo'] = [
    'id' => $promo['id'],
    'code' => $promo['code'],
    'discount_amount' => $discountAmount
];

echo json_encode([
    'success' => true,
    'message' => 'Áp dụng thành công!',
    'code' => $promo['code'],
    'discount' => $discountAmount
]);
