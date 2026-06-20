<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Directorio temporal en Vercel (único con permisos de escritura)
$appStorage = '/tmp/storage';

if (!is_dir($appStorage)) {
    mkdir($appStorage, 0777, true);
    mkdir($appStorage . '/framework/cache/data', 0777, true);
    mkdir($appStorage . '/framework/views', 0777, true);
    mkdir($appStorage . '/framework/sessions', 0777, true);
    mkdir($appStorage . '/logs', 0777, true);
}

// Configurar variables de entorno por seguridad
$_ENV['VIEW_COMPILED_PATH'] = $appStorage . '/framework/views';
putenv('VIEW_COMPILED_PATH=' . $appStorage . '/framework/views');

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

// Redirigir el almacenamiento a /tmp
$app->useStoragePath($appStorage);

$app->handleRequest(Request::capture());
