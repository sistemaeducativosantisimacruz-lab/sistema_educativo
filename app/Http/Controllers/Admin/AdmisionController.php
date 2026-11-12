<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnoLectivo;
use App\Models\Bimestre;
use App\Models\Docente;
use App\Models\EntrevistaAdmision;
use App\Models\GradoSeccion;
use App\Models\User;
use App\Services\EntrevistaImportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdmisionController extends Controller
{
    /**
     * Listado general de postulantes y entrevistas de admisión.
     */
    public function index(Request $request)
    {
        $anoActual = AnoLectivo::where('activo', true)->value('anio') ?? (int) date('Y');

        $anosEnEntrevistas = EntrevistaAdmision::select('ano_lectivo_postula')
            ->distinct()
            ->pluck('ano_lectivo_postula')
            ->toArray();
        $anosLectivosIds = AnoLectivo::pluck('anio')->toArray();
        $todosLosAnos = array_values(array_unique(array_merge($anosLectivosIds, $anosEnEntrevistas, [$anoActual, $anoActual + 1])));
        rsort($todosLosAnos);

        // Resolver año a filtrar
        if ($request->has('ano_postula')) {
            $val = $request->get('ano_postula');
            $anoPostula = ($val === 'todos' || $val === '') ? null : (int) $val;
            session(['filtro_ano_postula' => $val]);
        } elseif (session()->has('filtro_ano_postula')) {
            $val = session('filtro_ano_postula');
            $anoPostula = ($val === 'todos' || $val === '') ? null : (int) $val;
        } else {
            // Priorizar el año que tenga entrevistas registradas si el año actual no tiene
            $tieneAnoSiguiente = in_array($anoActual + 1, $anosEnEntrevistas);
            $tieneAnoActual = in_array($anoActual, $anosEnEntrevistas);
            if ($tieneAnoSiguiente && !$tieneAnoActual) {
                $anoPostula = $anoActual + 1;
            } else {
                $anoPostula = $anoActual;
            }
        }

        $query = EntrevistaAdmision::with([
            'docenteEntrevistador',
            'seccionAsignada.grado',
            'seccionAsignada.seccion',
            'estudiante',
            'matricula'
        ]);

        if ($anoPostula) {
            $query->where('ano_lectivo_postula', $anoPostula);
        }

        // Filtro por búsqueda de texto (DNI, nombres, apellidos, padres)
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

        // Filtro por nivel
        if ($nivel = $request->get('nivel')) {
            $query->where('nivel', $nivel);
        }

        // Filtro por grado al que postula
        if ($grado = $request->get('grado_postula')) {
            $query->where('grado_postula', $grado);
        }

        // Filtro por conformidad
        if ($conformidad = $request->get('resultado_conformidad')) {
            $query->where('resultado_conformidad', $conformidad);
        }

        // Filtro por estado
        if ($estado = $request->get('estado')) {
            $query->where('estado', $estado);
        }

        // Filtro por docente entrevistador
        if ($docenteId = $request->get('docente_id')) {
            $query->where('docente_entrevistador_id', $docenteId);
        }

        // Métricas / KPIs calculadas sobre el año seleccionado (o global si se filtran todos)
        $baseMetrics = EntrevistaAdmision::query();
        if ($anoPostula) {
            $baseMetrics->where('ano_lectivo_postula', $anoPostula);
        }
        $totalPostulantes = (clone $baseMetrics)->count();
        $totalConformes = (clone $baseMetrics)->where('resultado_conformidad', 'CONFORME')->count();
        $totalNoConformes = (clone $baseMetrics)->where('resultado_conformidad', 'NO CONFORME')->count();
        $totalPendientes = (clone $baseMetrics)->where('estado', 'nuevo_ingresante')->count();
        $totalMatriculados = (clone $baseMetrics)->where('estado', 'matriculado')->count();

        // Postulantes paginados
        $entrevistas = $query->orderByDesc('id')->paginate(15)->withQueryString();

        // Opciones para filtros y modal de asignación
        $anosLectivos = AnoLectivo::orderByDesc('anio')->get();
        $anoActivo = AnoLectivo::where('activo', true)->first();

        // Control de bloqueo de matrícula: requiere que el año lectivo activo tenga sus 4 bimestres cerrados
        $bimestresTotal = $anoActivo ? Bimestre::where('ano_lectivo_id', $anoActivo->id)->count() : 0;
        $bimestresCerrados = $anoActivo ? Bimestre::where('ano_lectivo_id', $anoActivo->id)->where('estado', 'cerrado')->count() : 0;
        $puedeMatricular = ($anoActivo && $bimestresTotal >= 4 && $bimestresCerrados >= 4);
        
        $gradoSecciones = GradoSeccion::with(['grado', 'seccion', 'anoLectivo'])
            ->where('activo', true)
            ->get()
            ->sortBy([
                fn ($a, $b) => $a->grado->orden <=> $b->grado->orden,
                fn ($a, $b) => $a->seccion->nombre <=> $b->seccion->nombre,
            ]);

        $docentes = User::whereHas('role', fn ($q) => $q->where('nombre', 'docente'))
            ->orderBy('name')
            ->get();

        $gradosPostulaDisponibles = EntrevistaAdmision::select('grado_postula')
            ->distinct()
            ->orderBy('grado_postula')
            ->pluck('grado_postula');

        $docentesParaAsignar = Docente::with('user')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombres')
            ->get();

        return view('admin.admision.index', compact(
            'entrevistas',
            'anoPostula',
            'anoActual',
            'totalPostulantes',
            'totalConformes',
            'totalNoConformes',
            'totalPendientes',
            'totalMatriculados',
            'anosLectivos',
            'anoActivo',
            'puedeMatricular',
            'bimestresTotal',
            'bimestresCerrados',
            'gradoSecciones',
            'docentes',
            'docentesParaAsignar',
            'gradosPostulaDisponibles',
            'todosLosAnos'
        ));
    }

    /**
     * Descarga la plantilla oficial en Excel (Formato 1).
     */
    public function descargarPlantilla(Request $request, EntrevistaImportService $service): StreamedResponse
    {
        $nivel = $request->get('nivel', 'primaria');
        return $service->descargarPlantilla($nivel);
    }

    /**
     * Importa una o varias fichas de entrevista en Excel.
     */
    public function importar(Request $request, EntrevistaImportService $service)
    {
        $request->validate([
            'archivos' => 'nullable|array',
            'archivos.*' => 'nullable|file|mimes:xlsx,xls',
            'archivo' => 'nullable|file|mimes:xlsx,xls',
            'ano_postula' => 'required|integer',
            'docente_id' => 'nullable|exists:users,id',
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
            return back()->with('error', 'Debe seleccionar al menos un archivo Excel de entrevista para procesar.');
        }

        $docenteId = $request->filled('docente_id') ? (int) $request->docente_id : auth()->id();
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
            return redirect()->route('admin.admision.index', ['ano_postula' => $anoPostula])
                ->with('success', "Se importaron exitosamente {$procesados} ficha(s) de entrevista.");
        } elseif ($procesados > 0 && !empty($errores)) {
            return redirect()->route('admin.admision.index', ['ano_postula' => $anoPostula])
                ->with('warning', "Se procesaron {$procesados} ficha(s), pero hubo errores en " . count($errores) . " archivo(s): " . implode(' | ', $errores));
        } else {
            return back()->with('error', 'No se pudo importar ningún archivo. Motivos: ' . implode(' | ', $errores));
        }
    }

    /**
     * Muestra el detalle completo de la entrevista y su cuestionario.
     */
    public function show(EntrevistaAdmision $entrevista)
    {
        $entrevista->load([
            'docenteEntrevistador',
            'seccionAsignada.grado',
            'seccionAsignada.seccion',
            'estudiante',
            'matricula'
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json($entrevista);
        }

        return view('admin.admision.show', compact('entrevista'));
    }

    /**
     * Formaliza la matrícula asignando una sección al postulante.
     */
    public function matricular(Request $request, EntrevistaAdmision $entrevista, EntrevistaImportService $service)
    {
        $request->validate([
            'grado_seccion_id' => 'required|exists:grado_secciones,id',
            'ano_lectivo_id' => 'nullable|exists:anos_lectivos,id',
        ]);

        // Validar que los 4 bimestres del año lectivo activo estén cerrados
        $anoActivo = AnoLectivo::where('activo', true)->first();
        $bimestresTotal = $anoActivo ? Bimestre::where('ano_lectivo_id', $anoActivo->id)->count() : 0;
        $bimestresCerrados = $anoActivo ? Bimestre::where('ano_lectivo_id', $anoActivo->id)->where('estado', 'cerrado')->count() : 0;

        if (!$anoActivo || $bimestresTotal < 4 || $bimestresCerrados < 4) {
            $anoStr = $anoActivo ? $anoActivo->anio : 'actual';
            return back()->with('error', "La opción de matricular está bloqueada. Para habilitar la matrícula, deben estar cerrados los 4 bimestres del año lectivo {$anoStr} en el sistema (Actualmente: {$bimestresCerrados}/4 cerrados).");
        }

        $gradoSeccion = GradoSeccion::findOrFail($request->grado_seccion_id);
        $anoLectivoId = $request->ano_lectivo_id ?: $gradoSeccion->ano_lectivo_id;

        try {
            $resultado = $service->matricularPostulante($entrevista, $gradoSeccion->id, $anoLectivoId);

            return redirect()->route('admin.admision.index', ['ano_postula' => $entrevista->ano_lectivo_postula])
                ->with('success', "¡Postulante {$entrevista->nombre_completo_estudiante} matriculado exitosamente en {$resultado['seccion']}!");
        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar la matrícula: ' . $e->getMessage());
        }
    }

    /**
     * Alterna la asignación del rol de entrevistador para un docente.
     */
    public function toggleEntrevistador(Request $request, Docente $docente)
    {
        $docente->update([
            'es_entrevistador' => !$docente->es_entrevistador,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'docente_id' => $docente->id,
                'es_entrevistador' => (bool) $docente->es_entrevistador,
                'nombre' => $docente->nombre_completo,
                'mensaje' => $docente->es_entrevistador
                    ? "Rol de entrevistador asignado a {$docente->nombre_completo}."
                    : "Rol de entrevistador revocado a {$docente->nombre_completo}.",
            ]);
        }

        return back()->with('success', $docente->es_entrevistador
            ? "Rol de entrevistador asignado a {$docente->nombre_completo}."
            : "Rol de entrevistador revocado a {$docente->nombre_completo}.");
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
     * Muestra el formulario para crear una entrevista en línea.
     */
    public function create(Request $request)
    {
        $anoActual = AnoLectivo::where('activo', true)->value('anio') ?? (int) date('Y');
        $nivel = $request->get('nivel', 'primaria');
        $gradosPrimaria = \App\Models\Grado::where('nivel', 'primaria')->orderBy('orden')->get();
        $gradosSecundaria = \App\Models\Grado::where('nivel', 'secundaria')->orderBy('orden')->get();
        $docentes = User::whereHas('role', fn ($q) => $q->where('nombre', 'docente'))->orderBy('name')->get();

        $entrevista = new EntrevistaAdmision([
            'ano_lectivo_postula' => $anoActual,
            'nivel' => $nivel,
            'grado_postula' => $nivel === 'secundaria' ? '1° Secundaria' : '1° Primaria',
            'fecha_entrevista' => now()->toDateString(),
            'resultado_conformidad' => null,
            'estado_entrevista' => 'en_progreso',
            'docente_entrevistador_id' => auth()->id(),
        ]);

        $routeStore = route('admin.admision.guardar-en-linea');
        $routeCerrar = route('admin.admision.cerrar', ['entrevista' => '__ID__']);
        $routeBack = route('admin.admision.index');
        $routeBuscarEstudiantes = route('admin.admision.buscar-estudiantes');
        $esAdmin = true;

        return view('admin.admision.form', compact(
            'entrevista', 'anoActual', 'nivel', 'gradosPrimaria', 'gradosSecundaria',
            'docentes', 'routeStore', 'routeCerrar', 'routeBack', 'routeBuscarEstudiantes', 'esAdmin'
        ));
    }

    /**
     * Muestra el formulario para editar o continuar una entrevista en línea.
     */
    public function edit(EntrevistaAdmision $entrevista)
    {
        $anoActual = AnoLectivo::where('activo', true)->value('anio') ?? (int) date('Y');
        $nivel = $entrevista->nivel;
        $gradosPrimaria = \App\Models\Grado::where('nivel', 'primaria')->orderBy('orden')->get();
        $gradosSecundaria = \App\Models\Grado::where('nivel', 'secundaria')->orderBy('orden')->get();
        $docentes = User::whereHas('role', fn ($q) => $q->where('nombre', 'docente'))->orderBy('name')->get();

        $routeStore = route('admin.admision.guardar-en-linea');
        $routeCerrar = route('admin.admision.cerrar', ['entrevista' => $entrevista->id]);
        $routeBack = route('admin.admision.index');
        $routeBuscarEstudiantes = route('admin.admision.buscar-estudiantes');
        $esAdmin = true;

        return view('admin.admision.form', compact(
            'entrevista', 'anoActual', 'nivel', 'gradosPrimaria', 'gradosSecundaria',
            'docentes', 'routeStore', 'routeCerrar', 'routeBack', 'routeBuscarEstudiantes', 'esAdmin'
        ));
    }

    /**
     * Guarda el progreso (borrador / autoguardado) de la entrevista en línea.
     */
    public function guardarEnLinea(Request $request, EntrevistaImportService $service)
    {
        $docenteId = $request->docente_id ?: auth()->id();
        $entrevistaId = $request->entrevista_id ? (int) $request->entrevista_id : null;

        try {
            $entrevista = $service->guardarEntrevistaEnLinea($request->all(), $docenteId, $entrevistaId, 'en_progreso');

            // Recordar año de postulación en la sesión
            session(['filtro_ano_postula' => (string) $entrevista->ano_lectivo_postula]);

            return response()->json([
                'success' => true,
                'entrevista_id' => $entrevista->id,
                'ano_lectivo_postula' => $entrevista->ano_lectivo_postula,
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
        $docenteId = $request->docente_id ?: auth()->id();

        try {
            $entrevista = $service->guardarEntrevistaEnLinea($request->all(), $docenteId, $entrevista->id, 'completada');

            // Recordar año de postulación en la sesión
            session(['filtro_ano_postula' => (string) $entrevista->ano_lectivo_postula]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('admin.admision.index', ['ano_postula' => $entrevista->ano_lectivo_postula]),
                    'mensaje' => "¡Entrevista de {$entrevista->nombre_completo_estudiante} registrada y cerrada exitosamente!",
                ]);
            }

            return redirect()->route('admin.admision.index', ['ano_postula' => $entrevista->ano_lectivo_postula])
                ->with('success', "¡Entrevista de {$entrevista->nombre_completo_estudiante} registrada y cerrada exitosamente!");
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 422);
            }
            return back()->with('error', 'Error al cerrar entrevista: ' . $e->getMessage());
        }
    }

    /**
     * Elimina una ficha de entrevista (solo permitido si está en estado 'nuevo_ingresante').
     */
    public function destroy(EntrevistaAdmision $entrevista)
    {
        if ($entrevista->estado === 'matriculado') {
            return back()->with('error', 'No se puede eliminar una entrevista de un estudiante que ya ha sido formalmente matriculado.');
        }

        $nombre = $entrevista->nombre_completo_estudiante;
        $entrevista->delete();

        return redirect()->route('admin.admision.index')
            ->with('success', "Se eliminó correctamente la ficha de entrevista de {$nombre}.");
    }
}

