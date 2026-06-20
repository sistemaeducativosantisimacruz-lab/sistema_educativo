<?php
// Clear compiled views
$base_path = dirname(__DIR__);
$views_dir = $base_path . '/storage/framework/views';

if (is_dir($views_dir)) {
    $files = glob($views_dir . '/*.php');
    $count = 0;
    foreach ($files as $file) {
        if (basename($file) !== '.gitignore' && is_file($file)) {
            @unlink($file);
            $count++;
        }
    }
    echo "Deleted $count compiled views.\n";
}

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully!\n";
} else {
    echo "OPcache is not enabled or function does not exist.\n";
}
