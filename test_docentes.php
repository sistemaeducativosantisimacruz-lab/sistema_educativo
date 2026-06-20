<?php
$req = request();
$req->merge(["tab" => "docentes"]);
$ctrl = new App\Http\Controllers\Admin\RendimientoController();
$resp = $ctrl->index($req);
echo "DOCENTES_COUNT=" . $resp->getData()["docentesReport"]->count();
