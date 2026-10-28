<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnalyticsFilterRequest;
use App\Http\Requests\Admin\EstudianteDiagnosticoRequest;
use App\Models\AnoLectivo;
use App\Models\Bimestre;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\GradoSeccion;
use App\Services\AnaliticaEvaluacionService;
use App\Services\DiagnosticoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    protected AnaliticaEvaluacionService $analiticaService;

    public function __construct(AnaliticaEvaluacionService $analiticaService)
    {
        $this->analiticaService = $analiticaService;
    }

    public function index(AnalyticsFilterRequest $request)
    {
        $anoActivo = AnoLectivo::where('activo', true)->first();
        if (!$anoActivo) {
            return redirect()->route('admin.dashboard')->with('error', 'No hay año lectivo activo.');
        }

        $bimestres = Bimestre::where('ano_lectivo_id', $anoActivo->id)->orderBy('numero')->get();
        
        $seccionesQuery = GradoSeccion::with('grado', 'seccion')
            ->where('ano_lectivo_id', $anoActivo->id)
            ->join('grados', 'grado_secciones.grado_id', '=', 'grados.id')
            ->join('secciones', 'grado_secciones.seccion_id', '=', 'secciones.id')
            ->orderBy('grados.nivel')
            ->orderBy('grados.orden')
            ->orderBy('secciones.nombre')
            ->select('grado_secciones.*');

        if ($request->filled('nivel')) {
            $seccionesQuery->where('grados.nivel', $request->nivel);
        }

        $secciones = $seccionesQuery->get();

        // Cursos disponibles para el filtro (filtrables por nivel si se selecciona)
        $cursosQuery = Curso::where('activo', true)->soloCursos()->orderBy('nombre');
        if ($request->filled('nivel')) {
            $cursosQuery->paraNivel($request->nivel);
        }
        $cursos = $cursosQuery->get();

        $tipoVista = $request->input('tipo_vista', 'bimestral');
        $datos = [];
        $resumenGlobal = null;
        $graficosData = [];
        $umbralAlerta = config('analitica.umbral_alerta_riesgo', 40.0);

        if ($tipoVista === 'bimestral' && $request->filled('bimestre_id')) {
            $datos = $this->analiticaService->getDistribucionBimestral($anoActivo->id, $request->validated());
            $resumenGlobal = $this->analiticaService->calcularResumenGlobal($datos);
            
            // Preparar JSON para ApexCharts
            foreach ($datos as $cId => &$area) {
                foreach ($area['competencias'] as $compNombre => &$compData) {
                    $chartId = 'chart_' . $cId . '_' . md5($compNombre);
                    $compData['chart_id'] = $chartId;
                    
                    $graficosData[] = [
                        'chart_id' => $chartId,
                        'series' => [[
                            'name' => 'Porcentaje',
                            'data' => [
                                $compData['porcentajes']['AD'], 
                                $compData['porcentajes']['A'], 
                                $compData['porcentajes']['B'], 
                                $compData['porcentajes']['C']
                            ]
                        ]],
                        'labels' => ['Logro Destacado (AD)', 'Logro Esperado (A)', 'En Proceso (B)', 'En Inicio (C)']
                    ];
                }
            }
            unset($area);
            unset($compData);
        } elseif ($tipoVista === 'comparativa') {
            $bComps = array_filter($request->input('bimestres_comp', []));
            if (count($bComps) < 2) {
                return back()->with('error', 'Debe seleccionar al menos 2 bimestres para comparar.');
            }
            
            sort($bComps);
            $datos = $this->analiticaService->getComparativaInterbimestral($anoActivo->id, $bComps, $request->validated());
            
            // Mapear números de bimestres para las etiquetas
            $bimestresModelos = Bimestre::whereIn('id', $bComps)->get()->keyBy('id');

            foreach ($datos as $cId => &$area) {
                foreach ($area['competencias'] as $compNombre => &$compData) {
                    $series = [];
                    foreach ($bComps as $bId) {
                        $num = $bimestresModelos->has($bId) ? $bimestresModelos[$bId]->numero : $bId;
                        $series[] = [
                            'name' => 'Bimestre ' . $num,
                            'data' => [
                                $compData['bimestres_data'][$bId]['porcentajes']['AD'],
                                $compData['bimestres_data'][$bId]['porcentajes']['A'],
                                $compData['bimestres_data'][$bId]['porcentajes']['B'],
                                $compData['bimestres_data'][$bId]['porcentajes']['C']
                            ]
                        ];
                    }

                    $chartId = 'chart_' . $cId . '_' . md5($compNombre);
                    $compData['chart_id'] = $chartId;

                    $graficosData[] = [
                        'chart_id' => $chartId,
                        'series' => $series,
                        'categories' => ['AD', 'A', 'B', 'C']
                    ];
                }
            }
            unset($area);
            unset($compData);
        }

        // Obtener lista de estudiantes con nivel global para la pestaña de diagnóstico
        $estudiantes = [];
        if ($request->filled('grado_seccion_id') && $request->filled('bimestre_id')) {
            $estudiantes = $this->analiticaService->getEstudiantesSeccionConNivel(
                $anoActivo->id,
                (int) $request->grado_seccion_id,
                (int) $request->bimestre_id,
                $request->filled('curso_id') ? (int) $request->curso_id : null
            );
        }

        return view('admin.analytics.index', compact(
            'anoActivo',
            'bimestres',
            'secciones',
            'cursos',
            'tipoVista',
            'datos',
            'resumenGlobal',
            'graficosData',
            'umbralAlerta',
            'estudiantes'
        ));
    }

    /**
     * Endpoint JSON para obtener el detalle de diagnóstico de un estudiante seleccionado.
     *
     * @param int $id
     * @param EstudianteDiagnosticoRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function diagnosticoEstudiante(int $id, EstudianteDiagnosticoRequest $request)
    {
        $resultado = $this->analiticaService->getDetalleDiagnosticoEstudiante(
            $id,
            (int) $request->validated('grado_seccion_id'),
            (int) $request->validated('bimestre_id'),
            $request->validated('curso_id') ? (int) $request->validated('curso_id') : null
        );

        return response()->json($resultado);
    }

    /**
     * Genera la vista A4 limpia para el informe de rendimiento y diagnóstico del estudiante.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function reporteEstudiantePdf(Request $request)
    {
        $request->validate([
            'estudiante_id' => 'required|integer|exists:estudiantes,id',
            'grado_seccion_id' => 'required|integer|exists:grado_secciones,id',
            'bimestres' => 'nullable|array',
            'bimestre_ids' => 'nullable|array',
            'cursos' => 'nullable|array',
            'curso_ids' => 'nullable|array',
            'incluir_comparativa' => 'nullable|boolean',
        ]);

        $anoActivo = AnoLectivo::where('activo', true)->firstOrFail();
        $estudianteId = (int) $request->estudiante_id;
        $gradoSeccionId = (int) $request->grado_seccion_id;
        $incluirComparativa = $request->boolean('incluir_comparativa', true);

        // Validar matrícula del estudiante en la sección y año activo (excluyendo retirados)
        $matricula = DB::table('matriculas')
            ->where('estudiante_id', $estudianteId)
            ->where('grado_seccion_id', $gradoSeccionId)
            ->where('ano_lectivo_id', $anoActivo->id)
            ->where('estado', '!=', 'retirado')
            ->first();

        if (!$matricula) {
            abort(404, 'El estudiante no se encuentra matriculado en la sección indicada.');
        }

        $estudiante = Estudiante::findOrFail($estudianteId);
        $gradoSeccion = GradoSeccion::with('grado', 'seccion')->findOrFail($gradoSeccionId);

        // Bimestres seleccionados
        $bIds = $request->input('bimestres', $request->input('bimestre_ids', []));
        if (empty($bIds)) {
            $bimestres = Bimestre::where('ano_lectivo_id', $anoActivo->id)->orderBy('numero')->get();
        } else {
            $bimestres = Bimestre::where('ano_lectivo_id', $anoActivo->id)
                ->whereIn('id', array_map('intval', (array) $bIds))
                ->orderBy('numero')
                ->get();
        }

        if ($bimestres->isEmpty()) {
            abort(400, 'Debe seleccionar al menos un bimestre para el informe.');
        }

        // Cursos seleccionados
        $cIds = $request->input('cursos', $request->input('curso_ids', []));
        $cursosQuery = Curso::where('activo', true)
            ->paraNivel($gradoSeccion->grado->nivel)
            ->soloCursos()
            ->orderBy('nombre');

        if (!empty($cIds) && !in_array('todos', (array) $cIds)) {
            $cursosQuery->whereIn('id', array_map('intval', (array) $cIds));
        }
        $cursos = $cursosQuery->get();

        // Obtener todas las competencias de los cursos en una sola consulta
        $competencias = DB::table('competencias')
            ->whereIn('curso_id', $cursos->pluck('id'))
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        // Obtener todas las notas del estudiante para estos cursos y bimestres
        $notasRaw = DB::table('notas_bimestrales')
            ->where('estudiante_id', $estudianteId)
            ->whereIn('curso_id', $cursos->pluck('id'))
            ->whereIn('bimestre_id', $bimestres->pluck('id'))
            ->select('curso_id', 'competencia_id', 'bimestre_id', 'nota')
            ->get();

        $notasIndexadas = [];
        foreach ($notasRaw as $n) {
            $notasIndexadas[(int) $n->curso_id][(int) $n->competencia_id][(int) $n->bimestre_id] = $n->nota;
        }

        $diagnosticoService = app(DiagnosticoService::class);

        $cursosData = [];
        $totalCompetenciasEvaluadas = 0;
        $conteoEscalasGlobal = ['AD' => 0, 'A' => 0, 'B' => 0, 'C' => 0];

        $ultimoBimestre = $bimestres->last();
        $penultimoBimestre = $bimestres->count() > 1 ? $bimestres[$bimestres->count() - 2] : null;

        foreach ($cursos as $curso) {
            $compsCurso = $competencias->where('curso_id', $curso->id);
            if ($compsCurso->isEmpty()) {
                continue;
            }

            $competenciasList = [];
            $itemsActuales = [];
            $promedioActualSuma = 0;
            $promedioActualCount = 0;
            $promedioAntSuma = 0;
            $promedioAntCount = 0;

            foreach ($compsCurso as $comp) {
                $notasBimestre = [];
                foreach ($bimestres as $b) {
                    $nota = $notasIndexadas[$curso->id][$comp->id][$b->id] ?? null;
                    $notasBimestre[$b->id] = $nota;
                }

                $notaActual = $ultimoBimestre ? ($notasIndexadas[$curso->id][$comp->id][$ultimoBimestre->id] ?? null) : null;
                $notaAnt = $penultimoBimestre ? ($notasIndexadas[$curso->id][$comp->id][$penultimoBimestre->id] ?? null) : null;

                if ($notaActual) {
                    $itemsActuales[] = ['nombre' => $comp->nombre, 'nivel' => $notaActual];
                    $numActual = $diagnosticoService->convertirLetraANumero($notaActual);
                    if ($numActual !== null) {
                        $promedioActualSuma += $numActual;
                        $promedioActualCount++;
                    }
                    if (isset($conteoEscalasGlobal[$notaActual])) {
                        $conteoEscalasGlobal[$notaActual]++;
                        $totalCompetenciasEvaluadas++;
                    }
                }

                if ($notaAnt) {
                    $numAnt = $diagnosticoService->convertirLetraANumero($notaAnt);
                    if ($numAnt !== null) {
                        $promedioAntSuma += $numAnt;
                        $promedioAntCount++;
                    }
                }

                $variacion = null;
                $tendencia = 'estable';
                if ($notaAnt && $notaActual) {
                    $num1 = $diagnosticoService->convertirLetraANumero($notaAnt);
                    $num2 = $diagnosticoService->convertirLetraANumero($notaActual);
                    if ($num2 > $num1) {
                        $tendencia = 'mejora';
                        $variacion = '+ Mejora';
                    } elseif ($num2 < $num1) {
                        $tendencia = 'descenso';
                        $variacion = '- En riesgo';
                    } else {
                        $tendencia = 'estable';
                        $variacion = '= Mantiene';
                    }
                }

                $competenciasList[] = [
                    'id' => $comp->id,
                    'nombre' => $comp->nombre,
                    'notas_por_bimestre' => $notasBimestre,
                    'nota_actual' => $notaActual,
                    'nota_anterior' => $notaAnt,
                    'tendencia' => $tendencia,
                    'variacion' => $variacion,
                ];
            }

            $promActual = $promedioActualCount > 0 ? round($promedioActualSuma / $promedioActualCount, 2) : null;
            $promAnt = $promedioAntCount > 0 ? round($promedioAntSuma / $promedioAntCount, 2) : null;

            $diagnosticoCurso = $diagnosticoService->generarDiagnosticoEstudiante(
                $itemsActuales,
                $penultimoBimestre !== null,
                $promActual,
                $promAnt
            );

            $cursosData[] = [
                'id' => $curso->id,
                'nombre' => $curso->nombre,
                'competencias' => $competenciasList,
                'diagnostico' => $diagnosticoCurso,
                'nivel_promedio' => $diagnosticoService->convertirNumeroAEscala($promActual),
            ];
        }

        $diagnosticoGlobal = null;
        if (count($cursosData) > 1) {
            $diagGlobalItems = [];
            foreach ($cursosData as $cd) {
                foreach ($cd['competencias'] as $cmp) {
                    if ($cmp['nota_actual']) {
                        $diagGlobalItems[] = ['nombre' => $cmp['nombre'] . ' (' . $cd['nombre'] . ')', 'nivel' => $cmp['nota_actual']];
                    }
                }
            }
            $diagnosticoGlobal = $diagnosticoService->generarDiagnosticoEstudiante(
                $diagGlobalItems,
                $penultimoBimestre !== null
            );
        }

        return view('admin.analytics.pdf.informe_estudiante', compact(
            'estudiante',
            'gradoSeccion',
            'bimestres',
            'cursosData',
            'incluirComparativa',
            'totalCompetenciasEvaluadas',
            'conteoEscalasGlobal',
            'diagnosticoGlobal'
        ));
    }

    /**
     * Genera la vista A4 limpia para el informe comparativo interbimestral.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function reporteComparativoPdf(Request $request)
    {
        $request->validate([
            'grado_seccion_id' => 'required|integer|exists:grado_secciones,id',
            'bimestres' => 'nullable|array',
            'bimestre_ids' => 'nullable|array',
            'cursos' => 'nullable|array',
            'curso_ids' => 'nullable|array',
        ]);

        $anoActivo = AnoLectivo::where('activo', true)->firstOrFail();
        $gradoSeccionId = (int) $request->grado_seccion_id;
        $gradoSeccion = GradoSeccion::with('grado', 'seccion')->findOrFail($gradoSeccionId);

        $bIds = $request->input('bimestres', $request->input('bimestre_ids', []));
        if (empty($bIds) || count((array) $bIds) < 2) {
            // Por defecto tomar los dos primeros bimestres del año
            $bIds = Bimestre::where('ano_lectivo_id', $anoActivo->id)
                ->orderBy('numero')
                ->limit(2)
                ->pluck('id')
                ->toArray();
        }

        $bIdsInt = array_map('intval', (array) $bIds);
        sort($bIdsInt);

        $bimestres = Bimestre::whereIn('id', $bIdsInt)->orderBy('numero')->get();
        if ($bimestres->count() < 2) {
            abort(400, 'Debe seleccionar al menos dos bimestres para la comparativa.');
        }

        $cIds = $request->input('cursos', $request->input('curso_ids', []));
        $filtros = [
            'grado_seccion_id' => $gradoSeccionId,
            'tipo_vista' => 'comparativa'
        ];

        $datosComparativa = $this->analiticaService->getComparativaInterbimestral($anoActivo->id, $bIdsInt, $filtros);

        // Filtrar cursos si se seleccionaron específicos
        if (!empty($cIds) && !in_array('todos', (array) $cIds)) {
            $cIdsInt = array_map('intval', (array) $cIds);
            $datosComparativa = array_intersect_key($datosComparativa, array_flip($cIdsInt));
        }

        return view('admin.analytics.pdf.informe_comparativo', compact(
            'gradoSeccion',
            'bimestres',
            'datosComparativa'
        ));
    }
}
