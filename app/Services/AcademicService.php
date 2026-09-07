<?php

namespace App\Services;

use App\Models\NotaBimestral;
use Illuminate\Support\Collection;

class AcademicService
{
    /**
     * Obtiene y agrupa las notas de un estudiante por curso, competencia y bimestre.
     * 
     * @param int $estudianteId
     * @param \Illuminate\Support\Collection|array $bimestresIds
     * @return array
     */
    public function getNotasAgrupadas(int $estudianteId, $bimestresIds): array
    {
        $notasRaw = NotaBimestral::where('estudiante_id', $estudianteId)
            ->whereIn('bimestre_id', $bimestresIds)
            ->get();

        $notasMap = [];
        foreach ($notasRaw as $nota) {
            $notasMap[$nota->curso_id][$nota->competencia_id][$nota->bimestre_id] = $nota;
        }

        return $notasMap;
    }
}
