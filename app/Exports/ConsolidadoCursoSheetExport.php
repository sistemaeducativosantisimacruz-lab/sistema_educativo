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

    public function __construct($curso, $nivel, $anoLectivoId, $anio, $bimestre, $separarSecciones = false)
    {
        $this->curso = $curso;
        $this->nivel = $nivel;
        $this->anoLectivoId = $anoLectivoId;
        $this->anio = $anio;
        $this->bimestre = $bimestre;
        $this->separarSecciones = $separarSecciones;
    }

    public function view(): View
    {
        $grados = Grado::where('nivel', $this->nivel)->orderBy('orden')->get();
        $competencias = $this->curso->competencias()->orderBy('orden')->get();

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
                $secciones = DB::table('grado_secciones as gs')
                    ->join('secciones as s', 'gs.seccion_id', '=', 's.id')
                    ->where('gs.grado_id', $grado->id)
                    ->where('gs.ano_lectivo_id', $this->anoLectivoId)
                    ->orderBy('s.nombre')
                    ->select('gs.id as grado_seccion_id', 's.nombre as seccion_nombre')
                    ->get();

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
        $matriculasQuery = DB::table('matriculas')
            ->join('grado_secciones', 'matriculas.grado_seccion_id', '=', 'grado_secciones.id')
            ->where('matriculas.ano_lectivo_id', $this->anoLectivoId);

        if ($gradoSeccionId) {
            $matriculasQuery->where('matriculas.grado_seccion_id', $gradoSeccionId);
        } else {
            $matriculasQuery->where('grado_secciones.grado_id', $gradoId);
        }

        $matriculas = $matriculasQuery->select('matriculas.estudiante_id', 'matriculas.estado')->get();
        if ($matriculas->isEmpty()) {
            return [];
        }

        $matriculadosActivos = $matriculas->where('estado', '!=', 'retirado')->count();
        $estudiantesRetirados = $matriculas->where('estado', 'retirado')->pluck('estudiante_id')->toArray();

        $datosCompetencias = [];

        foreach ($competencias as $comp) {
            $notasQuery = DB::table('notas_bimestrales')
                ->join('matriculas', 'notas_bimestrales.estudiante_id', '=', 'matriculas.estudiante_id')
                ->join('grado_secciones', 'matriculas.grado_seccion_id', '=', 'grado_secciones.id')
                ->where('matriculas.ano_lectivo_id', $this->anoLectivoId)
                ->where('notas_bimestrales.curso_id', $this->curso->id)
                ->where('notas_bimestrales.competencia_id', $comp->id)
                ->where('notas_bimestrales.bimestre_id', $this->bimestre->id)
                ->whereNotNull('notas_bimestrales.nota');

            if ($gradoSeccionId) {
                $notasQuery->where('matriculas.grado_seccion_id', $gradoSeccionId);
            } else {
                $notasQuery->where('grado_secciones.grado_id', $gradoId);
            }

            $notasRaw = $notasQuery->select('notas_bimestrales.estudiante_id', 'notas_bimestrales.nota')
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
        // Habilitar ajuste de texto en toda la hoja para que competencias y cabeceras largas se adapten
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setWrapText(true);
        
        return [];
    }
}
