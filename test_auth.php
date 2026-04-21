<?php
// Test auth issue
$token = json_decode($_GET['token'] ?? '', true);
echo "Token type: " . gettype($token) . "\n";
echo "Token: " . ($token ?: 'empty');

$auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
echo "\n\nAuth header: " . ($auth ?: 'none');