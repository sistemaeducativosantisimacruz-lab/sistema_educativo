<?php

namespace App\Services;

use App\Models\GradoSeccion;
use App\Models\Docente;
use App\Models\Curso;
use App\Models\AsignacionDocente;
use Illuminate\Support\Facades\DB;

class GradoSeccionService
{
    /**
     * Asigna tutor y co-tutor a una sección, y gestiona automáticamente 
     * los cursos de tutoría o especialidades de primaria polidocente.
     */
    public function asignarTutoresYCursos(GradoSeccion $gradoSeccione, ?int $nuevoTutorId, ?int $nuevoCotutorId): array
    {
        $grado = $gradoSeccione->grado;

        // Validar nivel del tutor
        if ($nuevoTutorId) {
            $tutor = Docente::find($nuevoTutorId);
            if ($tutor && $tutor->nivel !== $grado->nivel) {
                throw new \Exception('El docente tutor debe pertenecer al mismo nivel educativo que la sección.');
            }

            $existeTutor = GradoSeccion::where('ano_lectivo_id', $gradoSeccione->ano_lectivo_id)
                ->where('tutor_id', $nuevoTutorId)
                ->where('id', '!=', $gradoSeccione->id)
                ->exists();

            if ($existeTutor) {
                throw new \Exception('El docente seleccionado ya está asignado como tutor en otra sección.');
            }
        }

        // Validar nivel del co-tutor
        if ($nuevoCotutorId) {
            $cotutor = Docente::find($nuevoCotutorId);
            if ($cotutor && $cotutor->nivel !== $grado->nivel) {
                throw new \Exception('El co-tutor debe pertenecer al mismo nivel educativo que la sección.');
            }
        }

        DB::beginTransaction();

        try {
            $cursoTutoria = Curso::where(function ($q) use ($grado) {
                    $q->whereRaw('LOWER(nombre) LIKE ?', ['%tutoría%'])
                      ->orWhereRaw('LOWER(nombre) LIKE ?', ['%tutoria%']);
                })
                ->where(function ($q) use ($grado) {
                    $q->where('nivel', $grado->nivel)->orWhere('nivel', 'ambos');
                })
                ->first();

            $tutorAnteriorId  = $gradoSeccione->tutor_id;
            $tutorCambio      = $tutorAnteriorId !== $nuevoTutorId;

            if ($cursoTutoria && $tutorCambio) {
                if ($tutorAnteriorId) {
                    AsignacionDocente::where('docente_id', $tutorAnteriorId)
                        ->where('grado_seccion_id', $gradoSeccione->id)
                        ->where('curso_id', $cursoTutoria->id)
                        ->where('ano_lectivo_id', $gradoSeccione->ano_lectivo_id)
                        ->delete();
                }

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

            if ($cursoTutoria && $nuevoTutorId) {
                $tutorModel = \App\Models\Docente::find($nuevoTutorId);
                if ($tutorModel) {
                    $tutorModel->cursos()->syncWithoutDetaching([$cursoTutoria->id]);
                }
            }

            if ($grado && $grado->nivel === 'primaria') {
                if (in_array($grado->orden, [1, 2, 3, 4])) {
                    if ($tutorCambio && $tutorAnteriorId) {
                        AsignacionDocente::where('docente_id', $tutorAnteriorId)
                            ->where('grado_seccion_id', $gradoSeccione->id)
                            ->where('ano_lectivo_id', $gradoSeccione->ano_lectivo_id)
                            ->whereNull('curso_id')
                            ->delete();
                    }

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
                } elseif (in_array($grado->orden, [5, 6])) {
                    $cursosAuto = Curso::where(function ($q) {
                        $q->whereRaw('LOWER(nombre) LIKE ?', ['%personal social%'])
                          ->orWhereRaw('LOWER(nombre) LIKE ?', ['%religi%'])
                          ->orWhereRaw('LOWER(nombre) LIKE ?', ['%arte%']);
                    })
                    ->where(function ($q) use ($grado) {
                        $q->where('nivel', $grado->nivel)->orWhere('nivel', 'ambos');
                    })
                    ->pluck('id')
                    ->toArray();

                    if ($tutorCambio && $tutorAnteriorId) {
                        $tutorAnteriorModel = \App\Models\Docente::find($tutorAnteriorId);
                        $cursosEspecialidadAnt = $tutorAnteriorModel ? $tutorAnteriorModel->cursos()->pluck('cursos.id')->toArray() : [];
                        $cursosARemover = array_unique(array_merge($cursosAuto, $cursosEspecialidadAnt));

                        if (!empty($cursosARemover)) {
                            AsignacionDocente::where('docente_id', $tutorAnteriorId)
                                ->where('grado_seccion_id', $gradoSeccione->id)
                                ->where('ano_lectivo_id', $gradoSeccione->ano_lectivo_id)
                                ->whereIn('curso_id', $cursosARemover)
                                ->delete();
                        }
                    }

                    if ($nuevoTutorId) {
                        $tutorModel = \App\Models\Docente::find($nuevoTutorId);
                        $cursosEspecialidad = $tutorModel ? $tutorModel->cursos()->pluck('cursos.id')->toArray() : [];
                        $cursosAAsignar = array_unique(array_merge($cursosAuto, $cursosEspecialidad));

                        foreach ($cursosAAsignar as $cId) {
                            AsignacionDocente::where('grado_seccion_id', $gradoSeccione->id)
                                ->where('ano_lectivo_id', $gradoSeccione->ano_lectivo_id)
                                ->where('curso_id', $cId)
                                ->where('docente_id', '!=', $nuevoTutorId)
                                ->delete();

                            AsignacionDocente::firstOrCreate(
                                [
                                    'docente_id'       => $nuevoTutorId,
                                    'grado_seccion_id' => $gradoSeccione->id,
                                    'ano_lectivo_id'   => $gradoSeccione->ano_lectivo_id,
                                    'curso_id'         => $cId,
                                ],
                                ['activo' => true]
                            );

                            if ($tutorModel) {
                                $tutorModel->cursos()->syncWithoutDetaching([$cId]);
                            }
                        }
                    }
                }
            }

            $gradoSeccione->update([
                'tutor_id'   => $nuevoTutorId,
                'cotutor_id' => $nuevoCotutorId,
            ]);

            DB::commit();

            $mensaje = 'Tutores asignados correctamente a la sección.';
            if ($tutorCambio && $tutorAnteriorId && $nuevoTutorId) {
                $mensaje = 'Tutor actualizado. El docente anterior ya no verá el curso de Tutoría de esta sección, pero los datos de los alumnos se conservan intactos.';
            }

            return ['success' => true, 'mensaje' => $mensaje];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
