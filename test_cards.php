<?php
require_once __DIR__ . '/api/config.php';
$db = getDB();
$cards = $db->query("SELECT id, title FROM cards WHERE is_active=1 ORDER BY id")->fetchAll();
foreach ($cards as $c) {
    echo "Card ID: {$c['id']} - {$c['title']}\n";
}