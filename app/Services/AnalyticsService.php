<?php

namespace App\Services;

class AnalyticsService
{
    protected AnaliticaEvaluacionService $engine;

    public function __construct(?AnaliticaEvaluacionService $engine = null)
    {
        $this->engine = $engine ?? app(AnaliticaEvaluacionService::class);
    }

    /**
     * Obtiene la distribución de frecuencias (AD, A, B, C) por área y competencia para un bimestre.
     */
    public function getDistribucionBimestral(int $anoLectivoId, array $filtros)
    {
        return $this->engine->getDistribucionBimestral($anoLectivoId, $filtros);
    }

    /**
     * Agrupa y calcula porcentajes, generando la interpretación cualitativa.
     */
    public function procesarEstadisticas($resultados)
    {
        return $this->engine->procesarEstadisticas($resultados);
    }

    /**
     * Obtiene la comparativa entre N bimestres.
     */
    public function getComparativaInterbimestral(int $anoLectivoId, array $bimestresIds, array $filtros)
    {
        return $this->engine->getComparativaInterbimestral($anoLectivoId, $bimestresIds, $filtros);
    }

    /**
     * Motor de Reglas Cualitativo
     */
    public function generarInterpretacion($porcentajes)
    {
        return $this->engine->generarInterpretacion($porcentajes);
    }

    /**
     * Interpretación cualitativa para comparativas.
     */
    public function interpretarComparativa($varC, $varB, $varA, $varAD)
    {
        return $this->engine->interpretarComparativa($varC, $varB, $varA, $varAD);
    }
}
