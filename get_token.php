<?php
require_once __DIR__ . '/api/config.php';

$email = 'admin@handy.pk';
$pass = 'admin123';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role='admin'");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($pass, $user['password'])) {
    echo json_encode(["success"=>false, "error"=>"Invalid credentials"]);
    exit;
}

$token = generateToken(['id'=>$user['id'], 'email'=>$user['email'], 'role'=>$user['role'], 'name'=>$user['name']]);

echo json_encode(["success"=>true, "token"=>$token, "user"=>["role"=>$user['role']]]);