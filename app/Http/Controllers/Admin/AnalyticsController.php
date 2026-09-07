<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AnoLectivo;
use App\Models\Bimestre;
use App\Models\GradoSeccion;
use App\Services\AnalyticsService;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request)
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

        $tipoVista = $request->input('tipo_vista', 'bimestral');
        $datos = [];
        $graficosData = [];

        if ($tipoVista === 'bimestral' && $request->filled('bimestre_id')) {
            $datos = $this->analyticsService->getDistribucionBimestral($anoActivo->id, $request->all());
            
            // Preparar JSON para ChartJS / ApexCharts
            foreach ($datos as $cId => $area) {
                foreach ($area['competencias'] as $compNombre => $compData) {
                    $chartId = 'chart_' . $cId . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $compNombre);
                    $graficosData[] = [
                        'chart_id' => $chartId,
                        'series' => [$compData['AD'], $compData['A'], $compData['B'], $compData['C']],
                        'labels' => ['Logro Destacado (AD)', 'Logro Esperado (A)', 'En Proceso (B)', 'En Inicio (C)']
                    ];
                }
            }
        } elseif ($tipoVista === 'comparativa') {
            $bComps = array_filter($request->input('bimestres_comp', []));
            if (count($bComps) < 2) {
                return back()->with('error', 'Debe seleccionar al menos 2 bimestres para comparar.');
            }
            
            sort($bComps);
            $datos = $this->analyticsService->getComparativaInterbimestral($anoActivo->id, $bComps, $request->all());
            
            // Mapear números de bimestres para las etiquetas
            $bimestresModelos = Bimestre::whereIn('id', $bComps)->get()->keyBy('id');

            foreach ($datos as $cId => $area) {
                foreach ($area['competencias'] as $compNombre => $compData) {
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

                    $chartId = 'chart_' . $cId . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $compNombre);
                    $graficosData[] = [
                        'chart_id' => $chartId,
                        'series' => $series,
                        'categories' => ['AD', 'A', 'B', 'C']
                    ];
                }
            }
        }

        return view('admin.analytics.index', compact('anoActivo', 'bimestres', 'secciones', 'tipoVista', 'datos', 'graficosData'));
    }
}
