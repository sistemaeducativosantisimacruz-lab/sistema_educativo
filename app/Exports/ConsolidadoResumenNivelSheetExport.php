<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Contracts\View\View;
use App\Models\Curso;
use App\Models\Grado;
use Illuminate\Support\Facades\DB;

class ConsolidadoResumenNivelSheetExport implements FromView, WithTitle, WithColumnWidths, WithStyles
{
    protected $nivel;
    protected $anoLectivoId;
    protected $anio;
    protected $bimestre;
    protected $prefetchedCursos;
    protected $prefetchedEstudiantesNivel;
    protected $prefetchedNotasIndexadas;

    public function __construct(
        $nivel,
        $anoLectivoId,
        $anio,
        $bimestre,
        $prefetchedCursos = null,
        $prefetchedEstudiantesNivel = null,
        $prefetchedNotasIndexadas = null
    ) {
        $this->nivel = $nivel;
        $this->anoLectivoId = $anoLectivoId;
        $this->anio = $anio;
        $this->bimestre = $bimestre;
        $this->prefetchedCursos = $prefetchedCursos;
        $this->prefetchedEstudiantesNivel = $prefetchedEstudiantesNivel;
        $this->prefetchedNotasIndexadas = $prefetchedNotasIndexadas;
    }

    public function view(): View
    {
        $cursos = $this->prefetchedCursos;
        $estudiantesNivel = $this->prefetchedEstudiantesNivel;
        $notasIndexadas = $this->prefetchedNotasIndexadas;

        // Fallback optimizado si no se suministran datos pre-cargados
        if ($cursos === null) {
            $grados = Grado::where('nivel', $this->nivel)->pluck('id')->toArray();

            $cursos = Curso::where('activo', true)
                ->paraNivel($this->nivel)
                ->soloCursos()
                ->with(['competencias' => function ($q) {
                    $q->orderBy('orden');
                }])
                ->orderBy('nombre')
                ->get();

            $matriculasTotal = DB::table('matriculas')
                ->join('grado_secciones', 'matriculas.grado_seccion_id', '=', 'grado_secciones.id')
                ->whereIn('grado_secciones.grado_id', $grados)
                ->where('matriculas.ano_lectivo_id', $this->anoLectivoId)
                ->select('matriculas.estudiante_id', 'matriculas.estado')
                ->get();

            $estudiantesNivel = [];
            $estudianteIds = [];
            foreach ($matriculasTotal as $m) {
                $estudiantesNivel[] = ['id' => $m->estudiante_id, 'estado' => $m->estado];
                $estudianteIds[$m->estudiante_id] = true;
            }

            $notasIndexadas = [];
            $cursoIds = $cursos->pluck('id')->toArray();
            if (!empty($estudianteIds) && !empty($cursoIds)) {
                $notasRaw = DB::table('notas_bimestrales')
                    ->where('bimestre_id', $this->bimestre->id)
                    ->whereIn('curso_id', $cursoIds)
                    ->whereIn('estudiante_id', array_keys($estudianteIds))
                    ->whereNotNull('nota')
                    ->select('curso_id', 'competencia_id', 'estudiante_id', 'nota')
                    ->get();

                foreach ($notasRaw as $nr) {
                    if (!isset($notasIndexadas[$nr->curso_id][$nr->competencia_id][$nr->estudiante_id])) {
                        $notasIndexadas[$nr->curso_id][$nr->competencia_id][$nr->estudiante_id] = $nr->nota;
                    }
                }
            }
        }

        // Mapear estudiantes del nivel
        $matriculadosActivos = 0;
        $retiradosMap = [];
        $todosNivelMap = [];
        if (!empty($estudiantesNivel)) {
            foreach ($estudiantesNivel as $e) {
                $todosNivelMap[$e['id']] = true;
                if ($e['estado'] === 'retirado') {
                    $retiradosMap[$e['id']] = true;
                } else {
                    $matriculadosActivos++;
                }
            }
        }

        $datosPorCurso = [];

        foreach ($cursos as $curso) {
            $competencias = $curso->competencias;
            $datosCompetencias = [];

            foreach ($competencias as $comp) {
                $compNotas = $notasIndexadas[$curso->id][$comp->id] ?? [];
                $notasEstudiantes = [];
                $retiradosEvaluados = 0;
                $conteo = ['AD' => 0, 'A' => 0, 'B' => 0, 'C' => 0];

                foreach ($compNotas as $estId => $nota) {
                    if (isset($todosNivelMap[$estId])) {
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
                    'porc_AD' => $evaluados > 0 ? ($conteo['AD'] / $evaluados) : 0,
                    'porc_A'  => $evaluados > 0 ? ($conteo['A'] / $evaluados) : 0,
                    'porc_B'  => $evaluados > 0 ? ($conteo['B'] / $evaluados) : 0,
                    'porc_C'  => $evaluados > 0 ? ($conteo['C'] / $evaluados) : 0,
                ];
            }

            if (count($datosCompetencias) > 0) {
                $datosPorCurso[] = [
                    'curso_nombre' => $curso->nombre,
                    'competencias' => $datosCompetencias
                ];
            }
        }

        return view('exports.consolidado_resumen_nivel', [
            'nivel' => strtoupper($this->nivel),
            'anio' => $this->anio,
            'bimestre_nombre' => strtoupper($this->bimestre->nombre),
            'datosPorCurso' => $datosPorCurso
        ]);
    }

    public function title(): string
    {
        return 'CONSOLIDADO POR NIVEL RESUMEN';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 2,     // Margen
            'B' => 15,    // Área / Curso
            'C' => 50,    // Competencia
            'D' => 8,    // Mat.
            'E' => 8,    // Sin Ev.
            'F' => 8,    // Eval.
            'G' => 8,     // Inicio (#)
            'H' => 10,     // Inicio (%)
            'I' => 10,     // Proceso (#)
            'J' => 10,     // Proceso (%)
            'K' => 10,     // Logrado (#)
            'L' => 10,     // Logrado (%)
            'M' => 12,     // Destacado (#)
            'N' => 12,     // Destacado (%)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setWrapText(true);
        return [];
    }
}
