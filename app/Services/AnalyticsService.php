<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Obtiene la distribución de frecuencias (AD, A, B, C) por área y competencia para un bimestre.
     */
    public function getDistribucionBimestral(int $anoLectivoId, array $filtros)
    {
        $query = DB::table('notas_bimestrales as nb')
            ->join('matriculas as m', 'nb.estudiante_id', '=', 'm.estudiante_id')
            ->join('cursos as c', 'nb.curso_id', '=', 'c.id')
            ->join('competencias as comp', 'nb.competencia_id', '=', 'comp.id')
            ->where('m.ano_lectivo_id', $anoLectivoId)
            ->where('nb.bimestre_id', $filtros['bimestre_id'])
            ->where('m.estado', '!=', 'retirado')
            ->whereNotNull('nb.nota');

        if (!empty($filtros['nivel'])) {
            $query->join('grado_secciones as gs', 'm.grado_seccion_id', '=', 'gs.id')
                  ->join('grados as g', 'gs.grado_id', '=', 'g.id')
                  ->where('g.nivel', $filtros['nivel']);
        }

        if (!empty($filtros['grado_seccion_id'])) {
            $query->where('m.grado_seccion_id', $filtros['grado_seccion_id']);
        }

        $resultados = $query->select(
                'c.id as curso_id',
                'c.nombre as area',
                'comp.id as competencia_id',
                'comp.nombre as competencia',
                'nb.nota as nivel_logro',
                DB::raw('COUNT(*) as cantidad')
            )
            ->groupBy('c.id', 'c.nombre', 'comp.id', 'comp.nombre', 'nb.nota')
            ->orderBy('c.nombre')
            ->orderBy('comp.orden')
            ->get();

        return $this->procesarEstadisticas($resultados);
    }

    /**
     * Agrupa y calcula porcentajes, generando la interpretación cualitativa.
     */
    private function procesarEstadisticas($resultados)
    {
        $areas = [];

        foreach ($resultados as $row) {
            $cId = $row->curso_id;
            $comp = $row->competencia;

            if (!isset($areas[$cId])) {
                $areas[$cId] = [
                    'nombre' => $row->area,
                    'competencias' => []
                ];
            }

            if (!isset($areas[$cId]['competencias'][$comp])) {
                $areas[$cId]['competencias'][$comp] = [
                    'AD' => 0, 'A' => 0, 'B' => 0, 'C' => 0, 'total' => 0
                ];
            }

            $nota = $row->nivel_logro;
            if (in_array($nota, ['AD', 'A', 'B', 'C'])) {
                $areas[$cId]['competencias'][$comp][$nota] += $row->cantidad;
                $areas[$cId]['competencias'][$comp]['total'] += $row->cantidad;
            }
        }

        // Calcular porcentajes y generar interpretación
        foreach ($areas as &$area) {
            foreach ($area['competencias'] as $compNombre => &$data) {
                $total = $data['total'];
                if ($total > 0) {
                    $data['porcentajes'] = [
                        'AD' => round(($data['AD'] / $total) * 100, 1),
                        'A'  => round(($data['A'] / $total) * 100, 1),
                        'B'  => round(($data['B'] / $total) * 100, 1),
                        'C'  => round(($data['C'] / $total) * 100, 1),
                    ];
                } else {
                    $data['porcentajes'] = ['AD' => 0, 'A' => 0, 'B' => 0, 'C' => 0];
                }

                $data['interpretacion'] = $this->generarInterpretacion($data['porcentajes']);
            }
        }

        return $areas;
    }

    /**
     * Motor de Reglas Qualitativo
     */
    private function generarInterpretacion($porcentajes)
    {
        $riesgo = $porcentajes['C'] + $porcentajes['B'];
        $logro = $porcentajes['A'] + $porcentajes['AD'];

        if ($riesgo >= 50) {
            return "Alerta: El " . $riesgo . "% de los estudiantes se encuentra en nivel de Inicio o Proceso. Se requiere intervención pedagógica urgente y reforzamiento.";
        } elseif ($riesgo >= 30) {
            return "Atención: Un grupo significativo (" . $riesgo . "%) aún está en proceso de alcanzar los aprendizajes esperados.";
        } elseif ($logro >= 80) {
            return "Destacado: Excelente desempeño. El " . $logro . "% de los estudiantes ha consolidado o superado el logro esperado.";
        } elseif ($logro >= 50) {
            return "Favorable: La mayoría (" . $logro . "%) ha alcanzado el logro esperado, indicando una buena asimilación de las competencias.";
        }

        return "Distribución heterogénea: Existen retos focalizados, aunque una parte del grupo está logrando los objetivos.";
    }

    /**
     * Obtiene la comparativa entre 2 bimestres.
     */
    public function getComparativaInterbimestral(int $anoLectivoId, array $bimestresIds, array $filtros)
    {
        if (count($bimestresIds) !== 2) {
            throw new \Exception("La comparativa debe realizarse exactamente entre 2 bimestres.");
        }

        $b1 = $bimestresIds[0];
        $b2 = $bimestresIds[1];

        // Reutilizamos el método base para cada bimestre
        $filtrosB1 = $filtros; $filtrosB1['bimestre_id'] = $b1;
        $dataB1 = $this->getDistribucionBimestral($anoLectivoId, $filtrosB1);

        $filtrosB2 = $filtros; $filtrosB2['bimestre_id'] = $b2;
        $dataB2 = $this->getDistribucionBimestral($anoLectivoId, $filtrosB2);

        $comparativa = [];

        foreach ($dataB1 as $cId => $areaB1) {
            if (!isset($dataB2[$cId])) continue; // Solo comparar áreas que existan en ambos

            $areaNombre = $areaB1['nombre'];
            $comparativa[$cId] = [
                'nombre' => $areaNombre,
                'competencias' => []
            ];

            foreach ($areaB1['competencias'] as $compNombre => $compB1) {
                if (!isset($dataB2[$cId]['competencias'][$compNombre])) continue;

                $compB2 = $dataB2[$cId]['competencias'][$compNombre];

                // Calculamos variaciones
                $variacionC = $compB2['porcentajes']['C'] - $compB1['porcentajes']['C'];
                $variacionB = $compB2['porcentajes']['B'] - $compB1['porcentajes']['B'];
                $variacionA = $compB2['porcentajes']['A'] - $compB1['porcentajes']['A'];
                $variacionAD = $compB2['porcentajes']['AD'] - $compB1['porcentajes']['AD'];

                $comparativa[$cId]['competencias'][$compNombre] = [
                    'b1' => $compB1,
                    'b2' => $compB2,
                    'variaciones' => [
                        'C' => round($variacionC, 1),
                        'B' => round($variacionB, 1),
                        'A' => round($variacionA, 1),
                        'AD' => round($variacionAD, 1),
                    ],
                    'interpretacion_comparativa' => $this->interpretarComparativa($variacionC, $variacionB, $variacionA, $variacionAD)
                ];
            }
        }

        return $comparativa;
    }

    private function interpretarComparativa($varC, $varB, $varA, $varAD)
    {
        $mejora = $varA + $varAD;
        $empeoramiento = $varC + $varB;

        if ($mejora > 10 && $empeoramiento < 0) {
            return "Evolución muy positiva: Se observa un traslado significativo de estudiantes hacia niveles de logro, reduciendo el rezago.";
        } elseif ($mejora > 0 && $empeoramiento <= 0) {
            return "Tendencia favorable: Leve incremento en el logro esperado.";
        } elseif ($empeoramiento > 10) {
            return "Alerta de retroceso: Han aumentado los estudiantes en niveles de inicio y proceso (+{$empeoramiento}%).";
        } elseif ($empeoramiento > 0) {
            return "Estancamiento o leve descenso en el rendimiento general.";
        }

        return "Rendimiento estable sin variaciones significativas entre periodos.";
    }
}
