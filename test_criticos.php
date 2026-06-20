<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$req = Illuminate\Http\Request::create('/admin/rendimiento/exportar-criticos', 'GET', [
    'todos_cursos' => 0, 
    'cursos_seleccionados' => [20, 22]
]);
$controller = new App\Http\Controllers\Admin\RendimientoController();
$resp = $controller->exportarCriticos($req);

if (is_object($resp)) {
    echo "Class: " . get_class($resp) . "\n";
    if (get_class($resp) === 'Symfony\Component\HttpFoundation\BinaryFileResponse') {
        echo "File: " . $resp->getFile()->getPathname() . "\n";
    }
} else {
    echo "Response is not an object.\n";
}
