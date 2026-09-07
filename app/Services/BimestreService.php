<?php

namespace App\Services;

use App\Models\Bimestre;
use App\Models\AnoLectivo;
use App\Models\GradoSeccion;
use App\Models\NotaBimestral;

class BimestreService
{
    /**
     * Genera un reporte de auditoría sobre el estado de calificaciones 
     * antes de proceder con el cierre de un bimestre.
     *
     * @param Bimestre $bimestre
     * @param AnoLectivo $anoActivo
     * @return array
     */
    public function generarAuditoriaDeCierre(Bimestre $bimestre, AnoLectivo $anoActivo): array
    {
        $secciones = GradoSeccion::with(['grado', 'seccion'])
            ->where('ano_lectivo_id', $anoActivo->id)
            ->get();

        $resumen = [];

        foreach ($secciones as $seccion) {
            $estudianteIds = $seccion->matriculas()
                ->where('estado', 'matriculado')
                ->pluck('estudiante_id')
                ->toArray();

            $totalMatriculados = count($estudianteIds);

            if ($totalMatriculados === 0) {
                continue;
            }

            $estudiantesConNotasCount = NotaBimestral::where('bimestre_id', $bimestre->id)
                ->whereIn('estudiante_id', $estudianteIds)
                ->distinct('estudiante_id')
                ->count('estudiante_id');

            $sinNotasCount = $totalMatriculados - $estudiantesConNotasCount;

            $resumen[] = [
                'seccion_nombre' => ($seccion->grado->nombre ?? '') . ' - ' . ($seccion->seccion->nombre ?? ''),
                'total_estudiantes' => $totalMatriculados,
                'estudiantes_sin_notas' => $sinNotasCount,
            ];
        }

        return $resumen;
    }
}
