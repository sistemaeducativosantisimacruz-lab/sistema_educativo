<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnalyticsFilterRequest;
use App\Models\AnoLectivo;
use App\Models\Bimestre;
use App\Models\Curso;
use App\Models\GradoSeccion;
use App\Services\AnaliticaEvaluacionService;

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

        return view('admin.analytics.index', compact(
            'anoActivo',
            'bimestres',
            'secciones',
            'cursos',
            'tipoVista',
            'datos',
            'resumenGlobal',
            'graficosData',
            'umbralAlerta'
        ));
    }
}
