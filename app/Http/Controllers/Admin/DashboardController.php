<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentAnoLectivo = \App\Models\AnoLectivo::where('activo', true)->first();
        
        $totalEstudiantesActivos = 0;
        $totalSecciones = 0;

        if ($currentAnoLectivo) {
            $totalEstudiantesActivos = \App\Models\Matricula::where('ano_lectivo_id', $currentAnoLectivo->id)->count();
            
            $totalSecciones = \App\Models\GradoSeccion::where('ano_lectivo_id', $currentAnoLectivo->id)
                ->where('activo', true)
                ->count();
        } else {
            $totalEstudiantesActivos = \App\Models\Estudiante::where('estado', 'Activo')->count();
        }

        $totalDocentes = \App\Models\Docente::count();

        // Estado de Mensualidades (Al día %) para el mes seleccionado o actual
        $currentMonth = $request->input('mes', (int) date('n'));
        $currentYear = $request->input('anio', (int) date('Y'));

        $totalMensualidadesMes = \App\Models\Mensualidad::where('mes', $currentMonth)
            ->where('anio', $currentYear)
            ->count();
            
        $mensualidadesAlDia = \App\Models\Mensualidad::where('mes', $currentMonth)
            ->where('anio', $currentYear)
            ->whereIn('estado', ['PAGÓ', 'EXONERADO', 'BENEFICIADO'])
            ->count();
            
        $porcentajeAlDia = $totalMensualidadesMes > 0 
            ? round(($mensualidadesAlDia / $totalMensualidadesMes) * 100) 
            : 0;

        $meses = \App\Models\Mensualidad::meses();

        // --- Novedades: Estado del Bimestre y Progreso de Notas ---
        $currentBimestre = null;
        $diasFaltantesBimestre = 0;
        $porcentajeNotas = 0;
        $docentesConNotas = 0;
        $totalDocentesAsignados = 0;

        if ($currentAnoLectivo) {
            $currentBimestre = \App\Models\Bimestre::where('ano_lectivo_id', $currentAnoLectivo->id)
                ->where('estado', 'abierto')
                ->whereDate('fecha_inicio', '<=', now())
                ->orderBy('numero')
                ->first();
                
            if (!$currentBimestre) {
                $currentBimestre = \App\Models\Bimestre::where('ano_lectivo_id', $currentAnoLectivo->id)
                    ->where('estado', 'abierto')
                    ->first();
            }

            if ($currentBimestre) {
                $fechaFin = \Carbon\Carbon::parse($currentBimestre->fecha_fin);
                $diasFaltantesBimestre = now()->lt($fechaFin) ? (int) ceil(now()->floatDiffInDays($fechaFin)) : 0;
                
                $totalDocentesAsignados = \App\Models\AsignacionDocente::where('ano_lectivo_id', $currentAnoLectivo->id)
                    ->where('activo', true)
                    ->distinct('docente_id')
                    ->count('docente_id');

                if ($totalDocentesAsignados > 0) {
                    $cursosConNotas = \App\Models\NotaBimestral::where('bimestre_id', $currentBimestre->id)
                        ->distinct('curso_id')
                        ->pluck('curso_id');
                        
                    $docentesConNotas = \App\Models\AsignacionDocente::where('ano_lectivo_id', $currentAnoLectivo->id)
                        ->where('activo', true)
                        ->whereIn('curso_id', $cursosConNotas)
                        ->distinct('docente_id')
                        ->count('docente_id');
                        
                    $polidocentes = \App\Models\AsignacionDocente::where('ano_lectivo_id', $currentAnoLectivo->id)
                        ->where('activo', true)
                        ->whereNull('curso_id')
                        ->get();
                        
                    foreach($polidocentes as $poli) {
                        $tieneNotas = \App\Models\Matricula::where('grado_seccion_id', $poli->grado_seccion_id)
                            ->join('notas_bimestrales', 'matriculas.estudiante_id', '=', 'notas_bimestrales.estudiante_id')
                            ->where('notas_bimestrales.bimestre_id', $currentBimestre->id)
                            ->exists();
                        if ($tieneNotas) {
                            $docentesConNotas++;
                        }
                    }
                    
                    $docentesConNotas = min($docentesConNotas, $totalDocentesAsignados);
                    $porcentajeNotas = round(($docentesConNotas / $totalDocentesAsignados) * 100);
                }
            }
        }

        return view('admin.dashboard', compact(
            'totalEstudiantesActivos',
            'totalDocentes',
            'totalSecciones',
            'porcentajeAlDia',
            'currentMonth',
            'currentYear',
            'meses',
            'currentBimestre',
            'diasFaltantesBimestre',
            'porcentajeNotas',
            'docentesConNotas',
            'totalDocentesAsignados'
        ));
    }
}
