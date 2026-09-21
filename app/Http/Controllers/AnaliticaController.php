<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Models\NotaBimestral;
use App\Services\AnaliticaEvaluacionService;

class AnaliticaController extends Controller
{
    /**
     * Devuelve la agregación real de notas bimestrales en JSON.
     * 
     * @param AnaliticaEvaluacionService $analiticaService
     * @return JsonResponse
     */
    public function distribucion(AnaliticaEvaluacionService $analiticaService): JsonResponse
    {
        // Se obtienen las notas reales de la base de datos
        // Asumimos que la columna es "nota" y contiene valores (AD, A, B, C)
        $notas = NotaBimestral::whereNotNull('nota')->pluck('nota')->toArray();

        // Utilizamos el servicio refactorizado para calcular la distribución
        $distribucion = $analiticaService->calcularDistribucionEscalas($notas);

        // Devolvemos el payload listo para consumirse en siguientes iteraciones
        return response()->json([
            'success' => true,
            'data' => [
                'total_notas' => count($notas),
                'distribucion' => $distribucion
            ]
        ]);
    }
}
