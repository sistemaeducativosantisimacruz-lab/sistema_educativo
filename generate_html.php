<?php
$req = request();
$req->merge(["tab" => "docentes"]);
$ctrl = new App\Http\Controllers\Admin\RendimientoController();
$resp = $ctrl->index($req);
file_put_contents('test_output.html', $resp->render());
