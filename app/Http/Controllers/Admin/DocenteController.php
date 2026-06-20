<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\Curso;
use App\Models\Grado;
use App\Models\User;
use App\Models\Role;
use App\Models\AnoLectivo;
use App\Models\GradoSeccion;
use App\Models\AsignacionDocente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DocenteController extends Controller
{
    public function index(Request $request)
    {
        $query = Docente::with(['user', 'cursos']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('dni', 'ilike', "%{$search}%")
                  ->orWhere('nombres', 'ilike', "%{$search}%")
                  ->orWhere('apellido_paterno', 'ilike', "%{$search}%")
                  ->orWhere('apellido_materno', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('nivel')) {
            $query->where('nivel', $request->nivel);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('curso_id')) {
            $query->whereHas('cursos', fn($q) => $q->where('cursos.id', $request->curso_id));
        }

        if ($request->filled('grado_id')) {
            $anoActivo = AnoLectivo::where('activo', true)->first();
            if ($anoActivo) {
                $query->whereHas('asignaciones', function ($q) use ($request, $anoActivo) {
                    $q->where('ano_lectivo_id', $anoActivo->id)
                      ->whereHas('gradoSeccion', fn($q2) => $q2->where('grado_id', $request->grado_id));
                });
            }
        }

        $docentes = $query->orderBy('apellido_paterno')->get();
        $cursos   = Curso::where('activo', true)->soloCursos()->orderBy('nombre')->get();
        $grados   = Grado::orderBy('orden')->get();

        return view('admin.docentes.index', compact('docentes', 'cursos', 'grados'));
    }

    public function store(Request $request)
    {
        $esEspecialista = $request->tipo === 'especialista';

        $request->validate([
            'dni'              => 'required|string|size:8|unique:docentes,dni|unique:users,dni',
            'nombres'          => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'email'            => 'nullable|email|unique:users,email',
            'celular'          => 'nullable|string|max:15',
            'nivel'            => 'required|in:primaria,secundaria',
            'tipo'             => 'required|in:especialista,polidocente',
            'curso_ids'        => $esEspecialista ? 'required|array|min:1' : 'nullable|array',
            'curso_ids.*'      => 'exists:cursos,id',
        ], [
            'dni.unique'         => 'El DNI ya está registrado en el sistema.',
            'email.unique'       => 'El correo ya está registrado en el sistema.',
            'curso_ids.required' => 'Debes seleccionar al menos un curso para el docente especialista.',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'                 => mb_strtoupper("{$request->nombres} {$request->apellido_paterno}"),
                'email'                => $request->email,
                'dni'                  => $request->dni,
                'password'             => Hash::make($request->dni),
                'role_id'              => Role::where('nombre', 'docente')->value('id'),
                'must_change_password' => true,
            ]);

            $docente = Docente::create([
                'user_id'          => $user->id,
                'curso_id'         => null,
                'nivel'            => $request->nivel,
                'tipo'             => $request->tipo,
                'dni'              => $request->dni,
                'celular'          => $request->celular,
                'nombres'          => $request->nombres,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
            ]);

            if ($request->tipo === 'especialista' && $request->filled('curso_ids')) {
                $docente->cursos()->sync($request->curso_ids);
            }
        });

        return redirect()->route('admin.docentes.index')
            ->with('success', 'Docente registrado correctamente. La contraseña inicial es su DNI.');
    }

    public function update(Request $request, Docente $docente)
    {
        $esEspecialista = $docente->esEspecialista();

        $request->validate([
            'dni'              => 'required|string|size:8|unique:docentes,dni,' . $docente->id,
            'nombres'          => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'email'            => 'nullable|email|unique:users,email,' . $docente->user_id,
            'celular'          => 'nullable|string|max:15',
            'curso_ids'        => $esEspecialista ? 'required|array|min:1' : 'nullable|array',
            'curso_ids.*'      => $esEspecialista ? 'exists:cursos,id' : 'nullable',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $docente, $esEspecialista) {
            $nombres = strtoupper(trim($request->nombres));
            $apPaterno = strtoupper(trim($request->apellido_paterno));
            $apMaterno = strtoupper(trim($request->apellido_materno));

            $docente->update([
                'dni'              => $request->dni,
                'nombres'          => $nombres,
                'apellido_paterno' => $apPaterno,
                'apellido_materno' => $apMaterno,
                'celular'          => $request->celular,
            ]);

            $nombreCompleto = "{$apPaterno} {$apMaterno} {$nombres}";
            
            $docente->user->update([
                'name'  => $nombreCompleto,
                'email' => $request->email,
                'dni'   => $request->dni,
                'password' => $docente->user->must_change_password ? \Illuminate\Support\Facades\Hash::make($request->dni) : $docente->user->password,
            ]);

            if ($esEspecialista) {
                $docente->cursos()->sync($request->curso_ids);
            }
        });

        return redirect()->route('admin.docentes.index')
            ->with('success', 'Datos del docente actualizados correctamente.');
    }

    public function show(Docente $docente)
    {
        $docente->load('cursos');
        $anoActivo = AnoLectivo::where('activo', true)->first();

        $asignaciones              = collect();
        $gradoSeccionesDisponibles = collect();

        if ($anoActivo) {
            $asignaciones = AsignacionDocente::with([
                    'gradoSeccion.grado',
                    'gradoSeccion.seccion',
                    'curso',
                ])
                ->join('grado_secciones', 'asignaciones_docente.grado_seccion_id', '=', 'grado_secciones.id')
                ->join('grados', 'grado_secciones.grado_id', '=', 'grados.id')
                ->join('secciones', 'grado_secciones.seccion_id', '=', 'secciones.id')
                ->where('asignaciones_docente.docente_id', $docente->id)
                ->where('asignaciones_docente.ano_lectivo_id', $anoActivo->id)
                ->orderBy('grados.orden')
                ->orderBy('secciones.nombre')
                ->select('asignaciones_docente.*')
                ->get();

            $asignadasGsIds = $asignaciones->pluck('grado_seccion_id')->unique()->toArray();

            $gradoSeccionesDisponibles = GradoSeccion::with(['grado', 'seccion'])
                ->where('ano_lectivo_id', $anoActivo->id)
                ->where('activo', true)
                ->whereHas('grado', fn($q) => $q->where('nivel', $docente->nivel))
                ->when($docente->esPolidocente(), fn($q) =>
                    $q->whereNotIn('id', $asignadasGsIds)
                )
                ->get();
        }

        return view('admin.docentes.show', compact(
            'docente', 'asignaciones', 'gradoSeccionesDisponibles', 'anoActivo'
        ));
    }

    public function asignar(Request $request, Docente $docente)
    {
        $anoActivo = AnoLectivo::where('activo', true)->first();
        if (!$anoActivo) {
            return back()->with('error', 'No hay año lectivo activo.');
        }

        $esEspecialista = $docente->esEspecialista();

        $request->validate([
            'asignaciones'                  => 'required|array|min:1',
            'asignaciones.*.grado_seccion_id' => 'required|exists:grado_secciones,id',
            'asignaciones.*.curso_id'         => $esEspecialista ? 'required|exists:cursos,id' : 'nullable',
        ], [
            'asignaciones.required'                    => 'Debes seleccionar al menos una sección.',
            'asignaciones.*.curso_id.required'         => 'Cada sección debe tener un curso seleccionado.',
            'asignaciones.*.grado_seccion_id.required' => 'Sección inválida.',
        ]);

        $exitos  = 0;
        $errores = [];

        foreach ($request->asignaciones as $item) {
            $gs_id   = $item['grado_seccion_id'];
            $cursoId = $esEspecialista ? ($item['curso_id'] ?? null) : null;

            if ($esEspecialista && !$cursoId) {
                $errores[] = 'Se omitió una entrada porque no tenía curso seleccionado.';
                continue;
            }

            $gradoSeccion = GradoSeccion::with(['grado', 'seccion'])->find($gs_id);
            if (!$gradoSeccion) continue;
            $grado = $gradoSeccion->grado;

            if ($grado && $grado->nivel === 'primaria' && in_array($grado->orden, [1, 2, 3, 4])) {
                AsignacionDocente::where('grado_seccion_id', $gs_id)
                    ->where('ano_lectivo_id', $anoActivo->id)
                    ->delete();
                $gradoSeccion->update(['tutor_id' => $docente->id]);
            } else {
                if ($esEspecialista) {
                    $conflicto = AsignacionDocente::with('docente')
                        ->where('grado_seccion_id', $gs_id)
                        ->where('ano_lectivo_id', $anoActivo->id)
                        ->where('curso_id', $cursoId)
                        ->first();

                    if ($conflicto) {
                        $nombreConflicto = $conflicto->docente
                            ? "{$conflicto->docente->apellido_paterno}, {$conflicto->docente->nombres}"
                            : 'otro docente';
                        $esMismoDocente = $conflicto->docente_id === $docente->id;
                        $msg = $esMismoDocente
                            ? "El docente ya tiene ese curso en {$grado->nombre} \"Sección {$gradoSeccion->seccion->nombre}\"."
                            : "El docente '{$nombreConflicto}' ya tiene ese curso en {$grado->nombre} \"Sección {$gradoSeccion->seccion->nombre}\". No se puede asignar dos docentes del mismo curso a una sección.";
                        $errores[] = $msg;
                        continue;
                    }
                }
            }

            $duplicado = AsignacionDocente::where('docente_id', $docente->id)
                ->where('grado_seccion_id', $gs_id)
                ->where('curso_id', $cursoId)
                ->where('ano_lectivo_id', $anoActivo->id)
                ->exists();

            if ($duplicado) {
                $errores[] = "Asignación duplicada: {$grado->nombre} \"Sección {$gradoSeccion->seccion->nombre}\" ya estaba registrada.";
                continue;
            }

            AsignacionDocente::create([
                'docente_id'       => $docente->id,
                'grado_seccion_id' => $gs_id,
                'curso_id'         => $cursoId,
                'ano_lectivo_id'   => $anoActivo->id,
                'activo'           => true,
            ]);

            if ($cursoId) {
                $docente->cursos()->syncWithoutDetaching([$cursoId]);
            }

            $exitos++;
        }

        if (count($errores) > 0 && $exitos === 0) {
            return back()->with('conflictos', $errores);
        } elseif (count($errores) > 0) {
            return redirect()->route('admin.docentes.show', $docente)
                ->with('success', "Se asignaron {$exitos} sección(es) correctamente.")
                ->with('conflictos', $errores);
        }

        return redirect()->route('admin.docentes.show', $docente)
            ->with('success', "Se asignaron {$exitos} sección(es) correctamente.");
    }

    public function verificarConflictos(Request $request, Docente $docente)
    {
        $anoActivo = AnoLectivo::where('activo', true)->first();
        if (!$anoActivo) {
            return response()->json(['conflictos' => []]);
        }

        $asignaciones = $request->input('asignaciones', []);
        $conflictos   = [];

        foreach ($asignaciones as $item) {
            $gs_id   = $item['grado_seccion_id'] ?? null;
            $cursoId = $item['curso_id'] ?? null;

            if (!$gs_id || !$cursoId) continue;

            $gradoSeccion = GradoSeccion::with(['grado', 'seccion'])->find($gs_id);
            if (!$gradoSeccion) continue;

            $conflicto = AsignacionDocente::with('docente')
                ->where('grado_seccion_id', $gs_id)
                ->where('ano_lectivo_id', $anoActivo->id)
                ->where('curso_id', $cursoId)
                ->where('docente_id', '!=', $docente->id)
                ->first();

            if ($conflicto) {
                $nombreConflicto = $conflicto->docente
                    ? "{$conflicto->docente->apellido_paterno}, {$conflicto->docente->nombres}"
                    : 'otro docente';
                $conflictos[] = [
                    'seccion'  => $gradoSeccion->grado->nombre . ' — Sección ' . $gradoSeccion->seccion->nombre,
                    'mensaje'  => "El docente '{$nombreConflicto}' ya enseña este curso en esta sección.",
                ];
            }
        }

        return response()->json(['conflictos' => $conflictos]);
    }

    public function desasignar(Docente $docente, AsignacionDocente $asignacion)
    {
        if ($asignacion->docente_id !== $docente->id) {
            abort(403);
        }

        $asignacion->delete();

        return redirect()->route('admin.docentes.show', $docente)
            ->with('success', 'Asignación removida correctamente.');
    }
}
