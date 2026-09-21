<?php

namespace App\Services;

class AnaliticaEvaluacionService
{
    /**
     * Procesa las escalas de evaluación (C, B, A, AD).
     *
     * @param array $datos
     * @return array
     */
    public function procesarEscalas(array $datos): array
    {
        // TODO: Implementar la lógica para el procesamiento de escalas (C, B, A, AD)
        // Ejemplo de estructura de retorno esperado:
        // return [
        //     'C' => 0,
        //     'B' => 0,
        //     'A' => 0,
        //     'AD' => 0,
        // ];

        return [];
    }

    /**
     * Calcula la distribución de notas en base a una escala (AD, A, B, C)
     * devolviendo la cantidad y el porcentaje de cada una.
     *
     * @param array $notas
     * @return array
     */
    public function calcularDistribucionEscalas(array $notas): array
    {
        $total = count($notas);
        
        if ($total === 0) {
            return [];
        }

        $frecuencias = array_count_values($notas);
        $resultado = [];

        $escalas = ['AD', 'A', 'B', 'C'];

        foreach ($escalas as $escala) {
            if (isset($frecuencias[$escala])) {
                $cantidad = $frecuencias[$escala];
                
                $resultado[$escala] = [
                    'cantidad' => $cantidad,
                    'porcentaje' => $this->calcularPorcentaje($cantidad, $total)
                ];
            }
        }

        return $resultado;
    }

    /**
     * Calcula el porcentaje de una cantidad respecto a un total.
     *
     * @param int $cantidad
     * @param int $total
     * @return float
     */
    private function calcularPorcentaje(int $cantidad, int $total): float
    {
        if ($total === 0) {
            return 0.0;
        }

        return round(($cantidad / $total) * 100, 1);
    }
}
