<?php
require_once __DIR__ . '/api/config.php';
$db = getDB();
$rows = $db->query("SELECT id, title, image, prices FROM cards ORDER BY id")->fetchAll();
foreach($rows as $r){
    echo "ID: ".$r['id']."\n";
    echo "Title: ".$r['title']."\n";
    echo "Image: ".$r['image']."\n";
    echo "Prices: ".$r['prices']."\n";
    echo "---\n";
}