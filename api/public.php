<?php
require_once __DIR__ . '/config.php';

$endpoint = $_GET['ep'] ?? '';
$db = getDB();

switch ($endpoint) {
    // Sliders
    case 'sliders':
        $rows = $db->query("SELECT * FROM sliders WHERE is_active=1 ORDER BY sort_order ASC")->fetchAll();
        respond($rows);
        break;

    // Home stats
    case 'stats':
        $rows = $db->query("SELECT * FROM stats WHERE is_active=1 ORDER BY sort_order ASC")->fetchAll();
        respond($rows);
        break;

    // Categories
    case 'categories':
        $rows = $db->query("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order ASC")->fetchAll();
        respond($rows);
        break;

    // Cards (optionally by category)
    case 'cards':
        $catId = $_GET['category_id'] ?? null;
        if ($catId) {
            $stmt = $db->prepare("SELECT c.*, cat.name AS category_name FROM cards c LEFT JOIN categories cat ON c.category_id=cat.id WHERE c.is_active=1 AND c.category_id=? ORDER BY c.sort_order ASC");
            $stmt->execute([$catId]);
        } else {
            $stmt = $db->query("SELECT c.*, cat.name AS category_name FROM cards c LEFT JOIN categories cat ON c.category_id=cat.id WHERE c.is_active=1 ORDER BY c.sort_order ASC");
        }
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) { $r['prices'] = json_decode($r['prices'], true); }
        respond($rows);
        break;

    // Single card
    case 'card':
        $id = $_GET['id'] ?? null;
        if (!$id) respondError('Card ID required');
        $stmt = $db->prepare("SELECT c.*, cat.name AS category_name FROM cards c LEFT JOIN categories cat ON c.category_id=cat.id WHERE c.id=? AND c.is_active=1");
        $stmt->execute([$id]);
        $card = $stmt->fetch();
        if (!$card) respondError('Card not found', 404);
        $card['prices'] = json_decode($card['prices'], true);
        respond($card);
        break;

    // Order form fields
    case 'fields':
        $section = $_GET['section'] ?? null;
        if ($section) {
            $stmt = $db->prepare("SELECT * FROM order_fields WHERE is_active=1 AND section=? ORDER BY sort_order ASC");
            $stmt->execute([$section]);
        } else {
            $stmt = $db->query("SELECT * FROM order_fields WHERE is_active=1 ORDER BY section, sort_order ASC");
        }
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) { if ($r['options']) $r['options'] = json_decode($r['options'], true); }
        respond($rows);
        break;

    // Shipping rates
    case 'shipping':
        $rows = $db->query("SELECT * FROM shipping_rates WHERE is_active=1 ORDER BY city ASC")->fetchAll();
        respond($rows);
        break;

    // Active payment methods
    case 'payment_methods':
        $rows = $db->query("SELECT id, name, type, account_number, account_title, instructions, logo_icon FROM payment_methods WHERE is_active=1 ORDER BY sort_order ASC")->fetchAll();
        respond($rows);
        break;

    default:
        respondError('Invalid endpoint', 404);
}
