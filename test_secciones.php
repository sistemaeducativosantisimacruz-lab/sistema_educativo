<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/admin/rendimiento/exportar-secciones', 'GET');
$controller = new App\Http\Controllers\Admin\RendimientoController();
$response = $controller->exportarSecciones($request);
echo get_class($response);
