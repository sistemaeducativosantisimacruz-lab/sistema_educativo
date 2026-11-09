<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnoLectivo;
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
        $anoPostula = $request->get('ano_postula', 2027);

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

        // Métricas / KPIs calculadas sobre el año seleccionado
        $baseMetrics = EntrevistaAdmision::where('ano_lectivo_postula', $anoPostula);
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

        return view('admin.admision.index', compact(
            'entrevistas',
            'anoPostula',
            'totalPostulantes',
            'totalConformes',
            'totalNoConformes',
            'totalPendientes',
            'totalMatriculados',
            'anosLectivos',
            'anoActivo',
            'gradoSecciones',
            'docentes',
            'gradosPostulaDisponibles'
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

