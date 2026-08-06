<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$m = \App\Models\Matricula::with('estudiante')->first();
echo json_encode($m->estudiante);
