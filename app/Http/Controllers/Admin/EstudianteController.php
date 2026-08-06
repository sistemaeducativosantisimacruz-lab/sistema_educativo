<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apoderado;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\GradoSeccion;
use App\Models\AnoLectivo;
use App\Models\Grado;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class EstudianteController extends Controller
{
    public function index(Request $request)
    {
        $anoActivo      = AnoLectivo::where('activo', true)->first();
        $gradoSecciones = collect();
        $matriculas     = collect();

        if ($anoActivo) {
            $gradoSecciones = GradoSeccion::with(['grado', 'seccion'])
                ->where('ano_lectivo_id', $anoActivo->id)
                ->get();

            $query = Matricula::select('matriculas.*')
                ->join('estudiantes', 'matriculas.estudiante_id', '=', 'estudiantes.id')
                ->with([
                    'estudiante.apoderado',
                    'estudiante.padre',
                    'estudiante.madre',
                    'gradoSeccion.grado',
                    'gradoSeccion.seccion',
                ])
                ->where('matriculas.ano_lectivo_id', $anoActivo->id)
                ->orderBy('estudiantes.apellido_paterno', 'asc')
                ->orderBy('estudiantes.apellido_materno', 'asc')
                ->orderBy('estudiantes.nombres', 'asc');

            if ($request->filled('grado_seccion_id')) {
                $query->where('matriculas.grado_seccion_id', $request->grado_seccion_id);
            }

            if ($request->filled('nivel')) {
                $query->whereHas('gradoSeccion.grado', function ($q) use ($request) {
                    $q->where('nivel', $request->nivel);
                });
            }

            if ($request->filled('grado_id')) {
                $query->whereHas('gradoSeccion', function ($q) use ($request) {
                    $q->where('grado_id', $request->grado_id);
                });
            }

            if ($request->filled('search')) {
                $search = $request->search;
                // Reemplazar comas por espacios para evitar problemas si copian y pegan "Apellido, Nombre"
                $cleanSearch = str_replace(',', ' ', $search);
                $words = array_filter(explode(' ', $cleanSearch));

                $query->whereHas('estudiante', function ($q) use ($words) {
                    foreach ($words as $word) {
                        $q->where(function ($sub) use ($word) {
                            $sub->where('nombres', 'ilike', "%{$word}%")
                                ->orWhere('apellido_paterno', 'ilike', "%{$word}%")
                                ->orWhere('apellido_materno', 'ilike', "%{$word}%")
                                ->orWhere('dni', 'ilike', "%{$word}%");
                        });
                    }
                });
            }

            $matriculas = $query->paginate(20)->withQueryString();
        }

        $grados = Grado::orderBy('orden')->get();

        return view('admin.estudiantes.index', compact(
            'matriculas', 'gradoSecciones', 'anoActivo', 'grados'
        ));
    }

    public function store(Request $request)
    {
        $anoActivo = AnoLectivo::where('activo', true)->first();
        if (!$anoActivo) {
            return back()->with('error', 'No hay un año lectivo activo para matricular al estudiante.');
        }

        $request->validate([
            'dni'               => 'required|string|size:8|unique:estudiantes,dni|unique:users,dni',
            'codigo_estudiante' => 'nullable|string|max:20|unique:estudiantes,codigo_estudiante',
            'apellido_paterno'  => 'required|string|max:255',
            'apellido_materno'  => 'required|string|max:255',
            'nombres'           => 'required|string|max:255',
            'fecha_nacimiento'  => 'required|date',
            'sexo'              => 'required|in:M,F',
            'nivel'             => 'required|in:primaria,secundaria',
            'grado_seccion_id'  => 'required|exists:grado_secciones,id',
            'tipo_matricula'    => 'required|in:Normal,Beneficio,Exonerado',
            'apoderado_nombres'          => 'nullable|string|max:255',
            'apoderado_apellido_paterno' => 'nullable|string|max:255',
            'apoderado_apellido_materno' => 'nullable|string|max:255',
            'apoderado_dni'              => 'nullable|string|size:8',
            'apoderado_direccion'        => 'nullable|string|max:255',
            'apoderado_telefono'         => 'nullable|string|max:20',
            'apoderado_parentesco'       => 'nullable|string|max:50',
            'colegio_inicial'            => 'nullable|string|max:255',
            'padre_dni'                  => 'nullable|string|size:8',
            'padre_nombres'              => 'nullable|string|max:255',
            'padre_telefono'             => 'nullable|string|max:20',
            'madre_dni'                  => 'nullable|string|size:8',
            'madre_nombres'              => 'nullable|string|max:255',
            'madre_telefono'             => 'nullable|string|max:20',
        ], [
            'dni.unique'              => 'El DNI ya está registrado en el sistema.',
            'grado_seccion_id.exists' => 'La seccion seleccionada no existe.',
        ]);

        $estudianteExistente = Estudiante::where('dni', $request->dni)->first();
        if ($estudianteExistente) {
            $yaMatriculado = Matricula::where('estudiante_id', $estudianteExistente->id)
                ->where('ano_lectivo_id', $anoActivo->id)
                ->exists();
            if ($yaMatriculado) {
                return back()->withInput()->with('error', 'El estudiante con DNI ' . $request->dni . ' ya se encuentra matriculado en el año lectivo activo.');
            }
        }

        DB::transaction(function () use ($request, $anoActivo) {
            $user = User::create([
                'name'                => mb_strtoupper("{$request->nombres} {$request->apellido_paterno}"),
                'email'               => $request->dni,
                'dni'                 => $request->dni,
                'password'            => Hash::make($request->dni),
                'role_id'             => Role::where('nombre', 'estudiante')->value('id'),
                'must_change_password' => true,
            ]);

            $estudiante = Estudiante::create([
                'user_id'          => $user->id,
                'dni'              => $request->dni,
                'codigo_estudiante'=> $request->codigo_estudiante,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'nombres'          => $request->nombres,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'sexo'             => $request->sexo,
                'nivel'            => $request->nivel,
                'estado'           => 'activo',
                'colegio_inicial'  => $request->colegio_inicial,
            ]);

            // Guardar familiares en tabla apoderados
            if ($request->filled('padre_dni') || $request->filled('padre_nombres')) {
                Apoderado::create([
                    'estudiante_dni'   => $estudiante->dni,
                    'dni'              => $request->padre_dni,
                    'nombres'          => mb_strtoupper($request->padre_nombres ?? ''),
                    'telefono'         => $request->padre_telefono,
                    'parentesco'       => 'PADRE',
                    'es_apoderado'     => false,
                ]);
            }

            if ($request->filled('madre_dni') || $request->filled('madre_nombres')) {
                Apoderado::create([
                    'estudiante_dni'   => $estudiante->dni,
                    'dni'              => $request->madre_dni,
                    'nombres'          => mb_strtoupper($request->madre_nombres ?? ''),
                    'telefono'         => $request->madre_telefono,
                    'parentesco'       => 'MADRE',
                    'es_apoderado'     => false,
                ]);
            }

            Matricula::create([
                'estudiante_id'    => $estudiante->id,
                'grado_seccion_id' => $request->grado_seccion_id,
                'ano_lectivo_id'   => $anoActivo->id,
                'estado'           => 'matriculado',
                'tipo_matricula'   => $request->tipo_matricula,
            ]);

            if ($request->filled('apoderado_dni') && $request->filled('apoderado_nombres')) {
                $esPadre = ($request->padre_dni && $request->apoderado_dni === $request->padre_dni);
                $esMadre = ($request->madre_dni && $request->apoderado_dni === $request->madre_dni);
                
                $parentesco = $request->apoderado_parentesco;
                if ($esPadre) $parentesco = 'PADRE';
                if ($esMadre) $parentesco = 'MADRE';

                Apoderado::updateOrCreate(
                    [
                        'estudiante_dni' => $estudiante->dni,
                        'dni'            => $request->apoderado_dni,
                    ],
                    [
                        'nombres'           => $request->apoderado_nombres,
                        'apellido_paterno'  => $request->apoderado_apellido_paterno,
                        'apellido_materno'  => $request->apoderado_apellido_materno,
                        'direccion'         => $request->apoderado_direccion,
                        'telefono'          => $request->apoderado_telefono,
                        'parentesco'        => mb_strtoupper($parentesco ?? 'OTRO'),
                        'es_apoderado'      => true,
                    ]
                );
            }
        });

        return redirect()->route('admin.estudiantes.index')
            ->with('success', 'Estudiante registrado y matriculado correctamente.');
    }

    public function update(Request $request, Estudiante $estudiante)
    {
        $request->validate([
            'dni' => [
                'required',
                'string',
                'size:8',
                Rule::unique('estudiantes', 'dni')->ignore($estudiante->id),
                Rule::unique('users', 'dni')->ignore($estudiante->user_id),
            ],
            'codigo_estudiante' => 'nullable|string|max:20|unique:estudiantes,codigo_estudiante,' . $estudiante->id,
            'apellido_paterno'  => 'required|string|max:255',
            'apellido_materno'  => 'required|string|max:255',
            'nombres'           => 'required|string|max:255',
            'fecha_nacimiento'  => 'required|date',
            'sexo'              => 'required|in:M,F',
            'tipo_matricula'    => 'nullable|in:Normal,Beneficio,Exonerado',
            'apoderado_nombres'          => 'nullable|string|max:255',
            'apoderado_apellido_paterno' => 'nullable|string|max:255',
            'apoderado_apellido_materno' => 'nullable|string|max:255',
            'apoderado_dni'              => 'nullable|string|size:8',
            'apoderado_direccion'        => 'nullable|string|max:255',
            'apoderado_telefono'         => 'nullable|string|max:20',
            'apoderado_parentesco'       => 'nullable|string|max:50',
            'colegio_inicial'            => 'nullable|string|max:255',
            'padre_dni'                  => 'nullable|string|size:8',
            'padre_nombres'              => 'nullable|string|max:255',
            'padre_telefono'             => 'nullable|string|max:20',
            'madre_dni'                  => 'nullable|string|size:8',
            'madre_nombres'              => 'nullable|string|max:255',
            'madre_telefono'             => 'nullable|string|max:20',
        ], [
            'dni.unique' => 'El DNI ya está registrado en el sistema.',
        ]);

        DB::transaction(function () use ($request, $estudiante) {
            $oldDni = $estudiante->dni;

            $updateData = [
                'dni'               => $request->dni,
                'codigo_estudiante' => $request->codigo_estudiante,
                'apellido_paterno'  => $request->apellido_paterno,
                'apellido_materno'  => $request->apellido_materno,
                'nombres'           => $request->nombres,
                'fecha_nacimiento'  => $request->fecha_nacimiento,
                'sexo'              => $request->sexo,
                'colegio_inicial'   => $request->colegio_inicial,
            ];

            if ($estudiante->estado === 'retirado') {
                $updateData['estado'] = 'matriculado';
                
                $anoActivo = AnoLectivo::where('activo', true)->first();
                if ($anoActivo) {
                    Matricula::where('estudiante_id', $estudiante->id)
                        ->where('ano_lectivo_id', $anoActivo->id)
                        ->update(['estado' => 'matriculado']);
                }
            }

            if ($request->filled('tipo_matricula')) {
                $anoActivo = AnoLectivo::where('activo', true)->first();
                if ($anoActivo) {
                    $matricula = Matricula::where('estudiante_id', $estudiante->id)
                        ->where('ano_lectivo_id', $anoActivo->id)
                        ->first();
                        
                    if ($matricula) {
                        $matricula->update(['tipo_matricula' => $request->tipo_matricula]);

                        // Aplicar retroactivo si se solicita
                        if ($request->boolean('aplicar_retroactivo')) {
                            $nuevoEstado = match ($request->tipo_matricula) {
                                'Beneficio' => 'BENEFICIADO',
                                'Exonerado' => 'EXONERADO',
                                default     => 'DEBE',
                            };

                            \App\Models\Mensualidad::where('matricula_id', $matricula->id)
                                ->where('estado', '!=', 'PAGÓ') // Idealmente no afectar a los que ya pagaron
                                ->update(['estado' => $nuevoEstado]);
                        }
                    }
                }
            }

            $estudiante->update($updateData);

            if ($estudiante->user) {
                $userUpdate = [
                    'name' => mb_strtoupper("{$request->nombres} {$request->apellido_paterno}"),
                    'dni'  => $request->dni,
                ];

                if ($estudiante->user->email === $oldDni) {
                    $userUpdate['email'] = $request->dni;
                }

                if ($estudiante->user->must_change_password) {
                    $userUpdate['password'] = Hash::make($request->dni);
                }

                $estudiante->user->update($userUpdate);
            }

            if ($request->filled('padre_dni') || $request->filled('padre_nombres')) {
                $padreUpdateData = [
                    'nombres'          => mb_strtoupper($request->padre_nombres ?? ''),
                    'telefono'         => $request->padre_telefono,
                    'parentesco'       => 'PADRE',
                ];
                if ($request->filled('padre_dni')) {
                    Apoderado::updateOrCreate(
                        ['estudiante_dni' => $estudiante->dni, 'dni' => $request->padre_dni],
                        $padreUpdateData
                    );
                } else {
                    Apoderado::updateOrCreate(
                        ['estudiante_dni' => $estudiante->dni, 'parentesco' => 'PADRE'],
                        $padreUpdateData
                    );
                }
            } else {
                Apoderado::where('estudiante_dni', $estudiante->dni)->where('parentesco', 'PADRE')->where('es_apoderado', false)->delete();
            }

            if ($request->filled('madre_dni') || $request->filled('madre_nombres')) {
                $madreUpdateData = [
                    'nombres'          => mb_strtoupper($request->madre_nombres ?? ''),
                    'telefono'         => $request->madre_telefono,
                    'parentesco'       => 'MADRE',
                ];
                if ($request->filled('madre_dni')) {
                    Apoderado::updateOrCreate(
                        ['estudiante_dni' => $estudiante->dni, 'dni' => $request->madre_dni],
                        $madreUpdateData
                    );
                } else {
                    Apoderado::updateOrCreate(
                        ['estudiante_dni' => $estudiante->dni, 'parentesco' => 'MADRE'],
                        $madreUpdateData
                    );
                }
            } else {
                Apoderado::where('estudiante_dni', $estudiante->dni)->where('parentesco', 'MADRE')->where('es_apoderado', false)->delete();
            }

            if ($request->filled('apoderado_dni') && $request->filled('apoderado_nombres')) {
                $esPadre = ($request->padre_dni && $request->apoderado_dni === $request->padre_dni);
                $esMadre = ($request->madre_dni && $request->apoderado_dni === $request->madre_dni);
                
                $parentesco = $request->apoderado_parentesco;
                if ($esPadre) $parentesco = 'PADRE';
                if ($esMadre) $parentesco = 'MADRE';

                // Resetear otros apoderados
                Apoderado::where('estudiante_dni', $estudiante->dni)->update(['es_apoderado' => false]);

                Apoderado::updateOrCreate(
                    ['estudiante_dni' => $estudiante->dni, 'dni' => $request->apoderado_dni],
                    [
                        'nombres'           => $request->apoderado_nombres,
                        'apellido_paterno'  => $request->apoderado_apellido_paterno,
                        'apellido_materno'  => $request->apoderado_apellido_materno,
                        'direccion'         => $request->apoderado_direccion,
                        'telefono'          => $request->apoderado_telefono,
                        'parentesco'        => mb_strtoupper($parentesco ?? 'OTRO'),
                        'es_apoderado'      => true,
                    ]
                );
            }
        });

        return back()->with('success', 'Datos del estudiante actualizados correctamente.');
    }

    public function mover(Request $request, Estudiante $estudiante)
    {
        $request->validate([
            'grado_seccion_id' => 'required|exists:grado_secciones,id',
        ]);

        $anoActivo = AnoLectivo::where('activo', true)->firstOrFail();

        $matricula = Matricula::where('estudiante_id', $estudiante->id)
            ->where('ano_lectivo_id', $anoActivo->id)
            ->firstOrFail();

        $seccionActual = $matricula->gradoSeccion;
        $nuevaSeccion = GradoSeccion::findOrFail($request->grado_seccion_id);

        if ($seccionActual->grado_id !== $nuevaSeccion->grado_id) {
            return back()->with('error', 'El estudiante solo puede ser movido a otra sección del mismo grado.');
        }

        if ($seccionActual->id === $nuevaSeccion->id) {
            return back()->with('error', 'El estudiante ya se encuentra en esta sección.');
        }

        $matricula->update(['grado_seccion_id' => $nuevaSeccion->id]);

        return back()->with('success', 'El estudiante ha sido movido de sección correctamente.');
    }

    public function retirar(Request $request, Estudiante $estudiante)
    {
        $anoActivo = AnoLectivo::where('activo', true)->firstOrFail();

        $matricula = Matricula::where('estudiante_id', $estudiante->id)
            ->where('ano_lectivo_id', $anoActivo->id)
            ->firstOrFail();

        $matricula->update(['estado' => 'retirado']);
        $estudiante->update(['estado' => 'retirado']);

        return back()->with('success', 'El estudiante ha sido retirado del año lectivo.');
    }
}
