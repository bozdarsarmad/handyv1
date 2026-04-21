<?php
require_once __DIR__ . '/api/config.php';
$db = getDB();

// Fix description field - remove special characters that might break JS
$db->query("UPDATE cards SET description='Get your Matric certificates verified and delivered' WHERE id=4");

// Also ensure prices JSON is properly escaped
$db->query("UPDATE cards SET prices='{\"Migration\":500,\"Certificate\":2000,\"Degree\":3500}' WHERE id=4");

echo "Fixed!";