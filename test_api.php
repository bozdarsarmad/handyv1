<?php
require_once __DIR__ . '/api/config.php';

header('Content-Type: application/json');

// Get token from header
$auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/Bearer\s+(.+)/i', $auth, $m)) {
    echo json_encode(['success'=>false,'error'=>'No token']);
    exit;
}
$token = $m[1];

// Verify token
try {
    $decoded = jwt_verify($token, JWT_SECRET);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'error'=>'Invalid token']);
    exit;
}

// Check admin role
if ($decoded['role'] !== 'admin') {
    echo json_encode(['success'=>false,'error'=>'Not admin']);
    exit;
}

// Get users
$db = getDB();
$users = $db->query("SELECT id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();
echo json_encode($users);