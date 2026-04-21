<?php
require_once __DIR__ . '/api/config.php';
$db = getDB();
$users = $db->query("SELECT * FROM users")->fetchAll();
echo json_encode($users, JSON_PRETTY_PRINT);