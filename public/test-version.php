<?php
$lock = json_decode(file_get_contents(__DIR__ . '/../composer.lock'), true);
foreach ($lock['packages'] as $p) {
    if (strpos($p['name'], 'phpspreadsheet') !== false) {
        echo $p['name'] . ': ' . $p['version'] . "\n";
    }
}
