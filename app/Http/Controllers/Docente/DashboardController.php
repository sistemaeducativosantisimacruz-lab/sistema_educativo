<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AnoLectivo;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $docente = Auth::user()->docente;
        return view('docente.dashboard', compact('docente'));
    }

    public function secciones()
    {
        $docente = Auth::user()->docente;
        $anoActivo = AnoLectivo::where('activo', true)->first();
        
        $seccionesAsignadas = collect();
        if ($docente && $anoActivo) {
            $asignaciones = $docente->asignaciones()
                                    ->where('ano_lectivo_id', $anoActivo->id)
                                    ->with('gradoSeccion.grado', 'gradoSeccion.seccion')
                                    ->get();
            
            // Extract unique GradoSeccion objects
            $seccionesAsignadas = $asignaciones->pluck('gradoSeccion')->unique('id');
            
            // Add sections where the teacher is a tutor
            $seccionesTutor = \App\Models\GradoSeccion::where('tutor_id', $docente->id)
                                ->where('ano_lectivo_id', $anoActivo->id)
                                ->with('grado', 'seccion')
                                ->get();
                                
            $seccionesAsignadas = $seccionesAsignadas->merge($seccionesTutor)->unique('id');

            // Sort logic: Tutor first, then by grade and section numerically/alphabetically
            $seccionesAsignadas = $seccionesAsignadas->sortBy(function($gs) use ($docente) {
                $isTutor = $gs->tutor_id === $docente->id ? 0 : 1;
                $ordenStr = sprintf('%04d', $gs->grado->orden);
                return $isTutor . '-' . $ordenStr . '-' . $gs->seccion->nombre;
            });
        }
        
        return view('docente.secciones', compact('seccionesAsignadas', 'anoActivo'));
    }
}
