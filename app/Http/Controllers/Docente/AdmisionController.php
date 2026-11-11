<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\EntrevistaAdmision;
use App\Services\EntrevistaImportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdmisionController extends Controller
{
    /**
     * Listado de entrevistas realizadas por el docente autenticado.
     */
    public function index(Request $request)
    {
        $docenteId = auth()->id();
        $anoActual = \App\Models\AnoLectivo::where('activo', true)->value('anio') ?? (int) date('Y');
        $anoPostula = $request->filled('ano_postula') ? (int) $request->get('ano_postula') : $anoActual;

        $query = EntrevistaAdmision::with([
            'seccionAsignada.grado',
            'seccionAsignada.seccion',
        ])
        ->where('docente_entrevistador_id', $docenteId);

        if ($anoPostula) {
            $query->where('ano_lectivo_postula', $anoPostula);
        }

        if ($search = trim($request->get('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('estudiante_dni', 'like', "%{$search}%")
                  ->orWhere('estudiante_nombres', 'ilike', "%{$search}%")
                  ->orWhere('estudiante_apellido_paterno', 'ilike', "%{$search}%")
                  ->orWhere('estudiante_apellido_materno', 'ilike', "%{$search}%")
                  ->orWhere('padre_nombres', 'ilike', "%{$search}%")
                  ->orWhere('madre_nombres', 'ilike', "%{$search}%");
            });
        }

        if ($conformidad = $request->get('resultado_conformidad')) {
            $query->where('resultado_conformidad', $conformidad);
        }

        if ($estado = $request->get('estado')) {
            $query->where('estado', $estado);
        }

        // Métricas del docente
        $baseMetrics = EntrevistaAdmision::where('docente_entrevistador_id', $docenteId)
            ->where('ano_lectivo_postula', $anoPostula);

        $totalMisEntrevistas = (clone $baseMetrics)->count();
        $totalConformes = (clone $baseMetrics)->where('resultado_conformidad', 'CONFORME')->count();
        $totalNoConformes = (clone $baseMetrics)->where('resultado_conformidad', 'NO CONFORME')->count();
        $totalMatriculados = (clone $baseMetrics)->where('estado', 'matriculado')->count();

        $entrevistas = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return view('docente.admision.index', compact(
            'entrevistas',
            'anoPostula',
            'anoActual',
            'totalMisEntrevistas',
            'totalConformes',
            'totalNoConformes',
            'totalMatriculados'
        ));
    }

    /**
     * Descarga la plantilla oficial en Excel para que el docente pueda llenarla durante las entrevistas.
     */
    public function descargarPlantilla(Request $request, EntrevistaImportService $service): StreamedResponse
    {
        $nivel = $request->get('nivel', 'primaria');
        return $service->descargarPlantilla($nivel);
    }

    /**
     * Sube y procesa una o varias fichas de entrevista realizadas por el docente.
     */
    public function importar(Request $request, EntrevistaImportService $service)
    {
        $request->validate([
            'archivos' => 'nullable|array',
            'archivos.*' => 'nullable|file|mimes:xlsx,xls',
            'archivo' => 'nullable|file|mimes:xlsx,xls',
            'ano_postula' => 'required|integer',
        ], [
            'archivos.*.mimes' => 'Cada archivo debe ser un libro Excel (.xlsx o .xls).',
            'archivo.mimes' => 'El archivo debe ser un libro Excel (.xlsx o .xls).',
        ]);

        $files = [];
        if ($request->hasFile('archivos')) {
            $files = $request->file('archivos');
        } elseif ($request->hasFile('archivo')) {
            $files = [$request->file('archivo')];
        }

        if (empty($files)) {
            return back()->with('error', 'Debe seleccionar al menos un archivo Excel de entrevista para subir.');
        }

        $docenteId = auth()->id();
        $anoPostula = (int) $request->ano_postula;

        $procesados = 0;
        $errores = [];

        foreach ($files as $file) {
            $nombreOriginal = $file->getClientOriginalName();
            try {
                $service->importarFicha($file, $docenteId, $anoPostula);
                $procesados++;
            } catch (\Exception $e) {
                $errores[] = "{$nombreOriginal}: " . $e->getMessage();
            }
        }

        if ($procesados > 0 && empty($errores)) {
            return redirect()->route('docente.admision.index', ['ano_postula' => $anoPostula])
                ->with('success', "Se subieron e importaron exitosamente {$procesados} ficha(s) de entrevista.");
        } elseif ($procesados > 0 && !empty($errores)) {
            return redirect()->route('docente.admision.index', ['ano_postula' => $anoPostula])
                ->with('warning', "Se procesaron {$procesados} ficha(s), pero hubo observaciones en: " . implode(' | ', $errores));
        } else {
            return back()->with('error', 'No se pudo subir ningún archivo: ' . implode(' | ', $errores));
        }
    }

    /**
     * Muestra el detalle de una entrevista realizada por el docente.
     */
    public function show(EntrevistaAdmision $entrevista)
    {
        if ($entrevista->docente_entrevistador_id !== auth()->id()) {
            abort(403, 'No tiene autorización para visualizar esta ficha de entrevista.');
        }

        $entrevista->load([
            'seccionAsignada.grado',
            'seccionAsignada.seccion',
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json($entrevista);
        }

        return view('docente.admision.show', compact('entrevista'));
    }

    /**
     * Búsqueda en vivo de estudiantes existentes (para autocompletar paso de grado o hermanos).
     */
    public function buscarEstudiantes(Request $request, EntrevistaImportService $service)
    {
        $resultados = $service->buscarEstudiantes($request->get('q', ''));
        return response()->json($resultados);
    }

    /**
     * Muestra el formulario para crear una entrevista en línea como docente.
     */
    public function create(Request $request)
    {
        $anoActual = \App\Models\AnoLectivo::where('activo', true)->value('anio') ?? (int) date('Y');
        $nivel = $request->get('nivel', 'primaria');
        $gradosPrimaria = \App\Models\Grado::where('nivel', 'primaria')->orderBy('orden')->get();
        $gradosSecundaria = \App\Models\Grado::where('nivel', 'secundaria')->orderBy('orden')->get();

        $entrevista = new EntrevistaAdmision([
            'ano_lectivo_postula' => $anoActual,
            'nivel' => $nivel,
            'grado_postula' => $nivel === 'secundaria' ? '1° Secundaria' : '1° Primaria',
            'fecha_entrevista' => now()->toDateString(),
            'resultado_conformidad' => 'CONFORME',
            'estado_entrevista' => 'en_progreso',
            'docente_entrevistador_id' => auth()->id(),
        ]);

        $routeStore = route('docente.admision.guardar-en-linea');
        $routeCerrar = route('docente.admision.cerrar', ['entrevista' => '__ID__']);
        $routeBack = route('docente.admision.index');
        $routeBuscarEstudiantes = route('docente.admision.buscar-estudiantes');
        $esAdmin = false;

        return view('admin.admision.form', compact(
            'entrevista', 'anoActual', 'nivel', 'gradosPrimaria', 'gradosSecundaria',
            'routeStore', 'routeCerrar', 'routeBack', 'routeBuscarEstudiantes', 'esAdmin'
        ));
    }

    /**
     * Muestra el formulario para editar o continuar una entrevista en línea.
     */
    public function edit(EntrevistaAdmision $entrevista)
    {
        if ($entrevista->docente_entrevistador_id !== auth()->id()) {
            abort(403, 'No tiene autorización para editar esta ficha de entrevista.');
        }

        $anoActual = \App\Models\AnoLectivo::where('activo', true)->value('anio') ?? (int) date('Y');
        $nivel = $entrevista->nivel;
        $gradosPrimaria = \App\Models\Grado::where('nivel', 'primaria')->orderBy('orden')->get();
        $gradosSecundaria = \App\Models\Grado::where('nivel', 'secundaria')->orderBy('orden')->get();

        $routeStore = route('docente.admision.guardar-en-linea');
        $routeCerrar = route('docente.admision.cerrar', ['entrevista' => $entrevista->id]);
        $routeBack = route('docente.admision.index');
        $routeBuscarEstudiantes = route('docente.admision.buscar-estudiantes');
        $esAdmin = false;

        return view('admin.admision.form', compact(
            'entrevista', 'anoActual', 'nivel', 'gradosPrimaria', 'gradosSecundaria',
            'routeStore', 'routeCerrar', 'routeBack', 'routeBuscarEstudiantes', 'esAdmin'
        ));
    }

    /**
     * Guarda el progreso (borrador / autoguardado) de la entrevista en línea.
     */
    public function guardarEnLinea(Request $request, EntrevistaImportService $service)
    {
        $docenteId = auth()->id();
        $entrevistaId = $request->entrevista_id ? (int) $request->entrevista_id : null;

        if ($entrevistaId) {
            $existente = EntrevistaAdmision::findOrFail($entrevistaId);
            if ($existente->docente_entrevistador_id !== $docenteId) {
                abort(403, 'No tiene permiso para modificar esta ficha.');
            }
        }

        try {
            $entrevista = $service->guardarEntrevistaEnLinea($request->all(), $docenteId, $entrevistaId, 'en_progreso');

            return response()->json([
                'success' => true,
                'entrevista_id' => $entrevista->id,
                'estado_entrevista' => $entrevista->estado_entrevista,
                'saved_at' => now()->format('H:i:s'),
                'mensaje' => 'Progreso guardado automáticamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al guardar progreso: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Registra y cierra definitivamente la entrevista en línea.
     */
    public function cerrarEntrevista(Request $request, EntrevistaAdmision $entrevista, EntrevistaImportService $service)
    {
        $docenteId = auth()->id();
        if ($entrevista->docente_entrevistador_id !== $docenteId) {
            abort(403, 'No tiene permiso para cerrar esta ficha.');
        }

        try {
            $entrevista = $service->guardarEntrevistaEnLinea($request->all(), $docenteId, $entrevista->id, 'completada');

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('docente.admision.index', ['ano_postula' => $entrevista->ano_lectivo_postula]),
                    'mensaje' => "¡Entrevista de {$entrevista->nombre_completo_estudiante} registrada y cerrada exitosamente!",
                ]);
            }

            return redirect()->route('docente.admision.index', ['ano_postula' => $entrevista->ano_lectivo_postula])
                ->with('success', "¡Entrevista de {$entrevista->nombre_completo_estudiante} registrada y cerrada exitosamente!");
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 422);
            }
            return back()->with('error', 'Error al cerrar entrevista: ' . $e->getMessage());
        }
    }
}

