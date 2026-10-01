<?php
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../config/Database.php';

class CartController {
    
    private function initCart() {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    private function getProductVariant($variantId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT pv.*, p.name as product_name, p.slug as product_slug, 
                   (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
            FROM product_variants pv
            JOIN products p ON pv.product_id = p.id
            WHERE pv.id = :id
        ");
        $stmt->execute(['id' => $variantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $this->initCart();

        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) $data = $_POST;

        $variantId = $data['variant_id'] ?? 0;
        $quantity = (int)($data['quantity'] ?? 1);

        if (!$variantId || $quantity <= 0) {
            $this->jsonResponse(false, 'Dữ liệu không hợp lệ.');
            return;
        }

        // Validate variant
        $variant = $this->getProductVariant($variantId);
        if (!$variant) {
            $this->jsonResponse(false, 'Sản phẩm hoặc biến thể không tồn tại.');
            return;
        }

        // Check stock
        $currentCartQty = isset($_SESSION['cart'][$variantId]) ? $_SESSION['cart'][$variantId]['quantity'] : 0;
        $newQty = $currentCartQty + $quantity;

        if ($newQty > $variant['stock_quantity']) {
            $this->jsonResponse(false, 'Số lượng yêu cầu vượt quá số lượng tồn kho hiện có (' . $variant['stock_quantity'] . ').');
            return;
        }

        // Add to cart
        if (isset($_SESSION['cart'][$variantId])) {
            $_SESSION['cart'][$variantId]['quantity'] = $newQty;
        } else {
            $_SESSION['cart'][$variantId] = [
                'variant_id' => $variantId,
                'product_id' => $variant['product_id'],
                'quantity' => $quantity
            ];
        }

        $this->jsonResponse(true, 'Đã thêm vào giỏ hàng!', [
            'cart_count' => $this->getCartCount()
        ]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $this->initCart();

        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) $data = $_POST;

        $variantId = $data['variant_id'] ?? 0;
        $quantity = (int)($data['quantity'] ?? 0);

        if (!$variantId || !isset($_SESSION['cart'][$variantId])) {
            $this->jsonResponse(false, 'Sản phẩm không có trong giỏ hàng.');
            return;
        }

        if ($quantity <= 0) {
            unset($_SESSION['cart'][$variantId]);
            $this->jsonResponse(true, 'Đã xóa sản phẩm khỏi giỏ hàng.', [
                'cart_count' => $this->getCartCount(),
                'cart_total' => $this->calculateTotal()
            ]);
            return;
        }

        // Validate stock
        $variant = $this->getProductVariant($variantId);
        if (!$variant) {
            unset($_SESSION['cart'][$variantId]);
            $this->jsonResponse(false, 'Sản phẩm không còn tồn tại.');
            return;
        }

        if ($quantity > $variant['stock_quantity']) {
            $this->jsonResponse(false, 'Số lượng yêu cầu vượt quá tồn kho (' . $variant['stock_quantity'] . ').', [
                'stock_quantity' => $variant['stock_quantity']
            ]);
            return;
        }

        $_SESSION['cart'][$variantId]['quantity'] = $quantity;
        
        $this->jsonResponse(true, 'Cập nhật thành công.', [
            'cart_count' => $this->getCartCount(),
            'cart_total' => $this->calculateTotal()
        ]);
    }

    public function remove() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $this->initCart();

        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) $data = $_POST;

        $variantId = $data['variant_id'] ?? 0;

        if ($variantId && isset($_SESSION['cart'][$variantId])) {
            unset($_SESSION['cart'][$variantId]);
            $this->jsonResponse(true, 'Đã xóa sản phẩm khỏi giỏ hàng.', [
                'cart_count' => $this->getCartCount(),
                'cart_total' => $this->calculateTotal()
            ]);
        } else {
            $this->jsonResponse(false, 'Không tìm thấy sản phẩm cần xóa.');
        }
    }

    public function getCartItems() {
        $this->initCart();
        $items = [];
        $total = 0;

        foreach ($_SESSION['cart'] as $variantId => $item) {
            $variant = $this->getProductVariant($variantId);
            if ($variant) {
                // Adjust quantity if stock is lower than in cart
                if ($item['quantity'] > $variant['stock_quantity']) {
                    $_SESSION['cart'][$variantId]['quantity'] = $variant['stock_quantity'];
                    $item['quantity'] = $variant['stock_quantity'];
                }
                
                $subtotal = $variant['price'] * $item['quantity'];
                $total += $subtotal;

                $items[] = [
                    'variant_id' => $variantId,
                    'product_id' => $variant['product_id'],
                    'product_name' => $variant['product_name'],
                    'product_slug' => $variant['product_slug'],
                    'color' => $variant['color'],
                    'size' => $variant['size'],
                    'price' => $variant['price'],
                    'quantity' => $item['quantity'],
                    'stock_quantity' => $variant['stock_quantity'],
                    'subtotal' => $subtotal,
                    'primary_image' => $variant['primary_image'] ?? '/fashion-shop/assets/images/placeholder.jpg'
                ];
            } else {
                // If variant no longer exists, remove it
                unset($_SESSION['cart'][$variantId]);
            }
        }

        return ['items' => $items, 'total' => $total, 'count' => $this->getCartCount()];
    }

    public function getCartCount() {
        $this->initCart();
        $count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    private function calculateTotal() {
        $cart = $this->getCartItems();
        return $cart['total'];
    }

    public function getCountApi() {
        $this->jsonResponse(true, 'Thành công', ['cart_count' => $this->getCartCount()]);
    }

    private function jsonResponse($success, $message, $data = []) {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
        exit;
    }
}
