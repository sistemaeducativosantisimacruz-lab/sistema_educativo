<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SeccionAlumnosExport implements FromCollection, ShouldAutoSize, WithStyles, WithTitle
{
    protected $seccion;
    protected $tutor;
    protected $cotutor;
    protected $estudiantes;
    protected $anio;

    public function __construct($seccion, $tutor, $cotutor, $estudiantes, $anio)
    {
        $this->seccion     = $seccion;
        $this->tutor       = $tutor;
        $this->cotutor     = $cotutor;
        $this->estudiantes = $estudiantes;
        $this->anio        = $anio;
    }

    public function title(): string
    {
        // El título de la pestaña del Excel
        return 'Alumnos';
    }

    public function collection()
    {
        $rows = collect();

        // Cabecera informativa
        $rows->push(['REPORTE DE ALUMNOS POR SECCIÓN Y TUTORÍA', '', '', '', '']);
        $rows->push(['Grado y Sección:', $this->seccion, '', '', '']);
        $rows->push(['Año Lectivo:', $this->anio, '', '', '']);
        $rows->push([
            'Tutor Principal:', 
            $this->tutor ? ($this->tutor->apellido_paterno . ' ' . $this->tutor->apellido_materno . ', ' . $this->tutor->nombres) : 'No asignado', 
            '', '', ''
        ]);
        $rows->push([
            'Co-tutor:', 
            $this->cotutor ? ($this->cotutor->apellido_paterno . ' ' . $this->cotutor->apellido_materno . ', ' . $this->cotutor->nombres) : 'No asignado', 
            '', '', ''
        ]);
        $rows->push(['', '', '', '', '']); // Fila vacía

        // Encabezados de la tabla
        $rows->push(['N°', 'DNI', 'Código Estudiante', 'Apellidos y Nombres', 'Fecha de Nacimiento']);

        // Datos de estudiantes
        $index = 1;
        foreach ($this->estudiantes as $est) {
            $rows->push([
                $index++,
                $est['dni'] ?: '—',
                $est['codigo_estudiante'] ?: '—',
                $est['apellidos'] . ',  ' . $est['nombres'],
                $est['fecha_nacimiento'] ?: '—'
            ]);
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo del título principal
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE));
        
        // Estilos de las etiquetas de metadatos (columna A en negrita)
        $sheet->getStyle('A2:A5')->getFont()->setBold(true);

        // Estilo de los encabezados de la tabla (fila 7)
        $sheet->getStyle('A7:E7')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle('A7:E7')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('4F46E5'); // Indigo 600 color matching layout

        // Alinear la columna N°, DNI, Código y Fecha de Nacimiento al centro
        $highestRow = $sheet->getHighestRow();
        if ($highestRow >= 8) {
            $sheet->getStyle('A8:A' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B8:C' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E8:E' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Bordes delgados para la tabla
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FFE5E7EB'], // Gray-200 border
                    ],
                ],
            ];
            $sheet->getStyle('A7:E' . $highestRow)->applyFromArray($styleArray);
        }
    }
}
