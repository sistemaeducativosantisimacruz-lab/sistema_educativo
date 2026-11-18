<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Contracts\View\View;
use App\Models\Grado;
use Illuminate\Support\Facades\DB;

class ConsolidadoCursoSheetExport implements FromView, WithTitle, WithColumnWidths, WithStyles
{
    protected $curso;
    protected $nivel;
    protected $anoLectivoId;
    protected $anio;
    protected $bimestre;
    protected $separarSecciones;

    protected $prefetchedGrados;
    protected $prefetchedSeccionesPorGrado;
    protected $prefetchedEstudiantesPorGrado;
    protected $prefetchedEstudiantesPorSeccion;
    protected $prefetchedNotasCurso;

    public function __construct(
        $curso,
        $nivel,
        $anoLectivoId,
        $anio,
        $bimestre,
        $separarSecciones = false,
        $prefetchedGrados = null,
        $prefetchedSeccionesPorGrado = null,
        $prefetchedEstudiantesPorGrado = null,
        $prefetchedEstudiantesPorSeccion = null,
        $prefetchedNotasCurso = null
    ) {
        $this->curso = $curso;
        $this->nivel = $nivel;
        $this->anoLectivoId = $anoLectivoId;
        $this->anio = $anio;
        $this->bimestre = $bimestre;
        $this->separarSecciones = $separarSecciones;

        $this->prefetchedGrados = $prefetchedGrados;
        $this->prefetchedSeccionesPorGrado = $prefetchedSeccionesPorGrado;
        $this->prefetchedEstudiantesPorGrado = $prefetchedEstudiantesPorGrado;
        $this->prefetchedEstudiantesPorSeccion = $prefetchedEstudiantesPorSeccion;
        $this->prefetchedNotasCurso = $prefetchedNotasCurso;
    }

    public function view(): View
    {
        // Fallback optimizado si no se suministran datos pre-cargados
        if ($this->prefetchedGrados === null) {
            $this->prefetchedGrados = Grado::where('nivel', $this->nivel)->orderBy('orden')->get();
            $gradoIds = $this->prefetchedGrados->pluck('id')->toArray();

            $seccionesRaw = DB::table('grado_secciones as gs')
                ->join('secciones as s', 'gs.seccion_id', '=', 's.id')
                ->whereIn('gs.grado_id', $gradoIds)
                ->where('gs.ano_lectivo_id', $this->anoLectivoId)
                ->orderBy('s.nombre')
                ->select('gs.grado_id', 'gs.id as grado_seccion_id', 's.nombre as seccion_nombre')
                ->get();
            $this->prefetchedSeccionesPorGrado = $seccionesRaw->groupBy('grado_id');

            $matriculas = DB::table('matriculas')
                ->join('grado_secciones', 'matriculas.grado_seccion_id', '=', 'grado_secciones.id')
                ->where('matriculas.ano_lectivo_id', $this->anoLectivoId)
                ->whereIn('grado_secciones.grado_id', $gradoIds)
                ->select(
                    'matriculas.estudiante_id',
                    'matriculas.estado',
                    'grado_secciones.grado_id',
                    'matriculas.grado_seccion_id'
                )
                ->get();

            $this->prefetchedEstudiantesPorGrado = [];
            $this->prefetchedEstudiantesPorSeccion = [];
            $estudianteIds = [];
            foreach ($matriculas as $m) {
                $item = ['id' => $m->estudiante_id, 'estado' => $m->estado];
                $this->prefetchedEstudiantesPorGrado[$m->grado_id][] = $item;
                $this->prefetchedEstudiantesPorSeccion[$m->grado_seccion_id][] = $item;
                $estudianteIds[$m->estudiante_id] = true;
            }

            $this->prefetchedNotasCurso = [];
            if (!empty($estudianteIds)) {
                $notasRaw = DB::table('notas_bimestrales')
                    ->where('bimestre_id', $this->bimestre->id)
                    ->where('curso_id', $this->curso->id)
                    ->whereIn('estudiante_id', array_keys($estudianteIds))
                    ->whereNotNull('nota')
                    ->select('competencia_id', 'estudiante_id', 'nota')
                    ->get();

                foreach ($notasRaw as $nr) {
                    if (!isset($this->prefetchedNotasCurso[$nr->competencia_id][$nr->estudiante_id])) {
                        $this->prefetchedNotasCurso[$nr->competencia_id][$nr->estudiante_id] = $nr->nota;
                    }
                }
            }
        }

        $grados = $this->prefetchedGrados;
        $competencias = $this->curso->competencias;
        $datosPorGrado = [];

        foreach ($grados as $grado) {
            $nombreGrado = preg_replace('/[^0-9]/', '', $grado->nombre);
            if (empty($nombreGrado)) {
                $nombreGrado = substr($grado->nombre, 0, 1);
            }
            $nombreGrado .= 'º';

            if ($this->separarSecciones) {
                // 1. Tabla de Grado (unidad consolidada del grado)
                $competenciasGrado = $this->calcularDatosCompetencias($competencias, $grado->id);
                if (count($competenciasGrado) > 0) {
                    $datosPorGrado[] = [
                        'grado_nombre' => $nombreGrado . ' Grado',
                        'competencias' => $competenciasGrado
                    ];
                }

                // 2. Tablas individuales por cada sección del grado
                $secciones = $this->prefetchedSeccionesPorGrado->get($grado->id, collect());

                foreach ($secciones as $sec) {
                    $competenciasSec = $this->calcularDatosCompetencias($competencias, $grado->id, $sec->grado_seccion_id);
                    if (count($competenciasSec) > 0) {
                        $datosPorGrado[] = [
                            'grado_nombre' => $nombreGrado . ' ' . $sec->seccion_nombre,
                            'competencias' => $competenciasSec
                        ];
                    }
                }
            } else {
                // Modo estándar: Una sola tabla consolidada por grado
                $competenciasGrado = $this->calcularDatosCompetencias($competencias, $grado->id);
                if (count($competenciasGrado) > 0) {
                    $datosPorGrado[] = [
                        'grado_nombre' => $nombreGrado,
                        'competencias' => $competenciasGrado
                    ];
                }
            }
        }

        return view('exports.consolidado_curso', [
            'curso_nombre' => strtoupper($this->curso->nombre),
            'nivel' => strtoupper($this->nivel),
            'anio' => $this->anio,
            'bimestre_nombre' => strtoupper($this->bimestre->nombre),
            'datosPorGrado' => $datosPorGrado
        ]);
    }

    private function calcularDatosCompetencias($competencias, int $gradoId, ?int $gradoSeccionId = null): array
    {
        $estudiantes = $gradoSeccionId
            ? ($this->prefetchedEstudiantesPorSeccion[$gradoSeccionId] ?? [])
            : ($this->prefetchedEstudiantesPorGrado[$gradoId] ?? []);

        if (empty($estudiantes)) {
            return [];
        }

        $matriculadosActivos = 0;
        $retiradosMap = [];
        $estudiantesScopeMap = [];

        foreach ($estudiantes as $e) {
            $estudiantesScopeMap[$e['id']] = true;
            if ($e['estado'] === 'retirado') {
                $retiradosMap[$e['id']] = true;
            } else {
                $matriculadosActivos++;
            }
        }

        $datosCompetencias = [];

        foreach ($competencias as $comp) {
            $compNotas = $this->prefetchedNotasCurso[$comp->id] ?? [];
            $notasEstudiantes = [];
            $retiradosEvaluados = 0;
            $conteo = ['AD' => 0, 'A' => 0, 'B' => 0, 'C' => 0];

            foreach ($compNotas as $estId => $nota) {
                if (isset($estudiantesScopeMap[$estId])) {
                    $notasEstudiantes[$estId] = $nota;
                    if (isset($retiradosMap[$estId])) {
                        $retiradosEvaluados++;
                    }
                    if (isset($conteo[$nota])) {
                        $conteo[$nota]++;
                    }
                }
            }

            $evaluados = count($notasEstudiantes);
            $matriculados = $matriculadosActivos + $retiradosEvaluados;
            $sinEvaluar = max(0, $matriculados - $evaluados);

            $datosCompetencias[] = [
                'nombre' => $comp->nombre,
                'matriculados' => $matriculados,
                'evaluados' => $evaluados,
                'sin_evaluar' => $sinEvaluar,
                'AD' => $conteo['AD'],
                'A' => $conteo['A'],
                'B' => $conteo['B'],
                'C' => $conteo['C'],
                'porc_AD' => $evaluados > 0 ? round(($conteo['AD'] / $evaluados) * 100, 1) : 0,
                'porc_A'  => $evaluados > 0 ? round(($conteo['A'] / $evaluados) * 100, 1) : 0,
                'porc_B'  => $evaluados > 0 ? round(($conteo['B'] / $evaluados) * 100, 1) : 0,
                'porc_C'  => $evaluados > 0 ? round(($conteo['C'] / $evaluados) * 100, 1) : 0,
            ];
        }

        return $datosCompetencias;
    }

    public function title(): string
    {
        // Longitud máxima de título en Excel: 31 caracteres
        $title = substr($this->curso->nombre, 0, 31);
        $title = str_replace(['*', ':', '?', '[', ']', '\\', '/'], '', $title);
        return $title;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 2,     // Margen
            'B' => 14,    // Grado
            'C' => 50,    // Competencias
            'D' => 15,    // Matriculados
            'E' => 15,    // Sin evaluar
            'F' => 15,    // Evaluados
            'G' => 8,     // # Inicio
            'H' => 8,     // % Inicio
            'I' => 8,     // # Proceso
            'J' => 8,     // % Proceso
            'K' => 8,     // # Logrado
            'L' => 8,     // % Logrado
            'M' => 8,     // # Destacado
            'N' => 8,     // % Destacado
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setWrapText(true);
        return [];
    }
}
