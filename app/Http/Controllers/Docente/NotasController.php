<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GradoSeccion;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\AnoLectivo;
use App\Models\NotaBimestral;
use App\Models\Bimestre;
use App\Models\Curso;
use Illuminate\Support\Facades\Auth;

class NotasController extends Controller
{
    public function estudiantes(Request $request, $grado_seccion_id)
    {
        $docente = Auth::user()->docente;
        $anoActivo = AnoLectivo::where('activo', true)->first();

        if (!$anoActivo) {
            return redirect()->route('docente.dashboard')->with('error', 'No hay año lectivo activo.');
        }

        $gradoSeccion = GradoSeccion::with('grado', 'seccion')->findOrFail($grado_seccion_id);

        // Verify the teacher has access to this section
        $esTutor = $gradoSeccion->tutor_id === $docente->id;
        
        $tieneAcceso = $docente->asignaciones()
            ->where('ano_lectivo_id', $anoActivo->id)
            ->where('grado_seccion_id', $gradoSeccion->id)
            ->exists();

        if (!$tieneAcceso && !$esTutor) {
            return redirect()->route('docente.dashboard')->with('error', 'No tienes asignada esta sección.');
        }

        // Get students enrolled in this section for the active year
        $query = Matricula::with('estudiante')
            ->where('grado_seccion_id', $gradoSeccion->id)
            ->where('ano_lectivo_id', $anoActivo->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('estudiante', function($q) use ($search) {
                $q->where('nombres', 'ilike', "%{$search}%")
                  ->orWhere('apellido_paterno', 'ilike', "%{$search}%")
                  ->orWhere('apellido_materno', 'ilike', "%{$search}%")
                  ->orWhere('dni', 'ilike', "%{$search}%");
            });
        }

        $matriculas = $query->get()
            ->sortBy(function($m) {
                return $m->estudiante->apellido_paterno . ' ' . $m->estudiante->apellido_materno . ' ' . $m->estudiante->nombres;
            });

        return view('docente.seccion_estudiantes', compact('gradoSeccion', 'matriculas', 'anoActivo'));
    }

    public function verNotas(Request $request, $grado_seccion_id, $estudiante_id)
    {
        $docente = Auth::user()->docente;
        $anoActivo = AnoLectivo::where('activo', true)->first();

        if (!$anoActivo) {
            return redirect()->route('docente.dashboard')->with('error', 'No hay año lectivo activo.');
        }

        $gradoSeccion = GradoSeccion::with('grado', 'seccion')->findOrFail($grado_seccion_id);
        $estudiante = Estudiante::findOrFail($estudiante_id);

        // Verify access to the section
        $esTutor = $gradoSeccion->tutor_id === $docente->id;
        
        $tieneAcceso = $docente->asignaciones()
            ->where('ano_lectivo_id', $anoActivo->id)
            ->where('grado_seccion_id', $gradoSeccion->id)
            ->exists();

        if (!$tieneAcceso && !$esTutor) {
            return redirect()->route('docente.dashboard')->with('error', 'No tienes acceso a esta sección.');
        }

        // Determine which courses the teacher is assigned to for this specific section
        $cursosAsignadosIds = collect();
        if (!$esTutor) {
            $cursosAsignadosIds = $docente->asignaciones()
                ->where('ano_lectivo_id', $anoActivo->id)
                ->where('grado_seccion_id', $gradoSeccion->id)
                ->pluck('curso_id');
        }
            
        // If the teacher is a tutor, load all courses. If not, load only assigned courses.
        if ($esTutor || $cursosAsignadosIds->isEmpty()) {
            $cursos = Curso::where('activo', true)
                           ->where(function($q) use ($gradoSeccion) {
                               $q->where('nivel', $gradoSeccion->grado->nivel)
                                 ->orWhere('nivel', 'ambos');
                           })
                           ->with(['competencias' => function($q) {
                               $q->orderBy('orden');
                           }])->get();
        } else {
            $cursos = Curso::where('activo', true)
                           ->whereIn('id', $cursosAsignadosIds)
                           ->with(['competencias' => function($q) {
                               $q->orderBy('orden');
                           }])->get();
        }

        $bimestres = Bimestre::where('ano_lectivo_id', $anoActivo->id)->orderBy('numero')->get();

        // Load all grades for this student and the active year
        // Optimize by fetching them in one query
        $notasRaw = NotaBimestral::where('estudiante_id', $estudiante->id)
                                ->whereIn('bimestre_id', $bimestres->pluck('id'))
                                ->get();

        // Group grades for easy access: notas[$curso_id][$competencia_id][$bimestre_id]
        $notas = [];
        foreach ($notasRaw as $nota) {
            $notas[$nota->curso_id][$nota->competencia_id][$nota->bimestre_id] = $nota;
        }

        return view('docente.notas_estudiante', compact('gradoSeccion', 'estudiante', 'cursos', 'bimestres', 'notas', 'anoActivo'));
    }
}
