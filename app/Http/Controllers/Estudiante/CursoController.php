<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDocente;
use App\Models\Bimestre;
use App\Models\Estudiante;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $estudiante = Estudiante::with([
            'matriculas' => function ($q) {
                $q->whereHas('anoLectivo', fn ($a) => $a->where('activo', true));
            },
            'matriculas.gradoSeccion.grado',
            'matriculas.gradoSeccion.seccion',
            'matriculas.gradoSeccion.tutor.cursos',
            'matriculas.gradoSeccion.cotutor.cursos',
            'matriculas.gradoSeccion.asignacionesDocente.docente',
            'matriculas.gradoSeccion.asignacionesDocente.curso',
            'matriculas.anoLectivo',
        ])->where('user_id', $user->id)->first();

        $matriculaActiva = $estudiante?->matriculas->first();

        $docentes = collect();

        if ($matriculaActiva) {
            $gs = $matriculaActiva->gradoSeccion;
            $nivel = $gs->grado->nivel;

            if ($nivel === 'primaria') {
                $asignacionGeneral = AsignacionDocente::where('grado_seccion_id', $gs->id)
                    ->whereNull('curso_id')
                    ->first();

                // Si no hay asignación general (curso_id null), usar cualquier asignación del tutor (ej: Tutoría)
                if (!$asignacionGeneral && $gs->tutor_id) {
                    $asignacionGeneral = AsignacionDocente::where('grado_seccion_id', $gs->id)
                        ->where('docente_id', $gs->tutor_id)
                        ->first();
                }

                $cursosPrimaria = \App\Models\Curso::where('activo', true)
                    ->soloCursos()
                    ->whereIn('nivel', ['primaria', 'ambos'])
                    ->orderBy('nombre')
                    ->get();

                foreach ($cursosPrimaria as $curso) {
                    $asignacionEspecifica = AsignacionDocente::where('grado_seccion_id', $gs->id)
                        ->where('curso_id', $curso->id)
                        ->first();

                    if ($asignacionEspecifica) {
                        $docentes->push([
                            'tipo'         => 'Docente',
                            'docente'      => $asignacionEspecifica->docente,
                            'curso'        => $curso,
                            'asignacion_id' => $asignacionEspecifica->id,
                        ]);
                    } elseif ($asignacionGeneral) {
                        $docentes->push([
                            'tipo'         => 'Docente',
                            'docente'      => $asignacionGeneral->docente,
                            'curso'        => $curso,
                            'asignacion_id' => $asignacionGeneral->id,
                        ]);
                    } else {
                        $docentes->push([
                            'tipo'         => 'Sin asignar',
                            'docente'      => null,
                            'curso'        => $curso,
                            'asignacion_id' => 0,
                        ]);
                    }
                }
            } else {
                if ($gs->tutor) {
                    $asignTutor = AsignacionDocente::where('docente_id', $gs->tutor_id)
                        ->where('grado_seccion_id', $gs->id)
                        ->first();

                    $docentes->push([
                        'tipo'         => 'Tutor',
                        'docente'      => $gs->tutor,
                        'curso'        => $asignTutor ? $asignTutor->curso : null,
                        'asignacion_id' => $asignTutor?->id,
                    ]);
                }

                if ($gs->cotutor) {
                    $asignCotutor = AsignacionDocente::where('docente_id', $gs->cotutor_id)
                        ->where('grado_seccion_id', $gs->id)
                        ->first();

                    $docentes->push([
                        'tipo'         => 'Co-tutor',
                        'docente'      => $gs->cotutor,
                        'curso'        => $asignCotutor ? $asignCotutor->curso : null,
                        'asignacion_id' => $asignCotutor?->id,
                    ]);
                }

                foreach ($gs->asignacionesDocente as $asignacion) {
                    // Solo agregar si tiene un curso y no ha sido agregado previamente
                    if ($asignacion->curso) {
                        $yaAgregado = $docentes->contains(fn ($d) =>
                            $d['curso'] && $d['curso']->id === $asignacion->curso->id
                        );
                        if (!$yaAgregado) {
                            $docentes->push([
                                'tipo'         => 'Docente',
                                'docente'      => $asignacion->docente,
                                'curso'        => $asignacion->curso,
                                'asignacion_id' => $asignacion->id,
                            ]);
                        }
                    }
                }
            }
        }

        return view('estudiante.cursos.index', compact('matriculaActiva', 'docentes'));
    }

    public function show($asignacionId, Request $request, \App\Models\Curso $curso = null)
    {
        $user = auth()->user();

        $estudiante = Estudiante::where('user_id', $user->id)->firstOrFail();

        $matriculaActiva = $estudiante->matriculas()
            ->whereHas('anoLectivo', fn ($q) => $q->where('activo', true))
            ->first();

        abort_if(!$matriculaActiva, 403, 'No tienes matrícula activa.');

        $asignacion = null;
        if ($asignacionId && $asignacionId !== '0') {
            $asignacion = AsignacionDocente::with([
                'docente',
                'curso',
                'gradoSeccion.grado',
                'gradoSeccion.seccion',
                'anoLectivo'
            ])->findOrFail($asignacionId);
            
            abort_if($asignacion->grado_seccion_id !== $matriculaActiva->grado_seccion_id, 403, 'No tienes acceso a este curso.');
        }

        $cursoId = $curso ? $curso->id : ($asignacion ? $asignacion->curso_id : null);
        abort_if(!$cursoId && !$asignacion, 404, 'Curso no encontrado.');

        if (!$curso && $cursoId) {
            $curso = \App\Models\Curso::find($cursoId);
        }

        $bimestres = Bimestre::where('ano_lectivo_id', $matriculaActiva->ano_lectivo_id)
            ->orderBy('numero')
            ->get();
            
        // También cargar las notas bimestrales para este estudiante y este curso
        $estudiante->load(['notasBimestrales' => function($q) use ($cursoId) {
            if ($cursoId) {
                $q->where('curso_id', $cursoId);
            }
        }, 'notasBimestrales.competencia']);

        $bimestreFiltro = $request->input('bimestre');

        return view('estudiante.cursos.show', compact(
            'asignacion',
            'curso',
            'estudiante',
            'bimestres',
            'bimestreFiltro',
            'matriculaActiva'
        ));
    }
}
