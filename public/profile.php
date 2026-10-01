<?php
$pageTitle = 'Trang cá nhân - Fashion Shop';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../app/config/Database.php';

// Auth Check
if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

$customerId = $_SESSION['customer_id'];
$db = Database::getInstance()->getConnection();
$message = '';
$error = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        if (empty($fullName) || empty($phone)) {
            $error = "Vui lòng nhập đầy đủ Họ tên và Số điện thoại.";
        } else {
            $stmt = $db->prepare("UPDATE customers SET full_name = :name, phone = :phone WHERE id = :id");
            if ($stmt->execute(['name' => $fullName, 'phone' => $phone, 'id' => $customerId])) {
                $_SESSION['customer_name'] = $fullName;
                $_SESSION['customer_phone'] = $phone;
                $message = "Cập nhật thông tin cá nhân thành công!";
            } else {
                $error = "Có lỗi xảy ra khi cập nhật thông tin.";
            }
        }
    } elseif ($action === 'add_address') {
        $receiverName = trim($_POST['receiver_name'] ?? '');
        $phone = trim($_POST['address_phone'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $ward = trim($_POST['ward'] ?? '');
        $addressLine = trim($_POST['address_line'] ?? '');
        $isDefault = isset($_POST['is_default']) ? 1 : 0;
        
        if (empty($receiverName) || empty($phone) || empty($city) || empty($district) || empty($ward) || empty($addressLine)) {
            $error = "Vui lòng nhập đầy đủ thông tin địa chỉ.";
        } else {
            if ($isDefault) {
                // Remove default flag from other addresses
                $db->prepare("UPDATE customer_addresses SET is_default = 0 WHERE customer_id = ?")->execute([$customerId]);
            }
            
            $stmt = $db->prepare("INSERT INTO customer_addresses (customer_id, is_default, receiver_name, phone, city, district, ward, address_line) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$customerId, $isDefault, $receiverName, $phone, $city, $district, $ward, $addressLine])) {
                $message = "Thêm địa chỉ mới thành công!";
            } else {
                $error = "Lỗi khi thêm địa chỉ.";
            }
        }
    } elseif ($action === 'delete_address') {
        $addressId = $_POST['address_id'] ?? 0;
        if ($addressId) {
            $stmt = $db->prepare("DELETE FROM customer_addresses WHERE id = ? AND customer_id = ?");
            if ($stmt->execute([$addressId, $customerId])) {
                $message = "Xóa địa chỉ thành công!";
            } else {
                $error = "Lỗi khi xóa địa chỉ.";
            }
        }
    }
}

// Fetch Customer & Tier info
$stmt = $db->prepare("
    SELECT c.*, t.name as tier_name, t.discount_percent 
    FROM customers c 
    LEFT JOIN customer_tiers t ON c.tier_id = t.id 
    WHERE c.id = :id
");
$stmt->execute(['id' => $customerId]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch Addresses
$stmtAddr = $db->prepare("SELECT * FROM customer_addresses WHERE customer_id = :cid ORDER BY is_default DESC, id DESC");
$stmtAddr->execute(['cid' => $customerId]);
$addresses = $stmtAddr->fetchAll(PDO::FETCH_ASSOC);

// Fetch Orders
$stmtOrders = $db->prepare("
    SELECT * FROM orders 
    WHERE customer_id = :cid 
    ORDER BY created_at DESC
");
$stmtOrders->execute(['cid' => $customerId]);
$orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

// Define active tab
$tab = $_GET['tab'] ?? 'info';

// Function to style order status
function getOrderStatusHTML($status) {
    switch ($status) {
        case 'pending':
            return '<span style="background: #fef3c7; color: #d97706; padding: 4px 10px; border-radius: 99px; font-size: 13px; font-weight: 600;">Chờ xử lý</span>';
        case 'processing':
            return '<span style="background: #e0f2fe; color: #0284c7; padding: 4px 10px; border-radius: 99px; font-size: 13px; font-weight: 600;">Đang xử lý</span>';
        case 'shipped':
            return '<span style="background: #f3e8ff; color: #7e22ce; padding: 4px 10px; border-radius: 99px; font-size: 13px; font-weight: 600;">Đang giao</span>';
        case 'delivered':
            return '<span style="background: #dcfce7; color: #16a34a; padding: 4px 10px; border-radius: 99px; font-size: 13px; font-weight: 600;">Đã giao</span>';
        case 'cancelled':
            return '<span style="background: #fee2e2; color: #dc2626; padding: 4px 10px; border-radius: 99px; font-size: 13px; font-weight: 600;">Đã hủy</span>';
        default:
            return '<span style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 99px; font-size: 13px; font-weight: 600;">' . ucfirst($status) . '</span>';
    }
}
?>

<div class="store-wrapper" style="margin-top: 40px; margin-bottom: 80px;">
    <div style="display: grid; grid-template-columns: 280px 1fr; gap: 32px; align-items: start;">
        
        <!-- Sidebar -->
        <div style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 24px;">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid #e2e8f0;">
                <div style="width: 56px; height: 56px; background: #e0e7ff; color: #4f46e5; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700;">
                    <?= strtoupper(substr($customer['full_name'], 0, 1)) ?>
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 16px; margin-bottom: 4px;"><?= htmlspecialchars($customer['full_name']) ?></div>
                    <div style="color: var(--text-muted); font-size: 13px;"><?= htmlspecialchars($customer['email']) ?></div>
                </div>
            </div>
            
            <nav style="display: flex; flex-direction: column; gap: 8px;">
                <a href="profile.php?tab=info" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; text-decoration: none; color: <?= $tab === 'info' ? 'var(--primary)' : 'var(--text-color)' ?>; font-weight: <?= $tab === 'info' ? '600' : '500' ?>; background: <?= $tab === 'info' ? '#e0e7ff' : 'transparent' ?>; transition: 0.2s;">
                    <i class="far fa-user"></i> Thông tin cá nhân
                </a>
                <a href="profile.php?tab=orders" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; text-decoration: none; color: <?= $tab === 'orders' ? 'var(--primary)' : 'var(--text-color)' ?>; font-weight: <?= $tab === 'orders' ? '600' : '500' ?>; background: <?= $tab === 'orders' ? '#e0e7ff' : 'transparent' ?>; transition: 0.2s;">
                    <i class="fas fa-shopping-bag"></i> Lịch sử mua hàng
                </a>
                <a href="logout.php" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; text-decoration: none; color: var(--danger); font-weight: 500; transition: 0.2s;">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div>
            <?php if ($error): ?>
                <div style="background: #fee2e2; color: #dc2626; padding: 16px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <?php if ($message): ?>
                <div style="background: #dcfce7; color: #16a34a; padding: 16px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'info'): ?>
                <h1 style="font-size: 24px; margin-bottom: 24px; color: var(--text-color);">Thông tin cá nhân</h1>
                
                <!-- Personal Info Form -->
                <div style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 32px; margin-bottom: 32px;">
                    <form method="POST" action="profile.php?tab=info">
                        <input type="hidden" name="action" value="update_profile">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Họ và tên</label>
                                <input type="text" name="full_name" value="<?= htmlspecialchars($customer['full_name']) ?>" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required>
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Số điện thoại</label>
                                <input type="text" name="phone" value="<?= htmlspecialchars($customer['phone'] ?? '') ?>" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required>
                            </div>
                            <div style="grid-column: span 2;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Email (Tài khoản đăng nhập)</label>
                                <input type="email" value="<?= htmlspecialchars($customer['email']) ?>" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 6px; box-sizing: border-box; background: #f8fafc; color: #64748b;" readonly>
                                <div style="font-size: 12px; color: var(--text-muted); margin-top: 6px;">Không thể thay đổi email đã dùng để đăng ký.</div>
                            </div>
                        </div>
                        <button type="submit" class="store-btn" style="padding: 12px 24px; font-size: 15px;">Lưu thay đổi</button>
                    </form>
                </div>

                <!-- Addresses Section -->
                <h2 style="font-size: 20px; margin-bottom: 24px; color: var(--text-color);">Sổ địa chỉ</h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; margin-bottom: 24px;">
                    <?php foreach ($addresses as $addr): ?>
                    <div style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 24px; border: 1px solid <?= $addr['is_default'] ? 'var(--primary)' : '#e2e8f0' ?>; position: relative;">
                        <?php if ($addr['is_default']): ?>
                            <span style="position: absolute; top: 12px; right: 12px; background: #e0e7ff; color: var(--primary); font-size: 11px; padding: 4px 8px; border-radius: 4px; font-weight: 600;">Mặc định</span>
                        <?php endif; ?>
                        
                        <div style="font-weight: 600; font-size: 16px; margin-bottom: 8px;"><?= htmlspecialchars($addr['receiver_name']) ?></div>
                        <div style="color: var(--text-muted); font-size: 14px; margin-bottom: 4px;"><i class="fas fa-phone" style="width: 16px;"></i> <?= htmlspecialchars($addr['phone']) ?></div>
                        <div style="color: var(--text-muted); font-size: 14px; margin-bottom: 16px; line-height: 1.5;">
                            <i class="fas fa-map-marker-alt" style="width: 16px;"></i>
                            <?= htmlspecialchars($addr['address_line']) ?><br>
                            <span style="margin-left: 20px;"><?= htmlspecialchars($addr['ward'] . ', ' . $addr['district'] . ', ' . $addr['city']) ?></span>
                        </div>
                        
                        <form method="POST" action="profile.php?tab=info" onsubmit="return confirm('Bạn có chắc chắn muốn xóa địa chỉ này?');">
                            <input type="hidden" name="action" value="delete_address">
                            <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                            <button type="submit" style="background: none; border: none; color: var(--danger); cursor: pointer; font-weight: 500; padding: 0;"><i class="fas fa-trash-alt"></i> Xóa</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Add New Address Form -->
                <div style="background: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1; padding: 24px;">
                    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px;">Thêm địa chỉ mới</h3>
                    <form method="POST" action="profile.php?tab=info">
                        <input type="hidden" name="action" value="add_address">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div>
                                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px;">Tên người nhận</label>
                                <input type="text" name="receiver_name" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required>
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px;">Số điện thoại</label>
                                <input type="text" name="address_phone" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div>
                                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px;">Tỉnh / Thành phố</label>
                                <input type="text" name="city" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required>
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px;">Quận / Huyện</label>
                                <input type="text" name="district" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required>
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px;">Phường / Xã</label>
                                <input type="text" name="ward" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required>
                            </div>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px;">Địa chỉ cụ thể (Số nhà, đường...)</label>
                            <input type="text" name="address_line" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required>
                        </div>
                        <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px; cursor: pointer;">
                            <input type="checkbox" name="is_default" value="1" style="width: 16px; height: 16px; accent-color: var(--primary);">
                            <span style="font-size: 14px; font-weight: 500;">Đặt làm địa chỉ mặc định</span>
                        </label>
                        <button type="submit" class="store-btn" style="padding: 10px 20px; font-size: 14px; background: white; color: var(--primary); border: 1px solid var(--primary);">Thêm địa chỉ</button>
                    </form>
                </div>

            <?php else: ?>
                <!-- Orders Tab -->
                <h1 style="font-size: 24px; margin-bottom: 24px; color: var(--text-color);">Lịch sử mua hàng</h1>
                
                <?php if (empty($orders)): ?>
                    <div style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 60px 20px; text-align: center;">
                        <i class="fas fa-box-open" style="font-size: 48px; color: #cbd5e1; margin-bottom: 16px;"></i>
                        <h3 style="margin-bottom: 8px;">Chưa có đơn hàng nào</h3>
                        <p style="color: var(--text-muted); margin-bottom: 24px;">Bạn chưa thực hiện giao dịch nào. Hãy khám phá các sản phẩm của chúng tôi!</p>
                        <a href="category.php" class="store-btn" style="padding: 12px 24px;">Mua sắm ngay</a>
                    </div>
                <?php else: ?>
                    <div style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
                                    <th style="padding: 16px; font-weight: 600; color: var(--text-muted); font-size: 14px;">Mã Đơn</th>
                                    <th style="padding: 16px; font-weight: 600; color: var(--text-muted); font-size: 14px;">Ngày Đặt</th>
                                    <th style="padding: 16px; font-weight: 600; color: var(--text-muted); font-size: 14px;">Tổng Tiền</th>
                                    <th style="padding: 16px; font-weight: 600; color: var(--text-muted); font-size: 14px;">Trạng Thái</th>
                                    <th style="padding: 16px; font-weight: 600; color: var(--text-muted); font-size: 14px; text-align: right;">Chi Tiết</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr style="border-bottom: 1px solid #e2e8f0; transition: 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                    <td style="padding: 16px; font-weight: 600; font-family: monospace; font-size: 15px;">
                                        <?= htmlspecialchars($order['order_number']) ?>
                                    </td>
                                    <td style="padding: 16px; color: var(--text-muted); font-size: 14px;">
                                        <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                    </td>
                                    <td style="padding: 16px; font-weight: 700; color: var(--primary);">
                                        <?= number_format($order['total']) ?>đ
                                    </td>
                                    <td style="padding: 16px;">
                                        <?= getOrderStatusHTML($order['shipping_status']) ?>
                                    </td>
                                    <td style="padding: 16px; text-align: right;">
                                        <a href="order_detail.php?id=<?= $order['id'] ?>" style="color: var(--primary); text-decoration: none; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
                                            Xem <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
