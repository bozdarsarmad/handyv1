<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        if ($method !== 'POST') respondError('Method not allowed', 405);
        $data = json_decode(file_get_contents('php://input'), true);
        $name  = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $pass  = $data['password'] ?? '';
        $phone = trim($data['phone'] ?? '');
        if (!$name || !$email || !$pass) respondError('Name, email and password required');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) respondError('Invalid email');
        if (strlen($pass) < 6) respondError('Password must be at least 6 characters');
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) respondError('Email already registered');
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password, phone) VALUES (?,?,?,?)");
        $stmt->execute([$name, $email, $hash, $phone]);
        $userId = $db->lastInsertId();
        $token = generateToken(['id' => $userId, 'email' => $email, 'role' => 'user', 'name' => $name]);
        respondSuccess(['token' => $token, 'user' => ['id' => $userId, 'name' => $name, 'email' => $email, 'phone' => $phone, 'profile_pic' => null, 'role' => 'user']], 'Registration successful');
        break;

    case 'login':
        if ($method !== 'POST') respondError('Method not allowed', 405);
        $data = json_decode(file_get_contents('php://input'), true);
        $email = trim($data['email'] ?? '');
        $pass  = $data['password'] ?? '';
        if (!$email || !$pass) respondError('Email and password required');
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($pass, $user['password'])) respondError('Invalid credentials');
        $token = generateToken(['id' => $user['id'], 'email' => $user['email'], 'role' => $user['role'], 'name' => $user['name']]);
        unset($user['password']);
        respondSuccess(['token' => $token, 'user' => $user], 'Login successful');
        break;

    case 'me':
        $auth = requireAuth();
        $db = getDB();
        $stmt = $db->prepare("SELECT id, name, email, phone, profile_pic, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$auth['id']]);
        $user = $stmt->fetch();
        if (!$user) respondError('User not found', 404);
        respondSuccess(['user' => $user]);
        break;

    case 'update':
        if ($method !== 'POST') respondError('Method not allowed', 405);
        $auth = requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);
        $name  = trim($data['name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        if (!$name) respondError('Name is required');
        $db = getDB();
        $stmt = $db->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $auth['id']]);
        $stmt = $db->prepare("SELECT id, name, email, phone, profile_pic, role FROM users WHERE id = ?");
        $stmt->execute([$auth['id']]);
        $user = $stmt->fetch();
        respondSuccess(['user' => $user], 'Profile updated');
        break;

    case 'upload_pic':
        if ($method !== 'POST') respondError('Method not allowed', 405);
        $auth = requireAuth();
        if (!isset($_FILES['profile_pic'])) respondError('No file uploaded');
        $file = $_FILES['profile_pic'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed)) respondError('Only JPG, PNG, GIF, WEBP allowed');
        if ($file['size'] > 5 * 1024 * 1024) respondError('File too large (max 5MB)');
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $auth['id'] . '_' . time() . '.' . $ext;
        $uploadDir = UPLOAD_PATH . 'profiles/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) respondError('Upload failed');
        $picUrl = UPLOAD_URL . 'profiles/' . $filename;
        $db = getDB();
        $stmt = $db->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->execute([$picUrl, $auth['id']]);
        respondSuccess(['profile_pic' => $picUrl], 'Profile picture updated');
        break;

    default:
        respondError('Invalid action', 404);
}
