<?php
// =============================================
// HANDY - Database Configuration
// =============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Change to your DB user
define('DB_PASS', '');            // Change to your DB password
define('DB_NAME', 'handy_db');
define('BASE_URL', 'http://localhost/handy'); // Change to your domain

// JWT secret key
define('JWT_SECRET', 'handy_secret_key_change_in_production_2024');
define('JWT_EXPIRY', 86400 * 7); // 7 days

// Upload paths
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
            exit;
        }
    }
    return $pdo;
}

function respond($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }
    echo json_encode($data);
    exit;
}

function respondError($msg, $code = 400) {
    respond(['success' => false, 'error' => $msg], $code);
}

function respondSuccess($data = [], $msg = 'Success') {
    respond(array_merge(['success' => true, 'message' => $msg], $data));
}

// Simple JWT implementation
function generateToken($payload) {
    $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
    $payload['exp'] = time() + JWT_EXPIRY;
    $payload = base64_encode(json_encode($payload));
    $signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET, true);
    $signature = base64_encode($signature);
    return "$header.$payload.$signature";
}

function verifyToken($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;
    [$header, $payload, $sig] = $parts;
    $expectedSig = base64_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    if ($sig !== $expectedSig) return false;
    $data = json_decode(base64_decode($payload), true);
    if (!$data || $data['exp'] < time()) return false;
    return $data;
}

function requireAuth() {
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (!$auth || !str_starts_with($auth, 'Bearer ')) {
        respondError('Unauthorized', 401);
    }
    $token = substr($auth, 7);
    $data = verifyToken($token);
    if (!$data) respondError('Invalid or expired token', 401);
    return $data;
}

function requireAdmin() {
    $user = requireAuth();
    if ($user['role'] !== 'admin') respondError('Forbidden', 403);
    return $user;
}

function generateOrderNumber() {
    return 'HND-' . strtoupper(substr(md5(time() . rand()), 0, 8));
}

// Handle CORS preflight
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}
