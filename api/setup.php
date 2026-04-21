<?php
// This file creates required upload directories on first run
$dirs = [
    '../uploads/',
    '../uploads/profiles/',
    '../uploads/sliders/',
    '../uploads/payments/',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        file_put_contents($dir . '.htaccess', "Options -Indexes\n<FilesMatch '\.php$'>\nDeny from all\n</FilesMatch>");
    }
}
echo json_encode(['ok' => true, 'message' => 'Upload directories created']);
