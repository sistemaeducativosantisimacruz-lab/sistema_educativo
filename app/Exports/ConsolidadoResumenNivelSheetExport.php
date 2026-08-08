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

    public function __construct($nivel, $anoLectivoId, $anio, $bimestre)
    {
        $this->nivel = $nivel;
        $this->anoLectivoId = $anoLectivoId;
        $this->anio = $anio;
        $this->bimestre = $bimestre;
    }

    public function view(): View
    {
        $cursos = Curso::where('activo', true)
            ->paraNivel($this->nivel)
            ->soloCursos()
            ->orderBy('nombre')
            ->get();

        $grados = Grado::where('nivel', $this->nivel)->pluck('id')->toArray();

        $datosPorCurso = [];

        foreach ($cursos as $curso) {
            $competencias = $curso->competencias()->orderBy('orden')->get();

            $datosCompetencias = [];

            foreach ($competencias as $comp) {
                
                $matriculasQuery = DB::table('matriculas')
                    ->join('grado_secciones', 'matriculas.grado_seccion_id', '=', 'grado_secciones.id')
                    ->whereIn('grado_secciones.grado_id', $grados)
                    ->where('matriculas.ano_lectivo_id', $this->anoLectivoId);
                
                $matriculasTotal = $matriculasQuery->select('matriculas.estudiante_id', 'matriculas.estado')->get();
                $matriculadosActivos = $matriculasTotal->where('estado', '!=', 'retirado')->count();
                $estudiantesRetirados = $matriculasTotal->where('estado', 'retirado')->pluck('estudiante_id')->toArray();

                $notasRaw = DB::table('notas_bimestrales')
                    ->join('matriculas', 'notas_bimestrales.estudiante_id', '=', 'matriculas.estudiante_id')
                    ->join('grado_secciones', 'matriculas.grado_seccion_id', '=', 'grado_secciones.id')
                    ->whereIn('grado_secciones.grado_id', $grados)
                    ->where('matriculas.ano_lectivo_id', $this->anoLectivoId)
                    ->where('notas_bimestrales.curso_id', $curso->id)
                    ->where('notas_bimestrales.competencia_id', $comp->id)
                    ->where('notas_bimestrales.bimestre_id', $this->bimestre->id)
                    ->whereNotNull('notas_bimestrales.nota')
                    ->select('notas_bimestrales.estudiante_id', 'notas_bimestrales.nota')
                    ->orderBy('notas_bimestrales.bimestre_id', 'desc')
                    ->get();
                
                $notasEstudiantes = [];
                foreach ($notasRaw as $nr) {
                    if (!isset($notasEstudiantes[$nr->estudiante_id])) {
                        $notasEstudiantes[$nr->estudiante_id] = $nr->nota;
                    }
                }
                
                $evaluados = count($notasEstudiantes);
                
                $retiradosEvaluados = 0;
                foreach ($notasEstudiantes as $estudianteId => $nota) {
                    if (in_array($estudianteId, $estudiantesRetirados)) {
                        $retiradosEvaluados++;
                    }
                }
                
                $matriculados = $matriculadosActivos + $retiradosEvaluados;
                $sinEvaluar = max(0, $matriculados - $evaluados);
                
                $conteo = ['AD' => 0, 'A' => 0, 'B' => 0, 'C' => 0];
                foreach ($notasEstudiantes as $nota) {
                    if (isset($conteo[$nota])) {
                        $conteo[$nota]++;
                    }
                }
                
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
