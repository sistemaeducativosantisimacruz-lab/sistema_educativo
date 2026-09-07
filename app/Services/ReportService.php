<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AnoLectivo;
use App\Models\Mensualidad;
use App\Models\Docente;
use App\Models\Curso;
use App\Models\GradoSeccion;
use App\Models\Estudiante;

class ReportService
{
    public function getNotasResumen(AnoLectivo $anoActivo, Request $request)
    {
        $query = DB::table('notas_bimestrales')
            ->join('estudiantes', 'notas_bimestrales.estudiante_id', '=', 'estudiantes.id')
            ->join('matriculas', 'estudiantes.id', '=', 'matriculas.estudiante_id')
            ->join('grado_secciones', 'matriculas.grado_seccion_id', '=', 'grado_secciones.id')
            ->join('grados', 'grado_secciones.grado_id', '=', 'grados.id')
            ->join('cursos', 'notas_bimestrales.curso_id', '=', 'cursos.id')
            ->join('competencias', 'notas_bimestrales.competencia_id', '=', 'competencias.id')
            ->where('matriculas.ano_lectivo_id', $anoActivo->id)
            ->whereNotNull('notas_bimestrales.nota')
            ->select(
                'cursos.nombre as curso_nombre',
                'cursos.codigo as curso_codigo',
                'competencias.nombre as competencia_nombre',
                'competencias.orden as competencia_orden',
                'competencias.id as competencia_id',
                'notas_bimestrales.nota as promedio_letra',
                DB::raw('count(*) as cantidad')
            );

        if ($request->filled('nivel')) {
            $query->where('grados.nivel', $request->nivel);
        }

        if ($request->filled('grado_seccion_id')) {
            $query->where('matriculas.grado_seccion_id', $request->grado_seccion_id);
        }

        if ($request->filled('bimestre_id')) {
            $query->where('notas_bimestrales.bimestre_id', $request->bimestre_id);
        }

        return $query->groupBy(
                            'cursos.nombre', 
                            'cursos.codigo', 
                            'competencias.nombre', 
                            'competencias.orden', 
                            'competencias.id', 
                            'notas_bimestrales.nota'
                         )
                         ->orderBy('cursos.nombre')
                         ->orderBy('competencias.orden')
                         ->orderBy('competencias.id')
                         ->get();
    }

    public function getDeudasReport(AnoLectivo $anoActivo, Request $request)
    {
        $deudasQuery = Mensualidad::with([
            'matricula.estudiante',
            'matricula.gradoSeccion.grado',
            'matricula.gradoSeccion.seccion'
        ])
        ->whereHas('matricula', function ($q) use ($anoActivo) {
            $q->where('ano_lectivo_id', $anoActivo->id);
        });

        $estado = $request->input('estado', 'DEBE');
        if ($estado !== 'TODOS') {
            $deudasQuery->where('mensualidades.estado', $estado);
        }

        if ($request->filled('nivel')) {
            $deudasQuery->whereHas('matricula.gradoSeccion.grado', function($q) use ($request) {
                $q->where('nivel', $request->nivel);
            });
        }

        if ($request->filled('grado_seccion_id')) {
            $deudasQuery->whereHas('matricula', function($q) use ($request) {
                $q->where('grado_seccion_id', $request->grado_seccion_id);
            });
        }

        if ($request->filled('mes')) {
            $deudasQuery->where('mensualidades.mes', $request->mes);
        }

        return $deudasQuery->join('matriculas', 'mensualidades.matricula_id', '=', 'matriculas.id')
                              ->join('estudiantes', 'matriculas.estudiante_id', '=', 'estudiantes.id')
                              ->join('grado_secciones', 'matriculas.grado_seccion_id', '=', 'grado_secciones.id')
                              ->join('grados', 'grado_secciones.grado_id', '=', 'grados.id')
                              ->join('secciones', 'grado_secciones.seccion_id', '=', 'secciones.id')
                              ->orderBy('grados.nivel')
                              ->orderBy('grados.orden')
                              ->orderBy('secciones.nombre')
                              ->orderBy('estudiantes.apellido_paterno')
                              ->orderBy('estudiantes.apellido_materno')
                              ->orderBy('estudiantes.nombres')
                              ->orderBy('mensualidades.anio', 'desc')
                              ->orderBy('mensualidades.mes', 'desc')
                              ->select('mensualidades.*')
                              ->paginate(35)
                              ->appends($request->all());
    }

    public function getDocentesReport(AnoLectivo $anoActivo, Request $request)
    {
        $docentesQuery = Docente::with([
            'asignaciones' => function($q) use ($anoActivo) {
                $q->where('ano_lectivo_id', $anoActivo->id)
                  ->with(['gradoSeccion.grado', 'gradoSeccion.seccion', 'curso']);
            },
            'cursos',
            'tutoriaSecciones' => function($q) use ($anoActivo) {
                $q->where('ano_lectivo_id', $anoActivo->id)
                  ->with(['grado', 'seccion']);
            }
        ]);

        if ($request->filled('docente_nivel')) {
            $docentesQuery->where('docentes.nivel', $request->docente_nivel);
        }

        if ($request->filled('curso_id')) {
            $cursoId = $request->curso_id;
            $curso = Curso::find($cursoId);
            if ($curso) {
                $docentesQuery->where(function($q) use ($cursoId, $curso, $anoActivo) {
                    $q->whereHas('cursos', function($sub) use ($cursoId) {
                        $sub->where('cursos.id', $cursoId);
                    })
                    ->orWhereHas('asignaciones', function($sub) use ($cursoId, $anoActivo) {
                        $sub->where('ano_lectivo_id', $anoActivo->id)
                            ->where('curso_id', $cursoId);
                    })
                    ->orWhere('curso_id', $cursoId)
                    ->orWhere(function($sub) use ($curso, $anoActivo) {
                        $sub->where('tipo', 'polidocente')
                            ->where('nivel', $curso->nivel !== 'ambos' ? $curso->nivel : '!=', 'none')
                            ->whereHas('asignaciones', function($sub2) use ($anoActivo) {
                                $sub2->where('ano_lectivo_id', $anoActivo->id)
                                     ->whereNull('curso_id');
                            });
                    });
                });
            }
        }

        if ($request->boolean('solo_tutores')) {
            $docentesQuery->whereIn('docentes.id', function($q) use ($anoActivo) {
                $q->select('tutor_id')
                  ->from('grado_secciones')
                  ->where('ano_lectivo_id', $anoActivo->id)
                  ->whereNotNull('tutor_id');
            });
        }

        return $docentesQuery->get();
    }

    public function getSeccionesReport(AnoLectivo $anoActivo, Request $request)
    {
        $seccionesQuery = GradoSeccion::with(['grado', 'seccion', 'tutor', 'cotutor'])
            ->join('secciones', 'grado_secciones.seccion_id', '=', 'secciones.id')
            ->join('grados as g_ord', 'grado_secciones.grado_id', '=', 'g_ord.id')
            ->where('grado_secciones.ano_lectivo_id', $anoActivo->id)
            ->withCount(['matriculas' => function($q) {
                $q->where('estado', '!=', 'retirado');
            }])
            ->orderBy('g_ord.orden')
            ->orderBy('secciones.nombre')
            ->select('grado_secciones.*');

        if ($request->filled('nivel')) {
            $seccionesQuery->where('g_ord.nivel', $request->nivel);
        }
        if ($request->filled('grado_id')) {
            $seccionesQuery->where('g_ord.id', $request->grado_id);
        }
        if ($request->filled('seccion_id')) {
            $seccionesQuery->where('secciones.id', $request->seccion_id);
        }

        return $seccionesQuery->get();
    }

    public function getEstudiantesList(AnoLectivo $anoActivo, Request $request)
    {
        $estudiantesQuery = Estudiante::whereHas('matriculas', function($q) use ($anoActivo) {
            $q->where('ano_lectivo_id', $anoActivo->id);
        });
        
        if ($request->filled('grado_seccion_id')) {
            $estudiantesQuery->whereHas('matriculas', function($q) use ($request, $anoActivo) {
                $q->where('ano_lectivo_id', $anoActivo->id)
                  ->where('grado_seccion_id', $request->grado_seccion_id);
            });
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $estudiantesQuery->where(function($q) use ($search) {
                $q->where('nombres', 'ilike', "%{$search}%")
                  ->orWhere('apellido_paterno', 'ilike', "%{$search}%")
                  ->orWhere('apellido_materno', 'ilike', "%{$search}%")
                  ->orWhere('dni', 'ilike', "%{$search}%")
                  ->orWhere('codigo_estudiante', 'ilike', "%{$search}%");
            });
        }

        return $estudiantesQuery->orderBy('apellido_paterno')
                                ->orderBy('apellido_materno')
                                ->orderBy('nombres')
                                ->paginate(20)
                                ->appends($request->all());
    }
}
