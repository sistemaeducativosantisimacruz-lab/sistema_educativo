<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Services\DiagnosticoService;

class AnaliticaEvaluacionService
{
    protected DiagnosticoService $diagnosticoService;

    public function __construct(?DiagnosticoService $diagnosticoService = null)
    {
        $this->diagnosticoService = $diagnosticoService ?? app(DiagnosticoService::class);
    }
    /**
     * Procesa las escalas de evaluación (C, B, A, AD).
     *
     * @param array $datos
     * @return array
     */
    public function procesarEscalas(array $datos): array
    {
        return $this->calcularDistribucionEscalas($datos);
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
     * Obtiene la distribución de frecuencias (AD, A, B, C) por área y competencia para un bimestre.
     * Consulta agrupada optimizada para evitar problemas de N+1.
     *
     * @param int $anoLectivoId
     * @param array $filtros
     * @return array
     */
    public function getDistribucionBimestral(int $anoLectivoId, array $filtros): array
    {
        if (empty($filtros['bimestre_id'])) {
            return [];
        }

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

        if (!empty($filtros['curso_id'])) {
            $query->where('c.id', $filtros['curso_id']);
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
     * Agrupa y calcula frecuencias y porcentajes por competencia y área,
     * incorporando la interpretación cualitativa de cada una.
     *
     * @param iterable $resultados
     * @return array
     */
    public function procesarEstadisticas(iterable $resultados): array
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
                        'AD' => $this->calcularPorcentaje($data['AD'], $total),
                        'A'  => $this->calcularPorcentaje($data['A'], $total),
                        'B'  => $this->calcularPorcentaje($data['B'], $total),
                        'C'  => $this->calcularPorcentaje($data['C'], $total),
                    ];
                } else {
                    $data['porcentajes'] = ['AD' => 0.0, 'A' => 0.0, 'B' => 0.0, 'C' => 0.0];
                }

                $data['interpretacion'] = $this->generarInterpretacion($data['porcentajes']);
                $data['alerta_riesgo'] = $this->superaUmbralAlerta($data['porcentajes']);
            }
            unset($data);
        }
        unset($area);

        return $areas;
    }

    /**
     * Calcula el consolidado global de las cuatro tarjetas resumen:
     * C (En Inicio), B (En Proceso), A (Logro Esperado), AD (Logro Destacado).
     *
     * @param array $areas
     * @return array
     */
    public function calcularResumenGlobal(array $areas): array
    {
        $conteo = ['AD' => 0, 'A' => 0, 'B' => 0, 'C' => 0];
        $totalEvaluaciones = 0;

        foreach ($areas as $area) {
            foreach ($area['competencias'] as $comp) {
                foreach (['AD', 'A', 'B', 'C'] as $escala) {
                    $conteo[$escala] += $comp[$escala] ?? 0;
                }
                $totalEvaluaciones += $comp['total'] ?? 0;
            }
        }

        $resumen = [
            'total' => $totalEvaluaciones,
            'AD' => [
                'cantidad' => $conteo['AD'],
                'porcentaje' => $this->calcularPorcentaje($conteo['AD'], $totalEvaluaciones),
            ],
            'A' => [
                'cantidad' => $conteo['A'],
                'porcentaje' => $this->calcularPorcentaje($conteo['A'], $totalEvaluaciones),
            ],
            'B' => [
                'cantidad' => $conteo['B'],
                'porcentaje' => $this->calcularPorcentaje($conteo['B'], $totalEvaluaciones),
            ],
            'C' => [
                'cantidad' => $conteo['C'],
                'porcentaje' => $this->calcularPorcentaje($conteo['C'], $totalEvaluaciones),
            ],
        ];

        return $resumen;
    }

    /**
     * Determina si la suma de porcentajes de alumnos en niveles de inicio (C) y proceso (B)
     * supera el umbral de alerta configurado (por defecto 40%).
     *
     * @param array $porcentajes
     * @param float|null $umbral
     * @return bool
     */
    public function superaUmbralAlerta(array $porcentajes, ?float $umbral = null): bool
    {
        $umbral = $umbral ?? (float) config('analitica.umbral_alerta_riesgo', 40.0);
        $riesgo = ($porcentajes['C'] ?? 0.0) + ($porcentajes['B'] ?? 0.0);

        return $riesgo > $umbral;
    }

    /**
     * Obtiene la comparativa interbimestral entre N bimestres.
     *
     * @param int $anoLectivoId
     * @param array $bimestresIds
     * @param array $filtros
     * @return array
     * @throws \Exception
     */
    public function getComparativaInterbimestral(int $anoLectivoId, array $bimestresIds, array $filtros): array
    {
        if (count($bimestresIds) < 2) {
            throw new \Exception("La comparativa debe realizarse al menos entre 2 bimestres.");
        }

        $datosPorBimestre = [];
        foreach ($bimestresIds as $bId) {
            $filtrosBim = $filtros;
            $filtrosBim['bimestre_id'] = $bId;
            $datosPorBimestre[$bId] = $this->getDistribucionBimestral($anoLectivoId, $filtrosBim);
        }

        $comparativa = [];
        $primerBimestre = $bimestresIds[0];
        $ultimoBimestre = end($bimestresIds);
        
        $dataPrimerBimestre = $datosPorBimestre[$primerBimestre];
        $dataUltimoBimestre = $datosPorBimestre[$ultimoBimestre];

        foreach ($dataPrimerBimestre as $cId => $areaInicial) {
            $areaValida = true;
            foreach ($bimestresIds as $bId) {
                if (!isset($datosPorBimestre[$bId][$cId])) {
                    $areaValida = false;
                    break;
                }
            }
            if (!$areaValida) continue;

            $areaNombre = $areaInicial['nombre'];
            $comparativa[$cId] = [
                'nombre' => $areaNombre,
                'competencias' => []
            ];

            foreach ($areaInicial['competencias'] as $compNombre => $compInicial) {
                $compValida = true;
                foreach ($bimestresIds as $bId) {
                    if (!isset($datosPorBimestre[$bId][$cId]['competencias'][$compNombre])) {
                        $compValida = false;
                        break;
                    }
                }
                if (!$compValida) continue;

                $dataCompN = [];
                foreach ($bimestresIds as $bId) {
                    $dataCompN[$bId] = $datosPorBimestre[$bId][$cId]['competencias'][$compNombre];
                }

                $compFinal = $datosPorBimestre[$ultimoBimestre][$cId]['competencias'][$compNombre];

                // Calculamos variaciones (Último - Primero)
                $variacionC = $compFinal['porcentajes']['C'] - $compInicial['porcentajes']['C'];
                $variacionB = $compFinal['porcentajes']['B'] - $compInicial['porcentajes']['B'];
                $variacionA = $compFinal['porcentajes']['A'] - $compInicial['porcentajes']['A'];
                $variacionAD = $compFinal['porcentajes']['AD'] - $compInicial['porcentajes']['AD'];

                $comparativa[$cId]['competencias'][$compNombre] = [
                    'bimestres_data' => $dataCompN,
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

    /**
     * Motor de Reglas Cualitativo.
     *
     * @param array $porcentajes
     * @return string
     */
    public function generarInterpretacion(array $porcentajes): string
    {
        $riesgo = ($porcentajes['C'] ?? 0.0) + ($porcentajes['B'] ?? 0.0);
        $logro = ($porcentajes['A'] ?? 0.0) + ($porcentajes['AD'] ?? 0.0);

        $umbralUrgente = config('analitica.diagnostico.alerta_urgente', 50.0);
        $umbralAtencion = config('analitica.diagnostico.atencion_focalizada', 30.0);
        $umbralDestacado = config('analitica.diagnostico.logro_destacado', 80.0);
        $umbralFavorable = config('analitica.diagnostico.logro_favorable', 50.0);

        if ($riesgo >= $umbralUrgente) {
            return "Alerta: El " . $riesgo . "% de los estudiantes se encuentra en nivel de Inicio o Proceso. Se requiere intervención pedagógica urgente y reforzamiento.";
        } elseif ($riesgo >= $umbralAtencion) {
            return "Atención: Un grupo significativo (" . $riesgo . "%) aún está en proceso de alcanzar los aprendizajes esperados.";
        } elseif ($logro >= $umbralDestacado) {
            return "Destacado: Excelente desempeño. El " . $logro . "% de los estudiantes ha consolidado o superado el logro esperado.";
        } elseif ($logro >= $umbralFavorable) {
            return "Favorable: La mayoría (" . $logro . "%) ha alcanzado el logro esperado, indicando una buena asimilación de las competencias.";
        }

        return "Distribución heterogénea: Existen retos focalizados, aunque una parte del grupo está logrando los objetivos.";
    }

    /**
     * Interpretación cualitativa para comparativas.
     *
     * @param float $varC
     * @param float $varB
     * @param float $varA
     * @param float $varAD
     * @return string
     */
    public function interpretarComparativa(float $varC, float $varB, float $varA, float $varAD): string
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

    /**
     * Calcula el porcentaje de una cantidad respecto a un total.
     *
     * @param int $cantidad
     * @param int $total
     * @return float
     */
    public function calcularPorcentaje(int $cantidad, int $total): float
    {
        if ($total === 0) {
            return 0.0;
        }

        return round(($cantidad / $total) * 100, 1);
    }

    /**
     * Obtiene la lista de estudiantes matriculados en una sección y año lectivo (excluyendo retirados),
     * con su nombre formateado y su nivel global (AD/A/B/C) para el bimestre y curso especificado.
     * Si no tiene notas, nivel_global es null.
     * Consulta agregada en una sola llamada SQL (sin N+1, compatible con PostgreSQL y SQLite, sin CONCAT).
     *
     * @param int $anoLectivoId
     * @param int $gradoSeccionId
     * @param int $bimestreId
     * @param int|null $cursoId
     * @return array
     */
    public function getEstudiantesSeccionConNivel(int $anoLectivoId, int $gradoSeccionId, int $bimestreId, ?int $cursoId = null): array
    {
        $query = DB::table('matriculas as m')
            ->join('estudiantes as e', 'm.estudiante_id', '=', 'e.id')
            ->leftJoin('notas_bimestrales as nb', function ($join) use ($bimestreId, $cursoId) {
                $join->on('nb.estudiante_id', '=', 'e.id')
                     ->where('nb.bimestre_id', '=', $bimestreId)
                     ->whereNotNull('nb.nota');
                if ($cursoId) {
                    $join->where('nb.curso_id', '=', $cursoId);
                }
            })
            ->where('m.ano_lectivo_id', $anoLectivoId)
            ->where('m.grado_seccion_id', $gradoSeccionId)
            ->where('m.estado', '!=', 'retirado');

        $rows = $query->select(
                'e.id',
                'e.apellido_paterno',
                'e.apellido_materno',
                'e.nombres',
                DB::raw('COUNT(nb.nota) as total_notas'),
                DB::raw("AVG(CASE nb.nota WHEN 'AD' THEN 20.0 WHEN 'A' THEN 17.0 WHEN 'B' THEN 13.0 WHEN 'C' THEN 10.0 ELSE NULL END) as promedio_num")
            )
            ->groupBy('e.id', 'e.apellido_paterno', 'e.apellido_materno', 'e.nombres')
            ->orderBy('e.apellido_paterno')
            ->orderBy('e.apellido_materno')
            ->orderBy('e.nombres')
            ->get();

        $estudiantes = [];
        foreach ($rows as $row) {
            $nombre = trim("{$row->apellido_paterno} {$row->apellido_materno}, {$row->nombres}");
            $nivelGlobal = null;
            if ((int) $row->total_notas > 0 && $row->promedio_num !== null) {
                $nivelGlobal = $this->diagnosticoService->convertirNumeroAEscala((float) $row->promedio_num);
            }

            $estudiantes[] = [
                'id' => (int) $row->id,
                'nombre' => $nombre,
                'nivel_global' => $nivelGlobal,
            ];
        }

        return $estudiantes;
    }

    /**
     * NOTA METODOLÓGICA SOBRE EL CÁLCULO DEL NIVEL GLOBAL:
     * PromedioService calcula los promedios al cierre formal del bimestre a nivel de competencia
     * (almacenándolos en la tabla 'promedios_bimestrales'). Mientras dicha tabla no sea poblada
     * formalmente, este servicio calcula el nivel global en vivo convirtiendo las calificaciones
     * cualitativas a sus equivalentes numéricos oficiales (AD=20, A=17, B=13, C=10), obteniendo
     * el promedio aritmético de las competencias del periodo y aplicando los puntos de corte
     * oficiales (AD >= 17.5, A >= 13.5, B >= 10.5, C < 10.5).
     * Dado que el promedio aritmético de calificaciones cualitativas convertidas a valores de
     * intervalo es una estimación continua y no un cálculo oficial cerrado en SIAGIE/CNEB,
     * este indicador tiene un carácter diagnóstico y pedagógico referencial.
     *
     * Obtiene el detalle de diagnóstico de un estudiante para el bimestre y curso especificado.
     * Optimizado a un máximo de 3 consultas SQL (Contexto + Catálogo + Calificaciones agrupadas en PHP).
     *
     * @param int $estudianteId
     * @param int $gradoSeccionId
     * @param int $bimestreId
     * @param int|null $cursoId
     * @param int|null $anoLectivoId
     * @return array
     */
    public function getDetalleDiagnosticoEstudiante(
        int $estudianteId,
        int $gradoSeccionId,
        int $bimestreId,
        ?int $cursoId = null,
        ?int $anoLectivoId = null
    ): array {
        // Consulta 1: Contexto integrado y verificación de seguridad/matrícula
        // Resuelve en una sola consulta con joins: año lectivo, bimestre actual, bimestre anterior y matrícula.
        $contexto = DB::table('matriculas as m')
            ->join('estudiantes as e', 'm.estudiante_id', '=', 'e.id')
            ->join('anos_lectivos as al', function ($j) use ($anoLectivoId) {
                $j->on('m.ano_lectivo_id', '=', 'al.id');
                if ($anoLectivoId) {
                    $j->where('al.id', '=', $anoLectivoId);
                } else {
                    $j->where('al.activo', '=', true);
                }
            })
            ->join('grado_secciones as gs', function ($j) {
                $j->on('m.grado_seccion_id', '=', 'gs.id')->on('gs.ano_lectivo_id', '=', 'al.id');
            })
            ->join('grados as g', 'gs.grado_id', '=', 'g.id')
            ->join('bimestres as b_act', function ($j) use ($bimestreId) {
                $j->on('b_act.ano_lectivo_id', '=', 'al.id')->where('b_act.id', '=', $bimestreId);
            })
            ->where('m.estudiante_id', $estudianteId)
            ->where('m.grado_seccion_id', $gradoSeccionId)
            ->where('m.estado', '!=', 'retirado')
            ->select(
                'e.id as estudiante_id',
                'e.apellido_paterno',
                'e.apellido_materno',
                'e.nombres',
                'al.id as ano_lectivo_id',
                'g.nivel as nivel_seccion',
                'b_act.id as b_act_id',
                'b_act.numero as b_act_numero',
                DB::raw('(SELECT b_ant.id FROM bimestres b_ant WHERE b_ant.ano_lectivo_id = al.id AND b_ant.numero < b_act.numero ORDER BY b_ant.numero DESC LIMIT 1) as b_ant_id'),
                DB::raw('(SELECT b_ant.numero FROM bimestres b_ant WHERE b_ant.ano_lectivo_id = al.id AND b_ant.numero < b_act.numero ORDER BY b_ant.numero DESC LIMIT 1) as b_ant_numero')
            )
            ->first();

        if (!$contexto) {
            abort(404, 'Estudiante no encontrado o no matriculado en esta sección y año lectivo.');
        }

        $nombreCompleto = trim("{$contexto->apellido_paterno} {$contexto->apellido_materno}, {$contexto->nombres}");
        $columnaActual = "Bimestre {$contexto->b_act_numero}";
        $columnaAnterior = $contexto->b_ant_numero ? "Bimestre {$contexto->b_ant_numero}" : null;

        $itemsTabla = [];
        $itemsParaDiagnostico = [];
        $sumNumAct = 0.0; $cntNumAct = 0;
        $sumNumAnt = 0.0; $cntNumAnt = 0;

        $bimestresIds = array_values(array_filter([(int) $contexto->b_act_id, (int) $contexto->b_ant_id]));

        if ($cursoId) {
            // Consulta 2: Catálogo de competencias del curso
            $competencias = DB::table('competencias')
                ->where('curso_id', $cursoId)
                ->orderBy('orden')
                ->select('id', 'nombre')
                ->get();

            // Consulta 3: Calificaciones del bimestre actual y anterior en una sola consulta
            $notasRaw = DB::table('notas_bimestrales')
                ->where('estudiante_id', $estudianteId)
                ->whereIn('bimestre_id', $bimestresIds)
                ->where('curso_id', $cursoId)
                ->whereNotNull('nota')
                ->select('bimestre_id', 'competencia_id', 'nota')
                ->get();

            // Agrupación en PHP
            $notasPorBimYComp = [];
            foreach ($notasRaw as $nr) {
                $notasPorBimYComp[$nr->bimestre_id][$nr->competencia_id] = $nr->nota;
            }

            foreach ($competencias as $comp) {
                $act = $notasPorBimYComp[$contexto->b_act_id][$comp->id] ?? null;
                $ant = $contexto->b_ant_id ? ($notasPorBimYComp[$contexto->b_ant_id][$comp->id] ?? null) : null;

                $itemsTabla[] = [
                    'nombre' => $comp->nombre,
                    'anterior' => $ant,
                    'actual' => $act,
                ];

                if ($act) {
                    $nVal = $this->diagnosticoService->convertirLetraANumero($act);
                    if ($nVal !== null) {
                        $sumNumAct += $nVal;
                        $cntNumAct++;
                    }
                }
                if ($ant) {
                    $nVal = $this->diagnosticoService->convertirLetraANumero($ant);
                    if ($nVal !== null) {
                        $sumNumAnt += $nVal;
                        $cntNumAnt++;
                    }
                }

                $itemsParaDiagnostico[] = [
                    'nombre' => $comp->nombre,
                    'nivel' => $act,
                ];
            }
        } else {
            // Consulta 2: Catálogo de áreas/cursos
            $cursosQuery = DB::table('cursos')
                ->where('activo', true)
                ->whereNotIn('codigo', ['TIC', 'AUT', 'CAS', '0002', '0006', '0007'])
                ->whereNotIn('nombre', ['CASTELLANO COMO SEGUNDA LENGUA', 'GESTIONA SU APRENDIZAJE', 'DESENVOLVIMIENTO EN TIC']);

            if ($contexto->nivel_seccion) {
                $cursosQuery->where(function ($q) use ($contexto) {
                    $q->where('nivel', $contexto->nivel_seccion)->orWhere('nivel', 'ambos');
                });
            }

            $cursos = $cursosQuery->orderBy('nombre')->select('id', 'nombre')->get();

            // Consulta 3: Calificaciones de todos los cursos para bimestre actual y anterior en una sola consulta
            $notasRaw = DB::table('notas_bimestrales')
                ->where('estudiante_id', $estudianteId)
                ->whereIn('bimestre_id', $bimestresIds)
                ->whereNotNull('nota')
                ->select('bimestre_id', 'curso_id', 'nota')
                ->get();

            // Agrupación y cálculo numérico en PHP
            $valoresPorBimYCurso = [];
            foreach ($notasRaw as $nr) {
                $num = $this->diagnosticoService->convertirLetraANumero($nr->nota);
                if ($num !== null) {
                    $valoresPorBimYCurso[$nr->bimestre_id][$nr->curso_id][] = $num;
                }
            }

            $mapNombres = [
                'COMU' => 'COMUNICACIÓN',
                'PPSS' => 'PERSONAL SOCIAL',
                'INGLES EXT' => 'INGLÉS',
            ];

            foreach ($cursos as $c) {
                $nomUpper = strtoupper(trim($c->nombre));
                $nombreMostrado = $mapNombres[$nomUpper] ?? $c->nombre;

                $valsAct = $valoresPorBimYCurso[$contexto->b_act_id][$c->id] ?? [];
                $promAct = !empty($valsAct) ? (array_sum($valsAct) / count($valsAct)) : null;

                $promAnt = null;
                if ($contexto->b_ant_id && !empty($valoresPorBimYCurso[$contexto->b_ant_id][$c->id])) {
                    $valsAnt = $valoresPorBimYCurso[$contexto->b_ant_id][$c->id];
                    $promAnt = array_sum($valsAnt) / count($valsAnt);
                }

                $nivelAct = $this->diagnosticoService->convertirNumeroAEscala($promAct);
                $nivelAnt = $this->diagnosticoService->convertirNumeroAEscala($promAnt);

                $itemsTabla[] = [
                    'nombre' => $nombreMostrado,
                    'anterior' => $nivelAnt,
                    'actual' => $nivelAct,
                ];

                if ($promAct !== null) {
                    $sumNumAct += (float) $promAct;
                    $cntNumAct++;
                }
                if ($promAnt !== null) {
                    $sumNumAnt += (float) $promAnt;
                    $cntNumAnt++;
                }

                $itemsParaDiagnostico[] = [
                    'nombre' => $nombreMostrado,
                    'nivel' => $nivelAct,
                ];
            }
        }

        // 4. Calcular promedio global y nivel global
        $promedioGlobalActual = $cntNumAct > 0 ? ($sumNumAct / $cntNumAct) : null;
        $promedioGlobalAnterior = $cntNumAnt > 0 ? ($sumNumAnt / $cntNumAnt) : null;
        $nivelGlobal = $this->diagnosticoService->convertirNumeroAEscala($promedioGlobalActual);

        // 5. Generar diagnóstico cualitativo
        $diagnosticoTexto = $this->diagnosticoService->generarDiagnosticoEstudiante(
            $itemsParaDiagnostico,
            $contexto->b_ant_id !== null,
            $promedioGlobalActual,
            $promedioGlobalAnterior
        );

        return [
            'estudiante' => [
                'id' => (int) $contexto->estudiante_id,
                'nombre' => $nombreCompleto,
                'nivel_global' => $nivelGlobal,
            ],
            'columnas' => [
                'anterior' => $columnaAnterior,
                'actual' => $columnaActual,
            ],
            'competencias' => $itemsTabla,
            'diagnostico' => $diagnosticoTexto,
        ];
    }
}
