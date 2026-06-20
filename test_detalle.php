<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$gs = App\Models\GradoSeccion::find(30);
$ctrl = new App\Http\Controllers\Admin\GradoSeccionController();
$resp = $ctrl->detalle($gs);
echo json_encode($resp->getData());
