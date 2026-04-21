<?php
require_once __DIR__ . '/api/config.php';
$db = getDB();

// Login first to get token
$email = 'admin@handy.pk';
$pass = 'admin123';

$stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role='admin'");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($pass, $user['password'])) {
    echo json_encode(['error'=>'Invalid admin credentials']);
    exit;
}

$token = generateToken(['id'=>$user['id'], 'email'=>$user['email'], 'role'=>$user['role'], 'name'=>$user['name']]);

// Now use token to call admin API
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

$users = $db->query("SELECT id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();
echo json_encode(['success'=>true, 'users'=>$users, 'token'=>$token]);