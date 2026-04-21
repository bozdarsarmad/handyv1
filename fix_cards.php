<?php
require_once __DIR__ . '/api/config.php';
$db = getDB();

// Fix swapped title and image for ID 4
$db->query("UPDATE cards SET title='Matric Certificates', image='http://localhost/handy/uploads/sliders/card_1776745307.png' WHERE id=4");

// Fix swapped title and image for ID 5
$db->query("UPDATE cards SET title='Intermediate Certificates', image='http://localhost/handy/uploads/sliders/card_1776745381.png' WHERE id=5");

echo "Fixed!";
