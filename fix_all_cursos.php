<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AsignacionDocente;
use App\Models\Docente;

$asignaciones = AsignacionDocente::whereNotNull('curso_id')->get();
$count = 0;

foreach ($asignaciones as $asignacion) {
    $docente = Docente::find($asignacion->docente_id);
    if ($docente) {
        $docente->cursos()->syncWithoutDetaching([$asignacion->curso_id]);
        $count++;
    }
}

echo "Se sincronizaron {$count} cursos desde asignaciones_docente hacia docente_cursos.\n";
