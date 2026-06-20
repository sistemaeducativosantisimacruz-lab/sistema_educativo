<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Curso;
use Illuminate\Support\Facades\DB;

// We have old courses (activo=0) and new courses (activo=1)
$oldCourses = Curso::where('activo', false)->get();
$newCourses = Curso::where('activo', true)->get();

$mapping = [];
foreach ($oldCourses as $old) {
    // Find a new course that matches the name
    $new = $newCourses->first(function($c) use ($old) {
        // Normalize strings for comparison
        $n1 = strtoupper(trim(preg_replace('/[^A-Za-z0-9]/', '', $old->nombre)));
        $n2 = strtoupper(trim(preg_replace('/[^A-Za-z0-9]/', '', $c->nombre)));
        return str_contains($n1, $n2) || str_contains($n2, $n1);
    });
    if ($new) {
        $mapping[$old->id] = $new->id;
    }
}

// Add explicit mappings for things that might not match perfectly
// We'll output the mapping to be sure
echo "Mapping:\n";
foreach($mapping as $old => $new) {
    $o = Curso::find($old);
    $n = Curso::find($new);
    echo "{$o->nombre} (ID: {$old}) -> {$n->nombre} (ID: {$new})\n";
}
