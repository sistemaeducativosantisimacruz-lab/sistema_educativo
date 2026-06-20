<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnoLectivo;
use App\Models\GradoSeccion;

use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Docente;
use App\Models\Mensualidad;

class RendimientoController extends Controller
{
    public function index(Request $request)
    {
        $anoActivo = AnoLectivo::where('activo', true)->first();
        $tab = $request->query('tab', 'notas');
        
        $gradoSecciones = collect();
        $bimestres = collect();
        $cursos = Curso::where('activo', true)->soloCursos()->get();
        
        $resumen = collect();
        $deudas = collect();
        $docentesReport = collect();
        $seccionesReport = collect();
        $estudiantesList = collect();

        if ($anoActivo) {
            $gradoSecciones = GradoSeccion::join('grados', 'grado_secciones.grado_id', '=', 'grados.id')
                ->join('secciones', 'grado_secciones.seccion_id', '=', 'secciones.id')
                ->where('grado_secciones.ano_lectivo_id', $anoActivo->id)
                ->orderBy('grados.nivel')
                ->orderBy('grados.orden')
                ->orderBy('secciones.nombre')
                ->select('grado_secciones.*')
                ->with(['grado', 'seccion'])
                ->get();

            $bimestres = \App\Models\Bimestre::where('ano_lectivo_id', $anoActivo->id)
                ->orderBy('numero')
                ->get();

            $niveles = DB::table('grados')->distinct()->orderBy('nivel')->pluck('nivel');

            // 1. Notas Report grouped by Course and Competency
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

            $resumen = $query->groupBy(
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

            // 2. Deudas Report
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

            $deudas = $deudasQuery->join('matriculas', 'mensualidades.matricula_id', '=', 'matriculas.id')
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

            // 3. Docentes Report
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

            $docentesReport = $docentesQuery->get();

            // 4. Grados/Secciones Report
            $seccionesQuery = GradoSeccion::with(['grado', 'seccion', 'tutor', 'cotutor'])
                ->join('secciones', 'grado_secciones.seccion_id', '=', 'secciones.id')
                ->join('grados as g_ord', 'grado_secciones.grado_id', '=', 'g_ord.id')
                ->where('grado_secciones.ano_lectivo_id', $anoActivo->id)
                ->withCount('matriculas')
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

            $seccionesReport = $seccionesQuery->get();

            // 5. Estudiantes para Reporte Individual
            if ($tab === 'estudiantes') {
                $estudiantesQuery = \App\Models\Estudiante::whereHas('matriculas', function($q) use ($anoActivo) {
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

                $estudiantesList = $estudiantesQuery->orderBy('apellido_paterno')->orderBy('apellido_materno')->orderBy('nombres')->paginate(20);
            }
        }

        $niveles = $niveles ?? collect();

        return view('admin.rendimiento.index', compact(
            'resumen', 
            'gradoSecciones', 
            'bimestres',
            'niveles',
            'cursos', 
            'anoActivo', 
            'deudas', 
            'docentesReport', 
            'seccionesReport', 
            'estudiantesList',
            'tab'
        ));
    }

    public function exportar(Request $request)
    {
        $anoActivo = AnoLectivo::where('activo', true)->first();
        if (!$anoActivo) {
            return back()->with('error', 'No hay un año lectivo activo configurado.');
        }

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

        $resumen = $query->groupBy(
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

        $sheetTitle = 'Notas';
        if ($request->filled('grado_seccion_id')) {
            $gs = \App\Models\GradoSeccion::with(['grado', 'seccion'])->find($request->grado_seccion_id);
            if ($gs) {
                // E.g. "1ro Secundaria" -> "1"
                $gradeName = $gs->grado->nombre;
                preg_match('/^\d+/', $gradeName, $matches);
                $gradeNum = $matches[0] ?? '';
                
                // E.g. "C" -> "C"
                $secName = strtoupper(trim($gs->seccion->nombre));
                
                // E.g. "secundaria" -> "SEC"
                $levelAbbr = '';
                $level = strtolower($gs->grado->nivel);
                if ($level === 'primaria') {
                    $levelAbbr = 'PRI';
                } elseif ($level === 'secundaria') {
                    $levelAbbr = 'SEC';
                }
                
                if ($gradeNum !== '' && $secName !== '' && $levelAbbr !== '') {
                    $sheetTitle = $gradeNum . $secName . '-' . $levelAbbr;
                } else {
                    $sheetTitle = substr(str_replace([' ', '/', '\\', '?', '*', '[', ']'], '', $gradeName . '-' . $secName), 0, 31);
                }
            }
        }

        if ($resumen->isEmpty()) {
            return back()->with('error', 'No hay datos de calificaciones para exportar con los filtros seleccionados.');
        }

        $fileName = 'Reporte_Notas_' . ($sheetTitle !== 'Notas' ? $sheetTitle . '_' : '') . date('Ymd_His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\NotasReportExport($resumen, $sheetTitle), $fileName);
    }

    public function exportarDocentes(Request $request)
    {
        $anoActivo = AnoLectivo::where('activo', true)->first();
        if (!$anoActivo) {
            return back()->with('error', 'No hay un año lectivo activo configurado.');
        }

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

        $docentes = $docentesQuery->get();

        if ($docentes->isEmpty()) {
            return back()->with('error', 'No hay datos de docentes para exportar con los filtros seleccionados.');
        }

        $fileName = 'Reporte_Docentes_' . date('Ymd_His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DocentesReportExport($docentes), $fileName);
    }

    public function exportarReporteEstudiante(Request $request)
    {
        $request->validate([
            'estudiante_id' => 'required|exists:estudiantes,id',
        ]);

        $anoActivo = AnoLectivo::where('activo', true)->first();
        if (!$anoActivo) {
            return back()->with('error', 'No hay un año lectivo activo configurado.');
        }

        $estudiante = \App\Models\Estudiante::findOrFail($request->estudiante_id);
        $fileName = 'Reporte_Estudiante_' . $estudiante->dni . '_' . date('Ymd_His') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ReporteBimestralEstudianteExport($estudiante->id, $anoActivo->id), 
            $fileName
        );
    }

    public function exportarCriticos(Request $request)
    {
        $request->validate([
            'notas_seleccionadas' => 'required|array|min:1',
        ]);
        
        $anoActivo = AnoLectivo::where('activo', true)->first();
        if (!$anoActivo) {
            return back()->with('error', 'No hay un año lectivo activo configurado.');
        }

        $queryCursos = DB::table('cursos');
        if (!$request->boolean('todos_cursos') && $request->filled('cursos_seleccionados')) {
            $queryCursos->whereIn('id', $request->cursos_seleccionados);
        }
        $cursosFiltro = $queryCursos->select('id', 'nombre', 'codigo')->orderBy('nombre')->get();

        if ($cursosFiltro->isEmpty()) {
            return back()->with('error', 'No se encontraron cursos con los filtros seleccionados.');
        }

        $competencias = DB::table('competencias')
            ->whereIn('curso_id', $cursosFiltro->pluck('id'))
            ->orderBy('orden')
            ->get();

        $notasSeleccionadas = $request->notas_seleccionadas;
        $archivosGenerados = [];
        $dividirSecciones = $request->boolean('dividir_secciones');

        foreach ($notasSeleccionadas as $notaActual) {
            $queryCriticos = DB::table('notas_bimestrales')
                ->join('estudiantes', 'notas_bimestrales.estudiante_id', '=', 'estudiantes.id')
                ->join('matriculas', 'estudiantes.id', '=', 'matriculas.estudiante_id')
                ->join('grado_secciones', 'matriculas.grado_seccion_id', '=', 'grado_secciones.id')
                ->join('grados', 'grado_secciones.grado_id', '=', 'grados.id')
                ->join('secciones', 'grado_secciones.seccion_id', '=', 'secciones.id')
                ->where('matriculas.ano_lectivo_id', $anoActivo->id)
                ->where('notas_bimestrales.nota', $notaActual)
                ->whereIn('notas_bimestrales.curso_id', $cursosFiltro->pluck('id'));

            $bimestreStr = '';
            if ($request->filled('bimestre_id')) {
                $queryCriticos->where('notas_bimestrales.bimestre_id', $request->bimestre_id);
                $bimestreObj = \App\Models\Bimestre::find($request->bimestre_id);
                if ($bimestreObj) {
                    $bimestreStr = 'B' . $bimestreObj->numero . '_';
                }
            }

            if ($request->filled('nivel')) {
                $queryCriticos->where('grados.nivel', $request->nivel);
            }

            $criticos = $queryCriticos->select(
                    'notas_bimestrales.curso_id',
                    'notas_bimestrales.competencia_id',
                    'notas_bimestrales.nota as nota_logro',
                    'notas_bimestrales.conclusion_descriptiva',
                    'estudiantes.dni',
                    'estudiantes.nombres',
                    'estudiantes.apellido_paterno',
                    'estudiantes.apellido_materno',
                    'grados.nombre as grado_nombre',
                    'secciones.nombre as seccion_nombre',
                    'grados.nivel as grado_nivel',
                    'grados.orden as grado_orden'
                )
                ->distinct()
                ->orderBy('grados.nivel')
                ->orderBy('grados.orden')
                ->orderBy('secciones.nombre')
                ->orderBy('estudiantes.apellido_paterno')
                ->orderBy('estudiantes.apellido_materno')
                ->orderBy('estudiantes.nombres')
                ->get();

            $dataExport = collect();

            if ($dividirSecciones) {
                $criticosPorSeccion = $criticos->groupBy(function ($item) {
                    return $item->grado_nombre . ' - ' . $item->seccion_nombre;
                });

                foreach ($criticosPorSeccion as $seccionKey => $estudiantesSeccion) {
                    $seccionData = (object) [
                        'seccion_nombre' => $seccionKey,
                        'cursos' => collect()
                    ];

                    foreach ($cursosFiltro as $curso) {
                        $cursoData = (object) [
                            'curso_nombre' => $curso->nombre,
                            'competencias' => collect()
                        ];
                        
                        $compsCurso = $competencias->where('curso_id', $curso->id);
                        $hasAnyStudent = false;

                        foreach ($compsCurso as $comp) {
                            $estudiantesComp = $estudiantesSeccion->where('curso_id', $curso->id)
                                                                  ->where('competencia_id', $comp->id)
                                                                  ->values();
                            
                            $cursoData->competencias->push((object) [
                                'competencia_nombre' => $comp->nombre,
                                'estudiantes' => $estudiantesComp
                            ]);

                            if ($estudiantesComp->isNotEmpty()) {
                                $hasAnyStudent = true;
                            }
                        }

                        if ($hasAnyStudent) {
                            $seccionData->cursos->push($cursoData);
                        }
                    }
                    
                    if ($seccionData->cursos->isNotEmpty()) {
                        $dataExport->push($seccionData);
                    }
                }
            } else {
                foreach ($cursosFiltro as $curso) {
                    $cursoData = (object) [
                        'curso_id' => $curso->id,
                        'curso_nombre' => $curso->nombre,
                        'curso_codigo' => $curso->codigo,
                        'competencias' => collect()
                    ];
                    
                    $compsCurso = $competencias->where('curso_id', $curso->id);
                    $hasAnyStudent = false;

                    foreach ($compsCurso as $comp) {
                        $estudiantesComp = $criticos->where('curso_id', $curso->id)
                                                    ->where('competencia_id', $comp->id)
                                                    ->values();
                        
                        $cursoData->competencias->push((object) [
                            'competencia_nombre' => $comp->nombre,
                            'estudiantes' => $estudiantesComp
                        ]);

                        if ($estudiantesComp->isNotEmpty()) {
                            $hasAnyStudent = true;
                        }
                    }

                    if ($hasAnyStudent) {
                        $dataExport->push($cursoData);
                    }
                }
            }

            if ($dataExport->isEmpty()) {
                continue; // Skip this note since no students have it
            }

            $fileName = 'Reporte_Notas_' . $bimestreStr . ($dividirSecciones ? 'Secciones_' : '') . $notaActual . '_' . date('Ymd_His') . '.xlsx';
            $exportObj = $dividirSecciones 
                         ? new \App\Exports\EstudiantesCriticosSeccionExport($dataExport)
                         : new \App\Exports\EstudiantesCriticosExport($dataExport);

            if (count($notasSeleccionadas) > 1) {
                \Maatwebsite\Excel\Facades\Excel::store($exportObj, $fileName, 'local');
                $archivosGenerados[] = [
                    'path' => \Illuminate\Support\Facades\Storage::disk('local')->path($fileName),
                    'name' => $fileName
                ];
            } else {
                return \Maatwebsite\Excel\Facades\Excel::download($exportObj, $fileName);
            }
        }

        if (count($notasSeleccionadas) > 1) {
            if (empty($archivosGenerados)) {
                return back()->with('error', 'No se encontraron estudiantes con las notas seleccionadas en los cursos filtrados.');
            }
            
            $zipFileName = 'Reportes_Por_Notas_' . date('Ymd_His') . '.zip';
            $zipPath = \Illuminate\Support\Facades\Storage::disk('local')->path($zipFileName);
            
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
                foreach ($archivosGenerados as $file) {
                    if (file_exists($file['path'])) {
                        $zip->addFile($file['path'], $file['name']);
                    }
                }
                $zip->close();
            }
            
            foreach ($archivosGenerados as $file) {
                if (file_exists($file['path'])) {
                    @unlink($file['path']);
                }
            }
            
            return response()->download($zipPath)->deleteFileAfterSend(true);
        } else {
            return back()->with('error', 'No hay estudiantes con la nota seleccionada en los cursos filtrados.');
        }
    }

    public function exportarDeudas(Request $request)
    {
        $anoActivo = AnoLectivo::where('activo', true)->first();
        if (!$anoActivo) {
            return back()->with('error', 'No hay un año lectivo activo configurado.');
        }

        $query = \App\Models\Mensualidad::with([
            'matricula.estudiante',
            'matricula.gradoSeccion.grado',
            'matricula.gradoSeccion.seccion'
        ])->whereHas('matricula', function ($q) use ($anoActivo) {
            $q->where('ano_lectivo_id', $anoActivo->id);
        });

        // Filtrar por nivel
        if ($request->filled('nivel')) {
            $query->whereHas('matricula.gradoSeccion.grado', function($q) use ($request) {
                $q->where('nivel', $request->nivel);
            });
        }

        // Filtrar por grado y sección
        if ($request->filled('grado_seccion_id')) {
            $query->whereHas('matricula', function($q) use ($request) {
                $q->where('grado_seccion_id', $request->grado_seccion_id);
            });
        }

        // Filtrar por mes
        if ($request->filled('mes')) {
            $query->where('mensualidades.mes', $request->mes);
        }

        // Filtrar por estado de pago
        if ($request->filled('estado_pago')) {
            if ($request->estado_pago === 'DEUDORES') {
                $query->where('mensualidades.estado', 'DEBE');
            } elseif ($request->estado_pago === 'PAGARON') {
                $query->where('mensualidades.estado', 'PAGÓ');
            }
            // Si es AMBOS, no filtramos por estado
        }

        // Ordenar resultados
        $mensualidades = $query->join('matriculas', 'mensualidades.matricula_id', '=', 'matriculas.id')
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
            ->orderBy('mensualidades.mes')
            ->select('mensualidades.*')
            ->get();

        if ($mensualidades->isEmpty()) {
            return back()->with('error', 'No se encontraron registros de mensualidades con los filtros seleccionados.');
        }

        $dividirSecciones = $request->boolean('dividir_secciones');
        $estado = $request->estado_pago ?? 'AMBOS';
        
        $fileName = 'Reporte_Mensualidades_' . $estado . '_' . date('Ymd_His') . '.xlsx';

        if ($dividirSecciones) {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DeudoresSeccionExport($mensualidades), $fileName);
        } else {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DeudoresExport($mensualidades, 'Reporte ' . $estado), $fileName);
        }
    }

    public function exportarSecciones(Request $request)
    {
        $anoActivo = AnoLectivo::where('activo', true)->first();
        if (!$anoActivo) {
            return back()->with('error', 'No hay un año lectivo activo configurado.');
        }

        $query = DB::table('matriculas')
            ->join('estudiantes', 'matriculas.estudiante_id', '=', 'estudiantes.id')
            ->join('grado_secciones', 'matriculas.grado_seccion_id', '=', 'grado_secciones.id')
            ->join('grados', 'grado_secciones.grado_id', '=', 'grados.id')
            ->join('secciones', 'grado_secciones.seccion_id', '=', 'secciones.id')
            ->where('matriculas.ano_lectivo_id', $anoActivo->id);

        if ($request->filled('nivel')) {
            $query->where('grados.nivel', $request->nivel);
        }
        if ($request->filled('grado_id')) {
            $query->where('grados.id', $request->grado_id);
        }
        if ($request->filled('seccion_id')) {
            $query->where('secciones.id', $request->seccion_id);
        }

        $estudiantes = $query->select(
                'estudiantes.dni',
                'grados.nivel',
                'grados.nombre as grado',
                'secciones.nombre as seccion',
                'estudiantes.nombres',
                'estudiantes.apellido_paterno',
                'estudiantes.apellido_materno'
            )
            ->orderBy('grados.nivel')
            ->orderBy('grados.orden')
            ->orderBy('secciones.nombre')
            ->orderBy('estudiantes.apellido_paterno')
            ->orderBy('estudiantes.apellido_materno')
            ->orderBy('estudiantes.nombres')
            ->get();

        if ($estudiantes->isEmpty()) {
            return back()->with('error', 'No se encontraron estudiantes para los filtros seleccionados.');
        }

        $fileName = 'Estudiantes_Secciones_' . date('Ymd_His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\EstudiantesSeccionesExport($estudiantes), $fileName);
    }

    public function exportarConsolidado(Request $request)
    {
        $request->validate([
            'nivel' => 'required|in:primaria,secundaria',
            'bimestre_id' => 'required|exists:bimestres,id'
        ]);

        $anoActivo = AnoLectivo::where('activo', true)->first();
        if (!$anoActivo) {
            return back()->with('error', 'No hay un año lectivo activo configurado.');
        }

        $nivel = $request->nivel;
        $bimestre = \App\Models\Bimestre::find($request->bimestre_id);

        if ($request->boolean('generar_grafico')) {
            $fileName = 'Datos_Grafico_B' . $bimestre->numero . '_' . ucfirst($nivel) . '_' . date('Ymd_His') . '.xlsx';
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\ConsolidadoGraficoExport($nivel, $anoActivo->id, $bimestre),
                $fileName
            );
        }

        $fileName = 'Consolidado_B' . $bimestre->numero . '_' . ucfirst($nivel) . '_' . date('Ymd_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ConsolidadoNivelExport($nivel, $anoActivo->id, $anoActivo->anio, $bimestre),
            $fileName
        );
    }
}

