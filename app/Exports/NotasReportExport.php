<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NotasReportExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    protected $data;
    protected $title;

    public function __construct($data, $title = 'Notas')
    {
        $this->data = $data;
        $this->title = $title;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function collection()
    {
        $rows = collect();
        $agrupadoPorCurso = $this->data->groupBy('curso_nombre');

        foreach ($agrupadoPorCurso as $cursoNombre => $competencias) {
            // Fila de título de curso
            $rows->push([
                'Curso: ' . $cursoNombre,
                '', '', '', '', ''
            ]);

            $agrupadoPorCompetencia = $competencias->groupBy('competencia_nombre');
            foreach ($agrupadoPorCompetencia as $compNombre => $compRows) {
                $cantAD = $compRows->where('promedio_letra', 'AD')->sum('cantidad');
                $cantA = $compRows->where('promedio_letra', 'A')->sum('cantidad');
                $cantB = $compRows->where('promedio_letra', 'B')->sum('cantidad');
                $cantC = $compRows->where('promedio_letra', 'C')->sum('cantidad');
                $totalComp = $compRows->sum('cantidad');

                $rows->push([
                    '  ' . $compNombre,
                    $cantAD,
                    $cantA,
                    $cantB,
                    $cantC,
                    $totalComp
                ]);
            }

            // Fila vacía separadora
            $rows->push(['', '', '', '', '', '']);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Curso / Competencia',
            'Notas AD',
            'Notas A',
            'Notas B',
            'Notas C',
            'Total Calificaciones'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Aplicar estilos a la cabecera
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFEE2'); // Color amarillo claro/crema

        // Estilos para las filas de Curso y Competencias
        $highestRow = $sheet->getHighestRow();
        for ($i = 2; $i <= $highestRow; $i++) {
            $cellValue = $sheet->getCell('A' . $i)->getValue();
            if (str_starts_with(trim($cellValue), 'Curso:')) {
                // Hacer la fila del curso en negrita y con fondo gris claro
                $sheet->getStyle('A' . $i . ':F' . $i)->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle('A' . $i . ':F' . $i)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('F3F4F6');
            } elseif (!empty($cellValue)) {
                // Fila de competencia: Alinear números al centro
                $sheet->getStyle('B' . $i . ':F' . $i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
        }
    }
}
