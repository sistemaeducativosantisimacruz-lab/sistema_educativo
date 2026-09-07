<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnoLectivo;
use App\Models\GradoSeccion;
use App\Services\ExcelPreviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Estudiante;
use App\Models\Apoderado;
use App\Models\ImportacionSiagie;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class ImportController extends Controller
{
    protected $importService;

    public function __construct(\App\Services\ImportService $importService)
    {
        $this->importService = $importService;
    }

    public function create()
    {
        $anoActivo = AnoLectivo::where('activo', true)->first();
        $gradoSecciones = collect();
        $bimestres = collect();
        if ($anoActivo) {
            $gradoSecciones = GradoSeccion::join('grados', 'grado_secciones.grado_id', '=', 'grados.id')
                ->join('secciones', 'grado_secciones.seccion_id', '=', 'secciones.id')
                ->where('grado_secciones.ano_lectivo_id', $anoActivo->id)
                ->where('grado_secciones.activo', true)
                ->orderBy('grados.nivel')
                ->orderBy('grados.orden')
                ->orderBy('secciones.nombre')
                ->select('grado_secciones.*')
                ->with(['grado', 'seccion'])
                ->get();
            $bimestres = \App\Models\Bimestre::where('ano_lectivo_id', $anoActivo->id)->get();
        }
        $niveles = \DB::table('grados')->distinct()->orderBy('nivel')->pluck('nivel');
        return view('admin.importaciones.create', compact('gradoSecciones', 'anoActivo', 'bimestres', 'niveles'));
    }

    public function preview(Request $request, ExcelPreviewService $excelService)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:20480',
        ]);
        try {
            $file = $request->file('file');
            $preview = $excelService->parse($file->getRealPath());
            return response()->json([
                'success' => true,
                'columns' => $preview['headers'],
                'rows' => $preview['rows'],
                'original_name' => $file->getClientOriginalName(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Import preview error: '.$e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // New page for import wizard (standalone view)
    public function createImportar()
    {
        $anoActivo = AnoLectivo::where('activo', true)->first();
        $gradoSecciones = collect();
        $bimestres = collect();
        $historial = collect();
        if ($anoActivo) {
            $gradoSecciones = GradoSeccion::join('grados', 'grado_secciones.grado_id', '=', 'grados.id')
                ->join('secciones', 'grado_secciones.seccion_id', '=', 'secciones.id')
                ->where('grado_secciones.ano_lectivo_id', $anoActivo->id)
                ->where('grado_secciones.activo', true)
                ->orderBy('grados.nivel')
                ->orderBy('grados.orden')
                ->orderBy('secciones.nombre')
                ->select('grado_secciones.*')
                ->with(['grado', 'seccion'])
                ->get();
            $bimestres = \App\Models\Bimestre::where('ano_lectivo_id', $anoActivo->id)->get();
            
            $historial = \App\Models\ImportacionSiagie::with(['admin', 'gradoSeccion.grado', 'gradoSeccion.seccion'])
                ->where('ano_lectivo_id', $anoActivo->id)
                ->latest()
                ->paginate(20);
        }
        $niveles = \DB::table('grados')->distinct()->orderBy('nivel')->pluck('nivel');
        return view('admin.importar', compact('gradoSecciones', 'anoActivo', 'bimestres', 'niveles', 'historial'));
    }

    public function confirmar(Request $request, ExcelPreviewService $excelService)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:20480',
            'mapping' => 'required|json',
            'tipo' => 'required|string',
            'nivel_id' => 'required|string',
            'grado_id' => 'required|string',
            'seccion_id' => 'required|string',
            'unselected_indices' => 'nullable|json',
        ]);

        try {
            $mapping = json_decode($request->mapping, true);
            $unselectedIndices = $request->unselected_indices ? json_decode($request->unselected_indices, true) : [];
            $unselectedSet = array_flip($unselectedIndices);

            $file = $request->file('file');
            $anoActivo = AnoLectivo::where('activo', true)->first();
            
            $gradoSeccion = null;
            if ($request->grado_id !== 'todos' && $request->seccion_id !== 'todos') {
                $gradoSeccion = GradoSeccion::where('grado_id', $request->grado_id)
                    ->where('seccion_id', $request->seccion_id)
                    ->where('ano_lectivo_id', $anoActivo->id)
                    ->first();
            }

            if ($request->tipo === 'notas') {
                $bimestreId = $request->input('bimestre_id');
                $bimestre = \App\Models\Bimestre::find($bimestreId);
                if (!$bimestre || $bimestre->estado === 'cerrado') {
                    return response()->json(['success' => false, 'message' => 'El bimestre seleccionado no existe o ya está cerrado.'], 422);
                }
                if (!$request->bimestre_id) {
                    throw new \Exception('Bimestre requerido para la importación de notas.');
                }
                
                $stats = new \stdClass();
                $stats->procesados = [];
                $stats->errores = [];

                \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\NotasBimestralesImport($request->bimestre_id, $gradoSeccion?->id, $file->getRealPath(), $stats, $anoActivo->id), $file);
                
                $estudiantesCount = count($stats->procesados);
                
                // Si no se procesó ningún estudiante y hay errores, el estado es "errores" (no "con_errores")
                // Si se procesó al menos uno y hay errores, es "con_errores"
                $estado = count($stats->errores) > 0 ? ($estudiantesCount === 0 ? 'errores' : 'con_errores') : 'exitoso';
                
                $importacion = new ImportacionSiagie([
                    'admin_id' => Auth::id() ?? 1,
                    'grado_seccion_id' => $gradoSeccion?->id,
                    'ano_lectivo_id' => $anoActivo?->id,
                    'nombre_archivo' => $file->getClientOriginalName(),
                    'tipo' => $request->tipo,
                    'estudiantes_importados' => $estudiantesCount,
                    'errores' => count($stats->errores) > 0 ? $stats->errores : null,
                    'estado' => $estado,
                ]);
                $importacion->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Proceso de importación finalizado. Se importaron/actualizaron notas de ' . $estudiantesCount . ' estudiantes.',
                    'errores' => $stats->errores
                ]);
            }

            $preview = $excelService->parseComplete($file->getRealPath());
            $rows = $preview['rows'];

            $result = $this->importService->procesarImportacion($rows, $unselectedSet, $request, $mapping, $gradoSeccion, $anoActivo, $file);

            return response()->json([
                'success' => true,
                'message' => 'Importación exitosa. Se procesaron ' . $result['importados'] . ' registros.',
                'errores' => count($result['errores']) > 0 ? $result['errores'] : []
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Import confirmar error: '.$e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function revertir($id)
    {
        try {
            $importacion = \App\Models\ImportacionSiagie::findOrFail($id);
            $time = $importacion->created_at;

            DB::beginTransaction();

            $start = $time->copy()->subSeconds(10);
            $end = $time->copy()->addSeconds(10);

            if ($importacion->tipo === 'notas') {
                \DB::table('calificaciones')
                    ->whereBetween('created_at', [$start, $end])
                    ->delete();
                
                \DB::table('promedios_bimestrales')
                    ->whereBetween('created_at', [$start, $end])
                    ->delete();
            } else {
                // Borramos registros creados en esta importación
                \App\Models\Apoderado::whereBetween('created_at', [$start, $end])->delete();
                \DB::table('matriculas')->whereBetween('created_at', [$start, $end])->delete();
                \App\Models\Estudiante::whereBetween('created_at', [$start, $end])->delete();

                // Eliminar usuarios estudiantes creados
                \App\Models\User::whereHas('role', function($q) {
                    $q->where('nombre', 'estudiante');
                })->whereBetween('created_at', [$start, $end])->delete();
            }

            $importacion->estado = 'revertido';
            $importacion->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Importación revertida exitosamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error revertir importacion: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al revertir: ' . $e->getMessage()], 500);
        }
    }

    

    

    

    

    

    

    
    

    public function checkNotasExist(Request $request)
    {
        $request->validate([
            'grado_id' => 'required|integer',
            'seccion_id' => 'required|integer',
            'bimestre_id' => 'required|integer'
        ]);

        $anoActivo = AnoLectivo::where('activo', true)->first();
        if (!$anoActivo) {
            return response()->json(['success' => false, 'message' => 'No hay año lectivo activo.']);
        }

        $gradoSeccion = GradoSeccion::where('grado_id', $request->grado_id)
            ->where('seccion_id', $request->seccion_id)
            ->where('ano_lectivo_id', $anoActivo->id)
            ->first();

        if (!$gradoSeccion) {
            return response()->json(['success' => true, 'exists' => false]);
        }

        // Check if there are any notes for students in this grado_seccion for the given bimestre
        $estudiantesIds = \App\Models\Matricula::where('grado_seccion_id', $gradoSeccion->id)
            ->where('ano_lectivo_id', $anoActivo->id)
            ->where('estado', '!=', 'retirado')
            ->pluck('estudiante_id');

        $count = \App\Models\NotaBimestral::where('bimestre_id', $request->bimestre_id)
            ->whereIn('estudiante_id', $estudiantesIds)
            ->whereNotNull('nota')
            ->count();

        return response()->json([
            'success' => true,
            'exists' => $count > 0,
            'count' => $count
        ]);
    }
}
