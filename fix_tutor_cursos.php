<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\GradoSeccion;
use App\Models\Curso;
use App\Models\Docente;

// Obtener el curso de tutoría
$cursoTutoriaSec = Curso::where('nombre', 'Tutoría')->orWhere('nombre', 'like', '%tutoría%')->first();
if (!$cursoTutoriaSec) {
    echo "Curso de Tutoría no encontrado.\n";
    exit;
}

$gradoSecciones = GradoSeccion::whereNotNull('tutor_id')->get();
$count = 0;

foreach ($gradoSecciones as $gs) {
    $tutor = Docente::find($gs->tutor_id);
    if ($tutor) {
        $tutor->cursos()->syncWithoutDetaching([$cursoTutoriaSec->id]);
        $count++;
    }
}

echo "Se sincronizó el curso de Tutoría para {$count} tutores.\n";
