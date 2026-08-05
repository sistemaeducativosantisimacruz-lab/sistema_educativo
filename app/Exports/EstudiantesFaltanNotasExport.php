<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class EstudiantesFaltanNotasExport extends StringValueBinder implements FromCollection, WithTitle, WithColumnWidths, WithStyles, WithHeadings, WithMapping, WithEvents, WithCustomValueBinder
{
    protected $nivel;
    protected $anoLectivoId;
    protected $bimestre;

    public function __construct($nivel, $anoLectivoId, $bimestre)
    {
        $this->nivel = $nivel;
        $this->anoLectivoId = $anoLectivoId;
        $this->bimestre = $bimestre;
    }

    public function collection()
    {
        $estudiantesFaltan = collect();

        $estudiantesQuery = DB::table('matriculas')
            ->join('estudiantes', 'matriculas.estudiante_id', '=', 'estudiantes.id')
            ->join('grado_secciones', 'matriculas.grado_seccion_id', '=', 'grado_secciones.id')
            ->join('grados', 'grado_secciones.grado_id', '=', 'grados.id')
            ->join('secciones', 'grado_secciones.seccion_id', '=', 'secciones.id')
            ->where('matriculas.ano_lectivo_id', $this->anoLectivoId)
            ->where('grados.nivel', $this->nivel)
            ->where('matriculas.estado', '!=', 'retirado')
            ->select(
                'grados.nombre as grado',
                'secciones.nombre as seccion',
                'estudiantes.dni',
                'estudiantes.codigo_estudiante as codigo_modular',
                'estudiantes.nombres',
                'estudiantes.apellido_paterno',
                'estudiantes.apellido_materno',
                'estudiantes.id as estudiante_id'
            )
            ->orderBy('grados.orden')
            ->orderBy('secciones.nombre')
            ->orderBy('estudiantes.apellido_paterno')
            ->orderBy('estudiantes.apellido_materno')
            ->get();

        $cursos = DB::table('cursos')
            ->where('activo', true)
            ->whereIn('nivel', [$this->nivel, 'ambos', 'none'])
            ->pluck('id');

        $totalCompetencias = DB::table('competencias')
            ->whereIn('curso_id', $cursos)
            ->count();

        $notasConteo = DB::table('notas_bimestrales')
            ->where('bimestre_id', $this->bimestre->id)
            ->whereIn('curso_id', $cursos)
            ->whereNotNull('nota')
            ->select('estudiante_id', DB::raw('count(*) as total_notas'))
            ->groupBy('estudiante_id')
            ->pluck('total_notas', 'estudiante_id');

        foreach ($estudiantesQuery as $estudiante) {
            $notasEstudiante = $notasConteo[$estudiante->estudiante_id] ?? 0;
            if ($notasEstudiante < $totalCompetencias) {
                $estudiantesFaltan->push($estudiante);
            }
        }

        return $estudiantesFaltan;
    }

    public function headings(): array
    {
        return [
            ['Reporte de Estudiantes que faltan notas - ' . ucfirst($this->nivel) . ' - Bimestre ' . $this->bimestre->numero],
            [
                'Grado',
                'Sección',
                'DNI',
                'Código Modular',
                'Nombres y Apellidos'
            ]
        ];
    }

    public function map($estudiante): array
    {
        return [
            $estudiante->grado,
            $estudiante->seccion,
            $estudiante->dni,
            $estudiante->codigo_modular,
            $estudiante->apellido_paterno . ' ' . $estudiante->apellido_materno . ', ' . $estudiante->nombres
        ];
    }

    public function title(): string
    {
        return 'Estudiantes faltan notas';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 10,
            'C' => 15,
            'D' => 40,
            'E' => 40,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'f3f4f6']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'd1d5db'],
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Unir celdas para el título (Fila 1, de A a E)
                $sheet->mergeCells('A1:E1');

                // Aplicar estilo de texto a las columnas C (DNI) y D (Código Modular)
                // StringValueBinder ya lo hace, pero esto asegura que Excel no intente convertirlo después.
                $sheet->getStyle('C3:D' . $highestRow)
                      ->getNumberFormat()
                      ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

                // Alternar colores en las filas y agregar bordes + centrado
                for ($row = 3; $row <= $highestRow; $row++) {
                    $bgColor = ($row % 2 == 0) ? 'f9fafb' : 'ffffff';
                    
                    $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $bgColor]
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'd1d5db'],
                            ],
                        ],
                    ]);

                    // Centrar A, B, C, D
                    $sheet->getStyle('A' . $row . ':D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}
