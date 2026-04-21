<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db = getDB();

switch ($action) {

    // Create new order
    case 'create':
        if ($method !== 'POST') respondError('Method not allowed', 405);
        $auth = requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);

        $cardId             = $data['card_id'] ?? null;
        $selectedCerts      = $data['selected_certificates'] ?? [];
        $academicInfo       = $data['academic_info'] ?? [];
        $shippingInfo       = $data['shipping_info'] ?? [];

        if (!$cardId || empty($selectedCerts)) respondError('Card and certificates required');

        // Fetch card
        $stmt = $db->prepare("SELECT * FROM cards WHERE id=? AND is_active=1");
        $stmt->execute([$cardId]);
        $card = $stmt->fetch();
        if (!$card) respondError('Service not found');
        $card['prices'] = json_decode($card['prices'], true);

        // Calculate subtotal
        $subtotal = 0;
        foreach ($selectedCerts as $cert) {
            $price = $card['prices'][$cert] ?? 0;
            $subtotal += $price;
        }

        // Shipping cost
        $city = $shippingInfo['city'] ?? '';
        $shippingCost = 0;
        if ($city) {
            $stmt = $db->prepare("SELECT rate FROM shipping_rates WHERE city=? AND is_active=1");
            $stmt->execute([$city]);
            $rate = $stmt->fetch();
            $shippingCost = $rate ? $rate['rate'] : 0;
        }

        $total = $subtotal + $shippingCost;
        $orderNumber = generateOrderNumber();

        $stmt = $db->prepare("INSERT INTO orders (order_number, user_id, card_id, card_snapshot, selected_certificates, academic_info, shipping_info, shipping_cost, subtotal, total) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $orderNumber,
            $auth['id'],
            $cardId,
            json_encode($card),
            json_encode($selectedCerts),
            json_encode($academicInfo),
            json_encode($shippingInfo),
            $shippingCost,
            $subtotal,
            $total,
        ]);
        $orderId = $db->lastInsertId();

        respondSuccess(['order_id' => $orderId, 'order_number' => $orderNumber, 'total' => $total, 'subtotal' => $subtotal, 'shipping_cost' => $shippingCost], 'Order created successfully');
        break;

    // Get user's orders
    case 'my_orders':
        $auth = requireAuth();
        $stmt = $db->prepare("SELECT o.*, pm.name as payment_method_name, p.status as payment_status_detail, p.proof_image, p.transaction_id FROM orders o LEFT JOIN payments p ON p.order_id = o.id LEFT JOIN payment_methods pm ON p.method_id = pm.id WHERE o.user_id = ? ORDER BY o.created_at DESC");
        $stmt->execute([$auth['id']]);
        $orders = $stmt->fetchAll();
        foreach ($orders as &$o) {
            $o['selected_certificates'] = json_decode($o['selected_certificates'], true);
            $o['academic_info'] = json_decode($o['academic_info'], true);
            $o['shipping_info'] = json_decode($o['shipping_info'], true);
            $o['card_snapshot'] = json_decode($o['card_snapshot'], true);
        }
        respondSuccess(['orders' => $orders]);
        break;

    // Get single order
    case 'get':
        $auth = requireAuth();
        $orderId = $_GET['id'] ?? null;
        if (!$orderId) respondError('Order ID required');
        $stmt = $db->prepare("SELECT o.*, pm.name as payment_method_name, pm.account_number, pm.account_title, pm.instructions as payment_instructions, p.status as pay_status, p.proof_image, p.transaction_id, p.id as payment_id FROM orders o LEFT JOIN payments p ON p.order_id = o.id LEFT JOIN payment_methods pm ON p.method_id = pm.id WHERE o.id = ? AND o.user_id = ?");
        $stmt->execute([$orderId, $auth['id']]);
        $order = $stmt->fetch();
        if (!$order) respondError('Order not found', 404);
        $order['selected_certificates'] = json_decode($order['selected_certificates'], true);
        $order['academic_info'] = json_decode($order['academic_info'], true);
        $order['shipping_info'] = json_decode($order['shipping_info'], true);
        $order['card_snapshot'] = json_decode($order['card_snapshot'], true);
        respondSuccess(['order' => $order]);
        break;

    // Submit payment
    case 'pay':
        if ($method !== 'POST') respondError('Method not allowed', 405);
        $auth = requireAuth();
        $orderId  = $_POST['order_id'] ?? null;
        $methodId = $_POST['method_id'] ?? null;
        $txnId    = trim($_POST['transaction_id'] ?? '');
        if (!$orderId || !$methodId) respondError('Order and payment method required');

        // Verify order belongs to user
        $stmt = $db->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
        $stmt->execute([$orderId, $auth['id']]);
        $order = $stmt->fetch();
        if (!$order) respondError('Order not found', 404);

        $proofUrl = null;
        if (isset($_FILES['proof']) && $_FILES['proof']['error'] === 0) {
            $file = $_FILES['proof'];
            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            if (!in_array($file['type'], $allowed)) respondError('Only image files allowed for proof');
            if ($file['size'] > 5 * 1024 * 1024) respondError('Proof file too large');
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'pay_' . $orderId . '_' . time() . '.' . $ext;
            $dir = UPLOAD_PATH . 'payments/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
                $proofUrl = UPLOAD_URL . 'payments/' . $filename;
            }
        }

        // Check existing payment
        $stmt = $db->prepare("SELECT id FROM payments WHERE order_id=?");
        $stmt->execute([$orderId]);
        $existing = $stmt->fetch();
        if ($existing) {
            $stmt = $db->prepare("UPDATE payments SET method_id=?, transaction_id=?, proof_image=?, status='pending' WHERE order_id=?");
            $stmt->execute([$methodId, $txnId, $proofUrl, $orderId]);
        } else {
            $stmt = $db->prepare("INSERT INTO payments (order_id, method_id, amount, transaction_id, proof_image) VALUES (?,?,?,?,?)");
            $stmt->execute([$orderId, $methodId, $order['total'], $txnId, $proofUrl]);
        }

        $db->prepare("UPDATE orders SET payment_status='pending' WHERE id=?")->execute([$orderId]);
        respondSuccess([], 'Payment submitted for verification');
        break;

    default:
        respondError('Invalid action', 404);
}
