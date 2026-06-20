<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Curso;

$courseIdToDelete = 4;
$course = Curso::find($courseIdToDelete);

if ($course) {
    try {
        // Delete assignments or constraints first
        DB::table('asignaciones_docente')->where('curso_id', $courseIdToDelete)->delete();
        DB::table('docentes')->where('curso_id', $courseIdToDelete)->update(['curso_id' => null]);
        // add more tables if necessary, like notas
        if (Schema::hasTable('notas')) {
            DB::table('notas')->where('curso_id', $courseIdToDelete)->delete();
        }
        
        $course->delete();
        echo "Deleted course: {$course->nombre}\n";
    } catch (\Exception $e) {
        echo "Failed to delete: " . $e->getMessage() . "\n";
    }
} else {
    echo "Course not found.\n";
}

// Rename the remaining courses
$renames = [
    5 => 'Ciencia y Tecnología',
    6 => 'Educación por el trabajo',
    7 => 'Desarrollo personal',
    14 => 'Tutoría',
];

foreach ($renames as $id => $newName) {
    $c = Curso::find($id);
    if ($c) {
        $oldName = $c->nombre;
        $c->nombre = $newName;
        $c->save();
        echo "Renamed: {$oldName} -> {$newName}\n";
    }
}
