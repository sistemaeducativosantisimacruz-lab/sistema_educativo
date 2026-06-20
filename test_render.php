<?php
$req = request();
$req->merge(["tab" => "docentes"]);
$ctrl = new App\Http\Controllers\Admin\RendimientoController();
$resp = $ctrl->index($req);
$output = $resp->render();
if (strpos($output, 'No hay docentes registrados') !== false) {
    echo "NO_DOCENTES\n";
} else {
    echo "DOCENTES_RENDERED\n";
}
