<?php
// Clear compiled views
$views_dir = __DIR__ . '/storage/framework/views';
if (is_dir($views_dir)) {
    $files = glob($views_dir . '/*.php');
    foreach ($files as $file) {
        if (basename($file) !== '.gitignore' && is_file($file)) {
            unlink($file);
            echo "Deleted: " . basename($file) . "\n";
        }
    }
}

// Clear cache directory
$cache_dir = __DIR__ . '/storage/framework/cache';
if (is_dir($cache_dir)) {
    $files = glob($cache_dir . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            echo "Deleted cache: " . basename($file) . "\n";
        }
    }
}

echo "Cache cleared successfully!\n";
?>
