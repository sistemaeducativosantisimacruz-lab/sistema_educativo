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
    public function index(Request $request, \App\Services\ReportService $reportService)
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

            // Lógica delegada al servicio según la pestaña activa
            switch ($tab) {
                case 'notas':
                    $resumen = $reportService->getNotasResumen($anoActivo, $request);
                    break;
                case 'deudas':
                    $deudas = $reportService->getDeudasReport($anoActivo, $request);
                    break;
                case 'docentes':
                    $docentesReport = $reportService->getDocentesReport($anoActivo, $request);
                    break;
                case 'secciones':
                    $seccionesReport = $reportService->getSeccionesReport($anoActivo, $request);
                    break;
                case 'estudiantes':
                    $estudiantesList = $reportService->getEstudiantesList($anoActivo, $request);
                    break;
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

        $dividirSecciones = $request->boolean('dividir_secciones');

        // Obtener cursos
        $queryCursos = DB::table('cursos')->where('activo', true);
        if (!$request->boolean('todos_cursos') && $request->filled('cursos_seleccionados')) {
            $queryCursos->whereIn('id', $request->cursos_seleccionados);
        } elseif ($request->filled('nivel')) {
            $queryCursos->whereIn('nivel', [$request->nivel, 'ambos', 'none']);
        }
        $cursosRaw = $queryCursos->get();

        if ($cursosRaw->isEmpty()) {
            return back()->with('error', 'No se encontraron cursos con los filtros seleccionados.');
        }

        // Orden oficial MINEDU curricular para cursos
        $ordenMinedu = [
            'DESARROLLO PERSONAL, CIUDADANÍA Y CÍVICA' => 1,
            'DESARROLLO PERSONAL' => 1,
            'DPCC' => 1,
            'CIENCIAS SOCIALES' => 2,
            'EDUCACIÓN FÍSICA' => 3,
            'EDUCACION FISICA' => 3,
            'COMUNICACIÓN' => 4,
            'COMUNICACION' => 4,
            'ARTE Y CULTURA' => 5,
            'ARTE' => 5,
            'CASTELLANO COMO SEGUNDA LENGUA' => 6,
            'INGLÉS' => 7,
            'INGLES' => 7,
            'INGLÉS COMO LENGUA EXTRANJERA' => 7,
            'INGLÉS COMO IDIOMA EXTRANJERO' => 7,
            'MATEMÁTICA' => 8,
            'MATEMATICA' => 8,
            'CIENCIA Y TECNOLOGÍA' => 9,
            'CIENCIA Y TECNOLOGIA' => 9,
            'CIENCIA, TECNOLOGÍA Y AMBIENTE' => 9,
            'CIENCIA Y AMBIENTE' => 9,
            'EDUCACIÓN PARA EL TRABAJO' => 10,
            'EDUCACION PARA EL TRABAJO' => 10,
            'EDUCACIÓN RELIGIOSA' => 11,
            'EDUCACION RELIGIOSA' => 11,
            'PERSONAL SOCIAL' => 12,
            'DESENVOLVIMIENTO EN TIC' => 13,
            'GESTIONA SU APRENDIZAJE' => 14,
            'TUTORÍA Y ORIENTACIÓN EDUCATIVA' => 15,
            'TUTORÍA' => 15,
        ];

        $cursosOrdenados = $cursosRaw->sortBy(function ($c) use ($ordenMinedu) {
            $upper = mb_strtoupper(trim($c->nombre), 'UTF-8');
            return $ordenMinedu[$upper] ?? 99;
        })->values();

        // Competencias de los cursos seleccionados
        $competencias = DB::table('competencias')
            ->whereIn('curso_id', $cursosOrdenados->pluck('id'))
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        $cursosData = collect();
        $colorIdx = 0;
        foreach ($cursosOrdenados as $c) {
            $compsCurso = $competencias->where('curso_id', $c->id)->values();
            if ($compsCurso->isEmpty()) {
                continue;
            }

            $cursosData->push((object) [
                'id' => $c->id,
                'nombre' => $this->formatearNombreCurso($c->nombre),
                'color' => $this->obtenerColorCurso($c->nombre, $colorIdx++),
                'competencias' => $compsCurso
            ]);
        }

        if ($cursosData->isEmpty()) {
            return back()->with('error', 'Los cursos seleccionados no tienen competencias configuradas.');
        }

        $notasSeleccionadas = $request->notas_seleccionadas;
        $archivosGenerados = [];

        foreach ($notasSeleccionadas as $notaActual) {
            if ($dividirSecciones) {
                // Obtener secciones del año activo filtradas por nivel
                $querySecciones = DB::table('grado_secciones')
                    ->join('grados', 'grado_secciones.grado_id', '=', 'grados.id')
                    ->join('secciones', 'grado_secciones.seccion_id', '=', 'secciones.id')
                    ->where('grado_secciones.ano_lectivo_id', $anoActivo->id);

                if ($request->filled('nivel')) {
                    $querySecciones->where('grados.nivel', $request->nivel);
                }

                $secciones = $querySecciones->select(
                        'grado_secciones.id as grado_seccion_id',
                        'grados.id as grado_id',
                        'grados.nombre as grado_nombre',
                        'grados.nivel as grado_nivel',
                        'grados.orden as grado_orden',
                        'secciones.id as seccion_id',
                        'secciones.nombre as seccion_nombre'
                    )
                    ->orderBy('grados.orden')
                    ->orderBy('secciones.nombre')
                    ->get();

                $multipleNiveles = $secciones->pluck('grado_nivel')->unique()->count() > 1;
                $seccionesData = collect();
                $hayEstudiantesEnReporte = false;

                foreach ($secciones as $sec) {
                    // Nombre corto de pestaña (e.g. "1A", "5B")
                    preg_match('/\d+/', $sec->grado_nombre, $matches);
                    $num = $matches[0] ?? substr(trim($sec->grado_nombre), 0, 1);
                    $shortSection = $num . trim($sec->seccion_nombre);

                    $sheetTitle = $shortSection;
                    if ($multipleNiveles) {
                        $suffix = ($sec->grado_nivel === 'primaria') ? 'P' : 'S';
                        $sheetTitle .= ' ' . $suffix;
                    }

                    // Estudiantes matriculados activos en esta sección ordenados por apellidos y nombres
                    $alumnosSeccion = DB::table('matriculas')
                        ->join('estudiantes', 'matriculas.estudiante_id', '=', 'estudiantes.id')
                        ->where('matriculas.grado_seccion_id', $sec->grado_seccion_id)
                        ->where('matriculas.ano_lectivo_id', $anoActivo->id)
                        ->where('matriculas.estado', '!=', 'retirado')
                        ->select(
                            'estudiantes.id',
                            'estudiantes.apellido_paterno',
                            'estudiantes.apellido_materno',
                            'estudiantes.nombres'
                        )
                        ->orderBy('estudiantes.apellido_paterno')
                        ->orderBy('estudiantes.apellido_materno')
                        ->orderBy('estudiantes.nombres')
                        ->get();

                    $estudiantesIndexados = [];
                    $studentIds = [];
                    $idx = 1;
                    foreach ($alumnosSeccion as $al) {
                        $nombreCompleto = trim("{$al->apellido_paterno} {$al->apellido_materno}, {$al->nombres}");
                        $estudiantesIndexados[$al->id] = (object) [
                            'id' => $al->id,
                            'order_num' => $idx++,
                            'full_name' => mb_strtoupper($nombreCompleto, 'UTF-8'),
                            'notas' => []
                        ];
                        $studentIds[] = $al->id;
                    }

                    if (!empty($studentIds)) {
                        $queryNotas = DB::table('notas_bimestrales')
                            ->whereIn('estudiante_id', $studentIds)
                            ->whereIn('curso_id', $cursosData->pluck('id'))
                            ->where('nota', $notaActual);

                        if ($request->filled('bimestre_id')) {
                            $queryNotas->where('bimestre_id', $request->bimestre_id);
                        }

                        $notasRaw = $queryNotas->select('estudiante_id', 'competencia_id', 'nota')->get();

                        foreach ($notasRaw as $nr) {
                            if (isset($estudiantesIndexados[$nr->estudiante_id])) {
                                $estudiantesIndexados[$nr->estudiante_id]->notas[$nr->competencia_id] = $nr->nota;
                            }
                        }
                    }

                    // Filtrar solo los estudiantes que tienen al menos una calificación de la nota solicitada
                    $estudiantesConNota = collect($estudiantesIndexados)->filter(function ($est) {
                        return !empty($est->notas);
                    })->values();

                    if ($estudiantesConNota->isNotEmpty()) {
                        $hayEstudiantesEnReporte = true;
                    }

                    $seccionesData->push((object) [
                        'grado_seccion_id' => $sec->grado_seccion_id,
                        'grado_nombre' => $sec->grado_nombre,
                        'seccion_nombre' => $sec->seccion_nombre,
                        'sheet_title' => $sheetTitle,
                        'short_section' => $shortSection,
                        'cursos' => $cursosData,
                        'estudiantes' => $estudiantesConNota
                    ]);
                }

                if (!$hayEstudiantesEnReporte) {
                    continue; // Saltar nota sin estudiantes
                }

                $fileName = 'REPORTE CRITICO ' . $notaActual . '.xlsx';
                $exportObj = new \App\Exports\EstudiantesCriticosSeccionExport($seccionesData, $notaActual);

            } else {
                // Modo antiguo agrupado por cursos (si no se marca dividir en secciones)
                $queryCriticos = DB::table('notas_bimestrales')
                    ->join('estudiantes', 'notas_bimestrales.estudiante_id', '=', 'estudiantes.id')
                    ->join('matriculas', 'estudiantes.id', '=', 'matriculas.estudiante_id')
                    ->join('grado_secciones', 'matriculas.grado_seccion_id', '=', 'grado_secciones.id')
                    ->join('grados', 'grado_secciones.grado_id', '=', 'grados.id')
                    ->join('secciones', 'grado_secciones.seccion_id', '=', 'secciones.id')
                    ->where('matriculas.ano_lectivo_id', $anoActivo->id)
                    ->where('notas_bimestrales.nota', $notaActual)
                    ->whereIn('notas_bimestrales.curso_id', $cursosData->pluck('id'));

                if ($request->filled('bimestre_id')) {
                    $queryCriticos->where('notas_bimestrales.bimestre_id', $request->bimestre_id);
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
                foreach ($cursosData as $curso) {
                    $cursoData = (object) [
                        'curso_id' => $curso->id,
                        'curso_nombre' => $curso->nombre,
                        'curso_codigo' => $curso->id,
                        'competencias' => collect()
                    ];
                    
                    $compsCurso = $curso->competencias;
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

                if ($dataExport->isEmpty()) {
                    continue;
                }

                $fileName = 'REPORTE CRITICO ' . $notaActual . '.xlsx';
                $exportObj = new \App\Exports\EstudiantesCriticosExport($dataExport);
            }

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

    private function formatearNombreCurso($nombre)
    {
        $upper = mb_strtoupper(trim($nombre), 'UTF-8');
        if (in_array($upper, ['DESARROLLO PERSONAL, CIUDADANÍA Y CÍVICA', 'DESARROLLO PERSONAL', 'DPCC'])) {
            return 'DPCC';
        }
        if (in_array($upper, ['INGLÉS', 'INGLES', 'INGLÉS COMO LENGUA EXTRANJERA', 'INGLÉS COMO IDIOMA EXTRANJERO'])) {
            return 'Inglés como lengua extranjera';
        }
        if (in_array($upper, ['CIENCIA Y TECNOLOGÍA', 'CIENCIA Y TECNOLOGIA', 'CIENCIA, TECNOLOGÍA Y AMBIENTE'])) {
            return 'Ciencia y tecnología';
        }
        if (in_array($upper, ['CIENCIAS SOCIALES'])) return 'Ciencias Sociales';
        if (in_array($upper, ['EDUCACIÓN FÍSICA', 'EDUCACION FISICA'])) return 'Educación Física';
        if (in_array($upper, ['COMUNICACIÓN', 'COMUNICACION'])) return 'Comunicación';
        if (in_array($upper, ['ARTE Y CULTURA', 'ARTE'])) return 'Arte y Cultura';
        if (in_array($upper, ['MATEMÁTICA', 'MATEMATICA'])) return 'Matemática';
        if (in_array($upper, ['EDUCACIÓN RELIGIOSA', 'EDUCACION RELIGIOSA'])) return 'Educación Religiosa';
        if (in_array($upper, ['EDUCACIÓN PARA EL TRABAJO', 'EDUCACION PARA EL TRABAJO'])) return 'Educación para el Trabajo';
        return mb_convert_case($nombre, MB_CASE_TITLE, 'UTF-8');
    }

    private function obtenerColorCurso($nombre, $index = 0)
    {
        $upper = mb_strtoupper(trim($nombre), 'UTF-8');
        $cursoColores = [
            'DPCC' => '9BBB59',
            'DESARROLLO PERSONAL, CIUDADANÍA Y CÍVICA' => '9BBB59',
            'DESARROLLO PERSONAL' => '9BBB59',
            'CIENCIAS SOCIALES' => 'FBD4B4',
            'EDUCACIÓN FÍSICA' => 'DDD9C3',
            'EDUCACION FISICA' => 'DDD9C3',
            'COMUNICACIÓN' => 'C6D9F0',
            'COMUNICACION' => 'C6D9F0',
            'ARTE Y CULTURA' => 'FFC000',
            'ARTE' => 'FFC000',
            'INGLÉS' => 'FFFF00',
            'INGLES' => 'FFFF00',
            'INGLÉS COMO LENGUA EXTRANJERA' => 'FFFF00',
            'INGLÉS COMO IDIOMA EXTRANJERO' => 'FFFF00',
            'MATEMÁTICA' => 'EAF1DD',
            'MATEMATICA' => 'EAF1DD',
            'CIENCIA Y TECNOLOGÍA' => 'C4BD97',
            'CIENCIA Y TECNOLOGIA' => 'C4BD97',
            'CIENCIA, TECNOLOGÍA Y AMBIENTE' => 'C4BD97',
            'CIENCIA Y AMBIENTE' => 'C4BD97',
            'EDUCACIÓN RELIGIOSA' => 'B2A1C7',
            'EDUCACION RELIGIOSA' => 'B2A1C7',
            'EDUCACIÓN PARA EL TRABAJO' => 'F2DCDB',
            'EDUCACION PARA EL TRABAJO' => 'F2DCDB',
            'PERSONAL SOCIAL' => '9BBB59',
            'DESENVOLVIMIENTO EN TIC' => 'E2D9F3',
            'GESTIONA SU APRENDIZAJE' => 'DDEBF7',
            'TUTORÍA Y ORIENTACIÓN EDUCATIVA' => 'FDE9D9',
            'TUTORÍA' => 'FDE9D9',
        ];

        if (isset($cursoColores[$upper])) {
            return $cursoColores[$upper];
        }

        $fallbacks = ['C6D9F0', 'EAF1DD', 'FBD4B4', 'DDD9C3', 'B2A1C7', 'C4BD97', 'FFFF00', 'FFC000', 'F2DCDB'];
        return $fallbacks[$index % count($fallbacks)];
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
            ->where('matriculas.ano_lectivo_id', $anoActivo->id)
            ->where('matriculas.estado', '!=', 'retirado');

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

        if ($request->boolean('estudiantes_faltan_notas')) {
            $fileName = 'Estudiantes_Faltan_Notas_' . ucfirst($nivel) . '_B' . $bimestre->numero . '_' . date('Ymd_His') . '.xlsx';
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\EstudiantesFaltanNotasExport($nivel, $anoActivo->id, $bimestre),
                $fileName
            );
        }

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

