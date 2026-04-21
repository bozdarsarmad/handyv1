<?php
require_once __DIR__ . '/api/config.php';
// Test login
$input = json_decode('{"email":"admin@handy.pk","password":"admin123"}', true);

$email = $input['email'];
$pass = $input['password'];

$db = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(["success"=>false, "error"=>"User not found"]);
    exit;
}

if (!password_verify($pass, $user['password'])) {
    echo json_encode(["success"=>false, "error"=>"Password wrong"]);
    exit;
}

$token = generateToken(['id'=>$user['id'], 'email'=>$user['email'], 'role'=>$user['role'], 'name'=>$user['name']]);

echo json_encode(["success"=>true, "token"=>$token, "user"=>["id"=>$user['id'], "name"=>$user['name'], "email"=>$user['email'], "role"=>$user['role']]]);