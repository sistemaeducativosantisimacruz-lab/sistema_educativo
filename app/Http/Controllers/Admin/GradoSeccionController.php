<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grado;
use App\Models\Seccion;
use App\Models\GradoSeccion;
use App\Models\AnoLectivo;
use App\Models\AsignacionDocente;
use App\Models\Curso;
use App\Models\Docente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GradoSeccionController extends Controller
{
    public function index(Request $request)
    {
        $anoActivo = AnoLectivo::where('activo', true)->first();
        
        $gradoSecciones = collect();
        if ($anoActivo) {
            $query = GradoSeccion::with(['grado', 'seccion', 'tutor', 'cotutor'])
                ->withCount('matriculas')
                ->where('ano_lectivo_id', $anoActivo->id);

            if ($request->filled('nivel')) {
                $query->whereHas('grado', function ($q) use ($request) {
                    $q->where('nivel', $request->nivel);
                });
            }

            if ($request->filled('grado_id')) {
                $query->where('grado_id', $request->grado_id);
            }

            if ($request->filled('seccion_id')) {
                $query->where('seccion_id', $request->seccion_id);
            }

            $gradoSecciones = $query
                ->join('secciones', 'grado_secciones.seccion_id', '=', 'secciones.id')
                ->join('grados as g_ord', 'grado_secciones.grado_id', '=', 'g_ord.id')
                ->orderBy('g_ord.orden')
                ->orderBy('secciones.nombre')
                ->select('grado_secciones.*')
                ->get();
        }

        $grados = Grado::orderBy('orden')->get();
        $secciones = Seccion::orderBy('nombre')->get();
        $docentes = Docente::orderBy('apellido_paterno')->get();

        $assignedTutors = [];
        if ($anoActivo) {
            $assignedTutors = GradoSeccion::where('ano_lectivo_id', $anoActivo->id)
                ->whereNotNull('tutor_id')
                ->pluck('tutor_id', 'id')->toArray();
        }

        $gradoSeccionesAgrupadas = $gradoSecciones->groupBy('grado_id');

        return view('admin.grado-secciones.index', compact('gradoSecciones', 'gradoSeccionesAgrupadas', 'anoActivo', 'grados', 'secciones', 'docentes', 'assignedTutors'));
    }

    public function create()
    {
        $grados = Grado::orderBy('orden')->get();
        $secciones = Seccion::orderBy('nombre')->get();
        $anoActivo = AnoLectivo::where('activo', true)->first();

        return view('admin.grado-secciones.create', compact('grados', 'secciones', 'anoActivo'));
    }

    public function store(Request $request)
    {
        $anoActivo = AnoLectivo::where('activo', true)->first();
        if (!$anoActivo) {
            return back()->with('error', 'No hay un año lectivo activo.');
        }

        $request->validate([
            'grado_id' => 'required|exists:grados,id',
            'seccion_id' => [
                'required',
                'exists:secciones,id',
                Rule::unique('grado_secciones')->where(function ($query) use ($request, $anoActivo) {
                    return $query->where('grado_id', $request->grado_id)
                                 ->where('ano_lectivo_id', $anoActivo->id);
                }),
            ],
        ], [
            'seccion_id.unique' => 'Esta sección ya está asignada a este grado en el año lectivo actual.',
        ]);

        GradoSeccion::create([
            'grado_id' => $request->grado_id,
            'seccion_id' => $request->seccion_id,
            'ano_lectivo_id' => $anoActivo->id,
            'activo' => true,
        ]);

        return redirect()->route('admin.grado-secciones.index')
                         ->with('success', 'Sección asignada al grado exitosamente.');
    }

    public function destroy(GradoSeccion $gradoSeccione)
    {
        $gradoSeccione->delete();
        return redirect()->route('admin.grado-secciones.index')
                         ->with('success', 'Sección eliminada correctamente.');
    }

    /**
     * Devuelve en JSON el tutor y co-tutor actuales de una sección (para el modal AJAX).
     */
    public function getTutores(GradoSeccion $gradoSeccione)
    {
        $gradoSeccione->load(['tutor', 'cotutor']);

        return response()->json([
            'tutor_id'       => $gradoSeccione->tutor_id,
            'cotutor_id'     => $gradoSeccione->cotutor_id,
            'tutor_nombre'   => $gradoSeccione->tutor
                ? $gradoSeccione->tutor->apellido_paterno . ' ' . $gradoSeccione->tutor->apellido_materno . ', ' . $gradoSeccione->tutor->nombres
                : null,
            'cotutor_nombre' => $gradoSeccione->cotutor
                ? $gradoSeccione->cotutor->apellido_paterno . ' ' . $gradoSeccione->cotutor->apellido_materno . ', ' . $gradoSeccione->cotutor->nombres
                : null,
        ]);
    }

    public function updateTutores(Request $request, GradoSeccion $gradoSeccione)
    {
        $request->validate([
            'tutor_id'   => 'nullable|exists:docentes,id',
            'cotutor_id' => 'nullable|exists:docentes,id|different:tutor_id',
        ], [
            'cotutor_id.different' => 'El Co-tutor debe ser distinto al Tutor principal.',
        ]);

        $gradoSeccione->load('grado');
        $grado = $gradoSeccione->grado;

        // Validar nivel del tutor
        if ($request->tutor_id) {
            $tutor = Docente::find($request->tutor_id);
            if ($tutor && $tutor->nivel !== $grado->nivel) {
                return back()->with('error', 'El docente tutor debe pertenecer al mismo nivel educativo que la sección.');
            }

            $existeTutor = GradoSeccion::where('ano_lectivo_id', $gradoSeccione->ano_lectivo_id)
                ->where('tutor_id', $request->tutor_id)
                ->where('id', '!=', $gradoSeccione->id)
                ->exists();

            if ($existeTutor) {
                return back()->with('error', 'El docente seleccionado ya está asignado como tutor en otra sección.');
            }
        }

        // Validar nivel del co-tutor
        if ($request->cotutor_id) {
            $cotutor = Docente::find($request->cotutor_id);
            if ($cotutor && $cotutor->nivel !== $grado->nivel) {
                return back()->with('error', 'El co-tutor debe pertenecer al mismo nivel educativo que la sección.');
            }
        }

        // ── Gestión automática del curso de Tutoría ──────────────────────────
        // Buscar el curso de Tutoría para el nivel de este grado-sección
        $cursoTutoria = Curso::where(function ($q) use ($grado) {
                $q->whereRaw('LOWER(nombre) LIKE ?', ['%tutoría%'])
                  ->orWhereRaw('LOWER(nombre) LIKE ?', ['%tutoria%']);
            })
            ->where(function ($q) use ($grado) {
                $q->where('nivel', $grado->nivel)->orWhere('nivel', 'ambos');
            })
            ->first();

        $tutorAnteriorId  = $gradoSeccione->tutor_id;
        $nuevoTutorId     = $request->tutor_id ? (int) $request->tutor_id : null;
        $tutorCambio      = $tutorAnteriorId !== $nuevoTutorId;

        if ($cursoTutoria && $tutorCambio) {
            // Quitar curso Tutoría al tutor anterior (solo esta sección)
            if ($tutorAnteriorId) {
                AsignacionDocente::where('docente_id', $tutorAnteriorId)
                    ->where('grado_seccion_id', $gradoSeccione->id)
                    ->where('curso_id', $cursoTutoria->id)
                    ->where('ano_lectivo_id', $gradoSeccione->ano_lectivo_id)
                    ->delete();
            }

            // Asignar curso Tutoría al nuevo tutor (si no lo tiene ya en esta sección)
            if ($nuevoTutorId) {
                AsignacionDocente::firstOrCreate(
                    [
                        'docente_id'       => $nuevoTutorId,
                        'grado_seccion_id' => $gradoSeccione->id,
                        'curso_id'         => $cursoTutoria->id,
                        'ano_lectivo_id'   => $gradoSeccione->ano_lectivo_id,
                    ],
                    ['activo' => true]
                );
            }
        } elseif ($cursoTutoria && !$tutorCambio && $nuevoTutorId) {
            // Sin cambio de tutor, garantizar que tenga la asignación
            AsignacionDocente::firstOrCreate(
                [
                    'docente_id'       => $nuevoTutorId,
                    'grado_seccion_id' => $gradoSeccione->id,
                    'curso_id'         => $cursoTutoria->id,
                    'ano_lectivo_id'   => $gradoSeccione->ano_lectivo_id,
                ],
                ['activo' => true]
            );
        }

        // Asegurar que el tutor tenga el curso de Tutoría en docente_cursos
        if ($cursoTutoria && $nuevoTutorId) {
            $tutorModel = \App\Models\Docente::find($nuevoTutorId);
            if ($tutorModel) {
                $tutorModel->cursos()->syncWithoutDetaching([$cursoTutoria->id]);
            }
        }

        // ── Para primaria polidocente: asignación general (sin curso específico) ─
        if ($grado && $grado->nivel === 'primaria' && in_array($grado->orden, [1, 2, 3, 4])) {
            // Eliminar asignación general (curso_id NULL) del tutor anterior
            if ($tutorCambio && $tutorAnteriorId) {
                AsignacionDocente::where('docente_id', $tutorAnteriorId)
                    ->where('grado_seccion_id', $gradoSeccione->id)
                    ->where('ano_lectivo_id', $gradoSeccione->ano_lectivo_id)
                    ->whereNull('curso_id')
                    ->delete();
            }

            // Crear asignación general para el nuevo tutor
            if ($nuevoTutorId) {
                AsignacionDocente::firstOrCreate(
                    [
                        'docente_id'       => $nuevoTutorId,
                        'grado_seccion_id' => $gradoSeccione->id,
                        'ano_lectivo_id'   => $gradoSeccione->ano_lectivo_id,
                        'curso_id'         => null,
                    ],
                    ['activo' => true]
                );
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        // Guardar el cambio de tutor/co-tutor en grado_secciones
        $gradoSeccione->update([
            'tutor_id'   => $nuevoTutorId,
            'cotutor_id' => $request->cotutor_id ?: null,
        ]);

        $mensaje = 'Tutores asignados correctamente a la sección.';
        if ($tutorCambio && $tutorAnteriorId && $nuevoTutorId) {
            $mensaje = 'Tutor actualizado. El docente anterior ya no verá el curso de Tutoría de esta sección, pero los datos de los alumnos se conservan intactos.';
        }

        return redirect()->route('admin.grado-secciones.index')
            ->with('success', $mensaje);
    }

    public function detalle(GradoSeccion $gradoSeccion)
    {
        $gradoSeccion->load([
            'grado',
            'seccion',
            'tutor',
            'cotutor',
            'matriculas.estudiante',
            'asignacionesDocente.docente',
            'asignacionesDocente.curso',
        ]);

        $nivel = $gradoSeccion->grado->nivel;

        // Estudiantes activos
        $estudiantes = $gradoSeccion->matriculas
            ->where('estado', '!=', 'retirado')
            ->map(fn($m) => [
                'id'               => $m->estudiante->id,
                'dni'              => $m->estudiante->dni,
                'codigo_estudiante' => $m->estudiante->codigo_estudiante,
                'apellidos'        => $m->estudiante->apellido_paterno . ' ' . $m->estudiante->apellido_materno,
                'nombres'          => $m->estudiante->nombres,
                'fecha_nacimiento'  => $m->estudiante->fecha_nacimiento
                    ? $m->estudiante->fecha_nacimiento->format('d/m/Y')
                    : '',
            ])
            ->sortBy('apellidos')
            ->values();

        // Todos los cursos del nivel (incluye 'ambos')
        $cursos = Curso::where('activo', true)
            ->soloCursos()
            ->where(function ($q) use ($nivel) {
                $q->where('nivel', $nivel)->orWhere('nivel', 'ambos');
            })
            ->orderBy('nombre')
            ->get();

        // Asignaciones actuales indexadas por curso_id
        $asignacionesPorCurso = $gradoSeccion->asignacionesDocente
            ->whereNotNull('curso_id')
            ->keyBy('curso_id');

        // Docentes que enseñan cursos de este nivel (especialistas con ese curso, y polidocentes del mismo nivel)
        $docentesDelNivel = Docente::with('cursos')
            ->where('nivel', $nivel)
            ->orderBy('apellido_paterno')
            ->get();

        // Construir lista de cursos con su docente actual y los docentes disponibles
        $cursosConDocentes = $cursos->map(function ($curso) use ($asignacionesPorCurso, $docentesDelNivel, $gradoSeccion) {
            $asignacion = $asignacionesPorCurso->get($curso->id);

            $isTutoria = stripos($curso->nombre, 'tutoría') !== false || stripos($curso->nombre, 'tutoria') !== false;

            if ($isTutoria) {
                $tutoresOcupados = \App\Models\GradoSeccion::where('ano_lectivo_id', $gradoSeccion->ano_lectivo_id)
                    ->whereNotNull('tutor_id')
                    ->where('id', '!=', $gradoSeccion->id)
                    ->pluck('tutor_id')
                    ->toArray();

                $disponibles = $docentesDelNivel->filter(function ($d) use ($tutoresOcupados, $asignacion) {
                    if ($asignacion && $asignacion->docente_id == $d->id) return true;
                    if (in_array($d->id, $tutoresOcupados)) return false;
                    return true;
                });
            } else {
                $disponibles = $docentesDelNivel->filter(function ($d) use ($curso) {
                    if ($d->tipo === 'polidocente') return true;
                    return $d->cursos->contains('id', $curso->id);
                });
            }

            $disponibles = $disponibles->map(fn($d) => [
                'id'     => $d->id,
                'nombre' => $d->apellido_paterno . ' ' . $d->apellido_materno . ', ' . $d->nombres,
            ]);

            if ($asignacion && $asignacion->docente) {
                if (! $disponibles->contains('id', $asignacion->docente_id)) {
                    $disponibles->push([
                        'id'     => $asignacion->docente->id,
                        'nombre' => $asignacion->docente->apellido_paterno . ' ' . $asignacion->docente->apellido_materno . ', ' . $asignacion->docente->nombres,
                    ]);
                }
            }

            $disponibles = $disponibles->values();

            return [
                'curso_id'    => $curso->id,
                'curso_nombre'=> $curso->nombre,
                'docente_id'  => $asignacion ? $asignacion->docente_id : null,
                'docente_nombre' => $asignacion ? ($asignacion->docente->apellido_paterno . ' ' . $asignacion->docente->apellido_materno . ', ' . $asignacion->docente->nombres) : null,
                'disponibles' => $disponibles,
            ];
        })->values();

        // Vista resumida de docentes (compatible con vista anterior)
        $docentes = $gradoSeccion->asignacionesDocente
            ->map(fn($a, $i) => [
                'idx'    => $i,
                'id'     => $a->docente->id . '-' . ($a->curso_id ?? 0),
                'nombre' => $a->docente->apellido_paterno . ' ' . $a->docente->apellido_materno . ', ' . $a->docente->nombres,
                'curso'  => $a->curso ? $a->curso->nombre : 'Todas las áreas',
            ])
            ->sortBy('curso')
            ->values();

        $tutorData = $gradoSeccion->tutor ? [
            'id'              => $gradoSeccion->tutor->id,
            'dni'             => $gradoSeccion->tutor->dni ?? null,
            'nombre_completo' => $gradoSeccion->tutor->apellido_paterno . ' ' . $gradoSeccion->tutor->apellido_materno . ', ' . $gradoSeccion->tutor->nombres,
        ] : null;

        $cotutorData = $gradoSeccion->cotutor ? [
            'id'              => $gradoSeccion->cotutor->id,
            'dni'             => $gradoSeccion->cotutor->dni ?? null,
            'nombre_completo' => $gradoSeccion->cotutor->apellido_paterno . ' ' . $gradoSeccion->cotutor->apellido_materno . ', ' . $gradoSeccion->cotutor->nombres,
        ] : null;

        $anoLectivoModel = AnoLectivo::find($gradoSeccion->ano_lectivo_id);

        return response()->json([
            'seccion_nombre'      => $gradoSeccion->grado->nombre . ' - Sección ' . $gradoSeccion->seccion->nombre,
            'grado_seccion_id'    => $gradoSeccion->id,
            'ano_lectivo_id'      => $gradoSeccion->ano_lectivo_id,
            'anio'                => $anoLectivoModel ? $anoLectivoModel->anio : '',
            'nivel'               => $gradoSeccion->grado->nivel,
            'tutor'               => $tutorData,
            'cotutor'             => $cotutorData,
            'estudiantes'         => $estudiantes,
            'docentes'            => $docentes,
            'cursos_con_docentes' => $cursosConDocentes,
        ]);
    }

    /**
     * Guarda las asignaciones de docentes por curso para una sección.
     * Recibe: { asignaciones: [ { curso_id, docente_id|null }, ... ] }
     */
    public function updateDocentes(Request $request, GradoSeccion $gradoSeccion)
    {
        $request->validate([
            'asignaciones'              => 'required|array',
            'asignaciones.*.curso_id'   => 'required|exists:cursos,id',
            'asignaciones.*.docente_id' => 'nullable|exists:docentes,id',
        ]);

        $gradoSeccion->load('grado');
        $anoLectivoId = $gradoSeccion->ano_lectivo_id;

        foreach ($request->asignaciones as $item) {
            $cursoId   = $item['curso_id'];
            $docenteId = $item['docente_id'] ?? null;

            // Eliminar asignación anterior para este curso en esta sección
            AsignacionDocente::where('grado_seccion_id', $gradoSeccion->id)
                ->where('curso_id', $cursoId)
                ->where('ano_lectivo_id', $anoLectivoId)
                ->delete();

            $cursoObj = \App\Models\Curso::find($cursoId);
            $isTutoria = $cursoObj && (stripos($cursoObj->nombre, 'tutoría') !== false || stripos($cursoObj->nombre, 'tutoria') !== false);
            
            if ($isTutoria) {
                // Actualizar directamente al tutor de la sección para mantener sincronía con el modal de Tutores
                $gradoSeccion->update(['tutor_id' => $docenteId]);
            }

            // Crear nueva si hay docente seleccionado
            if ($docenteId) {
                AsignacionDocente::create([
                    'docente_id'       => $docenteId,
                    'grado_seccion_id' => $gradoSeccion->id,
                    'curso_id'         => $cursoId,
                    'ano_lectivo_id'   => $anoLectivoId,
                    'activo'           => true,
                ]);

                // Agregar el curso a docente_cursos si no lo tiene
                $docenteModel = \App\Models\Docente::find($docenteId);
                if ($docenteModel) {
                    $docenteModel->cursos()->syncWithoutDetaching([$cursoId]);
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Docentes asignados correctamente.']);
    }

    public function exportar(GradoSeccion $gradoSeccion)
    {
        $gradoSeccion->load([
            'grado',
            'seccion',
            'tutor',
            'cotutor',
            'matriculas.estudiante',
        ]);

        $estudiantes = $gradoSeccion->matriculas
            ->where('estado', '!=', 'retirado')
            ->map(fn($m) => [
                'dni'              => $m->estudiante->dni,
                'codigo_estudiante' => $m->estudiante->codigo_estudiante,
                'apellidos'        => $m->estudiante->apellido_paterno . ' ' . $m->estudiante->apellido_materno,
                'nombres'          => $m->estudiante->nombres,
                'fecha_nacimiento'  => $m->estudiante->fecha_nacimiento
                    ? $m->estudiante->fecha_nacimiento->format('d/m/Y')
                    : '',
            ])
            ->sortBy('apellidos')
            ->values()
            ->toArray();

        $anoLectivoModel = \App\Models\AnoLectivo::find($gradoSeccion->ano_lectivo_id);
        $anio = $anoLectivoModel ? $anoLectivoModel->anio : '';

        $seccionNombre = ($gradoSeccion->grado->nombre ?? '') . ' - ' . ($gradoSeccion->seccion->nombre ?? '');
        $filename = 'Alumnos_' . str_replace([' ', '/', '\\'], '_', $seccionNombre) . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SeccionAlumnosExport(
                $seccionNombre,
                $gradoSeccion->tutor,
                $gradoSeccion->cotutor,
                $estudiantes,
                $anio
            ),
            $filename
        );
    }
}

