<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$estudiante = \App\Models\Estudiante::first();
$controller = app()->make(\App\Http\Controllers\Admin\EstudianteController::class);

$request = \Illuminate\Http\Request::create('/dummy', 'PUT', [
    'dni' => $estudiante->dni,
    'codigo_estudiante' => 'TEST001',
    'nombres' => $estudiante->nombres,
    'apellido_paterno' => $estudiante->apellido_paterno,
    'apellido_materno' => $estudiante->apellido_materno,
    'fecha_nacimiento' => $estudiante->fecha_nacimiento->format('Y-m-d'),
    'sexo' => $estudiante->sexo,
]);

try {
    $controller->update($request, $estudiante);
    echo "Exito. Codigo ahora: " . $estudiante->fresh()->codigo_estudiante;
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
