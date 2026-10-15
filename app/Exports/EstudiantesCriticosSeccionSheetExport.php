<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class EstudiantesCriticosSeccionSheetExport implements FromArray, WithTitle, WithEvents
{
    protected $seccion;
    protected $notaActual;

    public function __construct($seccion, $notaActual = 'C')
    {
        $this->seccion = $seccion;
        $this->notaActual = $notaActual;
    }

    public function title(): string
    {
        $title = $this->seccion->sheet_title ?? 'Seccion';
        $title = str_replace(['*', ':', '/', '\\', '?', '[', ']'], '', $title);
        return substr(trim($title), 0, 31);
    }

    public function array(): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Alturas de filas iniciales
                $sheet->getRowDimension(1)->setRowHeight(18);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(165);
                $sheet->getRowDimension(4)->setRowHeight(15);

                // Anchos de columnas fijas
                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(8);
                $sheet->getColumnDimension('C')->setWidth(42);

                // Columna A: N
                $sheet->setCellValue('A1', 'N');
                $sheet->mergeCells('A1:A4');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(10)->setName('Calibri');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

                // Columna B: GRADO Y SECCIÓN
                $sheet->setCellValue('B1', 'GRADO Y SECCIÓN');
                $sheet->mergeCells('B1:B4');
                $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(10)->setName('Calibri');
                $sheet->getStyle('B1')->getAlignment()->setTextRotation(90)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

                // Columna C: DATOS DEL ESTUDIANTE / NOMBRE Y APELLIDOS
                $sheet->setCellValue('C1', 'DATOS DEL ESTUDIANTE');
                $sheet->mergeCells('C1:C2');
                $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(10)->setName('Calibri');
                $sheet->getStyle('C1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2EFD9');
                $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->setCellValue('C3', 'NOMBRE Y APELLIDOS');
                $sheet->mergeCells('C3:C4');
                $sheet->getStyle('C3')->getFont()->setBold(true)->setSize(10)->setName('Calibri');
                $sheet->getStyle('C3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2EFD9');
                $sheet->getStyle('C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

                $currentCol = 4; // Columna D
                $compColMap = [];

                if (!empty($this->seccion->cursos)) {
                    foreach ($this->seccion->cursos as $curso) {
                        $compCount = count($curso->competencias);
                        if ($compCount === 0) {
                            continue;
                        }

                        $startCol = Coordinate::stringFromColumnIndex($currentCol);
                        $endCol = Coordinate::stringFromColumnIndex($currentCol + $compCount - 1);

                        // Cabecera del Curso (Filas 1-2)
                        $sheet->setCellValue("{$startCol}1", $curso->nombre);
                        $sheet->mergeCells("{$startCol}1:{$endCol}2");

                        $cColor = $curso->color ?? 'D9D9D9';
                        $cStyle = $sheet->getStyle("{$startCol}1:{$endCol}2");
                        $cStyle->getFont()->setBold(true)->setSize(10)->setName('Calibri');
                        $cStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF' . $cColor);
                        $cStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

                        // Cabeceras de Competencias (Filas 3-4)
                        foreach ($curso->competencias as $comp) {
                            $colLetter = Coordinate::stringFromColumnIndex($currentCol);
                            $sheet->getColumnDimension($colLetter)->setWidth(7);

                            $sheet->setCellValue("{$colLetter}3", $comp->nombre);
                            $sheet->mergeCells("{$colLetter}3:{$colLetter}4");

                            $compStyle = $sheet->getStyle("{$colLetter}3:{$colLetter}4");
                            $compStyle->getFont()->setSize(10)->setName('Calibri');
                            $compStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF' . $cColor);
                            $compStyle->getAlignment()->setTextRotation(90)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

                            $compColMap[$comp->id] = $colLetter;
                            $currentCol++;
                        }
                    }
                }

                $hasCompetencies = ($currentCol > 4);
                $firstCompCol = 'D';
                $lastCompCol = $hasCompetencies ? Coordinate::stringFromColumnIndex($currentCol - 1) : 'D';
                $totalCol = Coordinate::stringFromColumnIndex($currentCol);

                // Columna Total: TOTAL DE COMPETENCIAS
                $sheet->getColumnDimension($totalCol)->setWidth(10);
                $sheet->setCellValue("{$totalCol}1", 'TOTAL DE COMPETENCIAS');
                $sheet->mergeCells("{$totalCol}1:{$totalCol}4");
                $totStyle = $sheet->getStyle("{$totalCol}1:{$totalCol}4");
                $totStyle->getFont()->setBold(true)->setSize(10)->setName('Calibri');
                $totStyle->getAlignment()->setTextRotation(90)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

                // Filas de estudiantes (a partir de la fila 5)
                $r = 5;
                if (!empty($this->seccion->estudiantes)) {
                    foreach ($this->seccion->estudiantes as $st) {
                        $sheet->getRowDimension($r)->setRowHeight(20);

                        $sheet->setCellValue("A{$r}", $st->order_num);
                        $sheet->setCellValue("B{$r}", $this->seccion->short_section ?? '');
                        $sheet->setCellValue("C{$r}", $st->full_name);

                        $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(10)->setName('Calibri');
                        $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

                        $sheet->getStyle("B{$r}")->getFont()->setBold(true)->setSize(10)->setName('Calibri');
                        $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

                        $sheet->getStyle("C{$r}")->getFont()->setSize(10)->setName('Calibri');
                        $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);

                        foreach ($compColMap as $compId => $colLetter) {
                            if (isset($st->notas[$compId])) {
                                $sheet->setCellValue("{$colLetter}{$r}", $st->notas[$compId]);
                                $sheet->getStyle("{$colLetter}{$r}")->getFont()->setSize(10)->setName('Calibri');
                                $sheet->getStyle("{$colLetter}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                            }
                        }

                        if ($hasCompetencies) {
                            $sheet->setCellValue("{$totalCol}{$r}", "=COUNTA({$firstCompCol}{$r}:{$lastCompCol}{$r})");
                        } else {
                            $sheet->setCellValue("{$totalCol}{$r}", 0);
                        }
                        $sheet->getStyle("{$totalCol}{$r}")->getFont()->setBold(true)->setSize(10)->setName('Calibri');
                        $sheet->getStyle("{$totalCol}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

                        $r++;
                    }
                }

                $lastRow = max(4, $r - 1);

                // Bordes en toda la cuadrícula de la tabla
                $sheet->getStyle("A1:{$totalCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
