<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Curso;
use Illuminate\Support\Facades\DB;

$mapping = [
    3 => 22,
    5 => 20,
    7 => 21,
    8 => 23,
    9 => 28,
    10 => 27,
    11 => 24,
    12 => 18,
    13 => 26,
    15 => 19,
    17 => 30,
    6 => 25,
];

DB::transaction(function() use ($mapping) {
    foreach($mapping as $old => $new) {
        DB::table('docente_cursos')->where('curso_id', $old)->update(['curso_id' => $new]);
        DB::table('asignaciones_docente')->where('curso_id', $old)->update(['curso_id' => $new]);
        try { DB::table('notas')->where('curso_id', $old)->update(['curso_id' => $new]); } catch(\Exception $e) {}
        try { DB::table('competencias')->where('curso_id', $old)->update(['curso_id' => $new]); } catch(\Exception $e) {}
    }

    // Reactivate Tutoría
    Curso::where('id', 14)->update(['activo' => true]);
});

echo "Done\n";
