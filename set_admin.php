<?php
require_once __DIR__ . '/api/config.php';
$db = getDB();

// Create/update admin user
$email = 'admin@handy.pk';
$name = 'Admin';
$pass = 'admin123'; // Set password to admin123
$hash = password_hash($pass, PASSWORD_BCRYPT);

$stmt = $db->prepare("UPDATE users SET password=? WHERE email=? AND role='admin'");
$stmt->execute([$hash, $email]);

echo "Admin password set to: $pass";