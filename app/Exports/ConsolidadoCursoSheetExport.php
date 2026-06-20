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

    public function __construct($curso, $nivel, $anoLectivoId, $anio, $bimestre)
    {
        $this->curso = $curso;
        $this->nivel = $nivel;
        $this->anoLectivoId = $anoLectivoId;
        $this->anio = $anio;
        $this->bimestre = $bimestre;
    }

    public function view(): View
    {
        $grados = Grado::where('nivel', $this->nivel)->orderBy('orden')->get();
        $competencias = $this->curso->competencias()->orderBy('orden')->get();

        $datosPorGrado = [];

        foreach ($grados as $grado) {
            $matriculados = DB::table('matriculas')
                ->join('grado_secciones', 'matriculas.grado_seccion_id', '=', 'grado_secciones.id')
                ->where('grado_secciones.grado_id', $grado->id)
                ->where('matriculas.ano_lectivo_id', $this->anoLectivoId)
                ->count();

            $datosCompetencias = [];

            foreach ($competencias as $comp) {
                $notasRaw = DB::table('notas_bimestrales')
                    ->join('matriculas', 'notas_bimestrales.estudiante_id', '=', 'matriculas.estudiante_id')
                    ->join('grado_secciones', 'matriculas.grado_seccion_id', '=', 'grado_secciones.id')
                    ->where('grado_secciones.grado_id', $grado->id)
                    ->where('matriculas.ano_lectivo_id', $this->anoLectivoId)
                    ->where('notas_bimestrales.curso_id', $this->curso->id)
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
                    'porc_AD' => $evaluados > 0 ? round(($conteo['AD'] / $evaluados) * 100, 1) : 0,
                    'porc_A'  => $evaluados > 0 ? round(($conteo['A'] / $evaluados) * 100, 1) : 0,
                    'porc_B'  => $evaluados > 0 ? round(($conteo['B'] / $evaluados) * 100, 1) : 0,
                    'porc_C'  => $evaluados > 0 ? round(($conteo['C'] / $evaluados) * 100, 1) : 0,
                ];
            }

            $nombreGrado = preg_replace('/[^0-9]/', '', $grado->nombre);
            if (empty($nombreGrado)) {
                $nombreGrado = substr($grado->nombre, 0, 1);
            }
            $nombreGrado .= 'º';

            $datosPorGrado[] = [
                'grado_nombre' => $nombreGrado,
                'competencias' => $datosCompetencias
            ];
        }

        return view('exports.consolidado_curso', [
            'curso_nombre' => strtoupper($this->curso->nombre),
            'nivel' => strtoupper($this->nivel),
            'anio' => $this->anio,
            'bimestre_nombre' => strtoupper($this->bimestre->nombre),
            'datosPorGrado' => $datosPorGrado
        ]);
    }

    public function title(): string
    {
        // Max title length in Excel is 31 characters
        $title = substr($this->curso->nombre, 0, 31);
        // Remove invalid characters for excel sheets
        $title = str_replace(['*', ':', '?', '[', ']', '\\', '/'], '', $title);
        return $title;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 2,     // Margen
            'B' => 10,    // Grado
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
        // Habilitar ajuste de texto en toda la hoja para que competencias y cabeceras largas se adapten
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setWrapText(true);
        
        return [];
    }
}
