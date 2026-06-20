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

// Forzar URL dinámica basada en el dominio actual que visita el usuario
// Esto evita problemas de CORS cuando se usan los distintos dominios de Vercel
if (isset($_SERVER['HTTP_HOST'])) {
    $currentUrl = 'https://' . $_SERVER['HTTP_HOST'];
    $_ENV['APP_URL'] = $currentUrl;
    $_SERVER['APP_URL'] = $currentUrl;
    putenv('APP_URL=' . $currentUrl);
    
    $_ENV['ASSET_URL'] = $currentUrl;
    $_SERVER['ASSET_URL'] = $currentUrl;
    putenv('ASSET_URL=' . $currentUrl);
}

// Arreglar variables de servidor para que Laravel no añada /api/index.php a las URLs
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../public';

// Ignorar la caché generada en el entorno de build (que tiene rutas estáticas /vercel/path0)
$_SERVER['APP_SERVICES_CACHE'] = '/tmp/storage/bootstrap/cache/services.php';
$_SERVER['APP_PACKAGES_CACHE'] = '/tmp/storage/bootstrap/cache/packages.php';
$_SERVER['APP_CONFIG_CACHE'] = '/tmp/storage/bootstrap/cache/config.php';
$_SERVER['APP_ROUTES_CACHE'] = '/tmp/storage/bootstrap/cache/routes-v7.php';

if (!is_dir('/tmp/storage/bootstrap/cache')) {
    mkdir('/tmp/storage/bootstrap/cache', 0777, true);
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

// Redirigir el almacenamiento a /tmp
$app->useStoragePath($appStorage);

try {
    $app->handleRequest(Request::capture());
} catch (\Throwable $e) {
    echo "<h1>Error en la aplicación:</h1>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    
    // Leer el log original de Laravel para ver el verdadero error
    $logFile = $appStorage . '/logs/laravel.log';
    if (file_exists($logFile)) {
        echo "<h2>Contenido de laravel.log (Error Original):</h2>";
        echo "<pre>" . htmlspecialchars(file_get_contents($logFile)) . "</pre>";
    } else {
        echo "<h2>No se encontró laravel.log</h2>";
    }

    echo "<h2>Stack Trace:</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
