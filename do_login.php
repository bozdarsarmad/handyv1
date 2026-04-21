<?php
require_once __DIR__ . '/api/config.php';

$data = ["email"=>"admin@handy.pk", "password"=>"admin123"];

// Call login action
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['action'] = 'login';

// Simulate the request
$method = 'POST';
$email = trim($data['email']);
$pass = $data['password'];

$db = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(["error"=>"User not found"]);
    exit;
}

if (!password_verify($pass, $user['password'])) {
    echo json_encode(["error"=>"Password wrong"]);
    exit;
}

$token = generateToken(['id' => $user['id'], 'email' => $user['email'], 'role' => $user['role'], 'name' => $user['name']]);
echo json_encode(["success"=>true, "token"=>$token, "user"=>["role"=>$user['role']]]);