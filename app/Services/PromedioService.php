<?php

namespace App\Services;

use App\Models\Bimestre;
use App\Models\Matricula;
use App\Models\Calificacion;
use App\Models\PromedioBimestral;
use Illuminate\Support\Facades\DB;

class PromedioService
{
    public function calcularPorBimestre(Bimestre $bimestre)
    {
        // Esta es la lógica principal para calcular los promedios al cerrar un bimestre.
        // Se ejecuta en segundo plano o de forma síncrona según el volumen de datos.
        
        $anoLectivoId = $bimestre->ano_lectivo_id;

        // Obtener todas las matrículas del año actual
        $matriculas = Matricula::where('ano_lectivo_id', $anoLectivoId)->get();

        foreach ($matriculas as $matricula) {
            // Por cada matrícula (estudiante + grado_seccion), buscar las calificaciones 
            // de ese estudiante en las sesiones correspondientes a este bimestre.

            // Since we've replaced calificaciones with notas_bimestrales, and lists of checks are no longer tied
            // directly to the final grade if we use SIAGIE import, let's adjust this query.
            // If the user wants to calculate averages from notas_bimestrales directly:
            $calificaciones = DB::table('notas_bimestrales')
                ->where('estudiante_id', $matricula->estudiante_id)
                ->where('bimestre_id', $bimestre->id)
                ->select(
                    'competencia_id',
                    'nota as calificacion_letra'
                )
                ->get();

            // Agrupar calificaciones por competencia
            $porCompetencia = $calificaciones->groupBy('competencia_id');

            foreach ($porCompetencia as $competenciaId => $notas) {
                // Convertir letras a números para promediar
                $suma = 0;
                $cantidad = 0;

                foreach ($notas as $nota) {
                    $valor = $this->convertirLetraANumero($nota->calificacion_letra);
                    if ($valor !== null) {
                        $suma += $valor;
                        $cantidad++;
                    }
                }

                if ($cantidad > 0) {
                    $promedioNum = $suma / $cantidad;
                    $promedioLetra = $this->convertirNumeroALetra($promedioNum);

                    // Guardar o actualizar el promedio bimestral
                    PromedioBimestral::updateOrCreate(
                        [
                            'matricula_id' => $matricula->id,
                            'competencia_id' => $competenciaId,
                            'bimestre_id' => $bimestre->id,
                        ],
                        [
                            'promedio_numero' => $promedioNum,
                            'promedio_letra' => $promedioLetra,
                            'calculado_en' => now(),
                        ]
                    );
                }
            }
        }
    }

    private function convertirLetraANumero(?string $letra): ?int
    {
        return match ($letra) {
            'AD' => 20,
            'A' => 17,
            'B' => 13,
            'C' => 10,
            default => null,
        };
    }

    private function convertirNumeroALetra(float $numero): string
    {
        if ($numero >= 17.5) return 'AD';
        if ($numero >= 13.5) return 'A';
        if ($numero >= 10.5) return 'B';
        return 'C';
    }
}
