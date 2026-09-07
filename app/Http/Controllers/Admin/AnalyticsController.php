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
                    $graficosData[$cId][$compNombre] = [
                        'series' => [$compData['AD'], $compData['A'], $compData['B'], $compData['C']],
                        'labels' => ['Logro Destacado (AD)', 'Logro Esperado (A)', 'En Proceso (B)', 'En Inicio (C)']
                    ];
                }
            }
        } elseif ($tipoVista === 'comparativa' && $request->filled('bimestres_comp')) {
            $bimestresComp = $request->bimestres_comp;
            if (count($bimestresComp) === 2) {
                $datos = $this->analyticsService->getComparativaInterbimestral($anoActivo->id, $bimestresComp, $request->all());
                
                foreach ($datos as $cId => $area) {
                    foreach ($area['competencias'] as $compNombre => $compData) {
                        $graficosData[$cId][$compNombre] = [
                            'series' => [
                                [
                                    'name' => 'Bimestre ' . Bimestre::find($bimestresComp[0])->numero,
                                    'data' => [$compData['b1']['porcentajes']['AD'], $compData['b1']['porcentajes']['A'], $compData['b1']['porcentajes']['B'], $compData['b1']['porcentajes']['C']]
                                ],
                                [
                                    'name' => 'Bimestre ' . Bimestre::find($bimestresComp[1])->numero,
                                    'data' => [$compData['b2']['porcentajes']['AD'], $compData['b2']['porcentajes']['A'], $compData['b2']['porcentajes']['B'], $compData['b2']['porcentajes']['C']]
                                ]
                            ],
                            'categories' => ['AD', 'A', 'B', 'C']
                        ];
                    }
                }
            }
        }

        return view('admin.analytics.index', compact('anoActivo', 'bimestres', 'secciones', 'tipoVista', 'datos', 'graficosData'));
    }
}
