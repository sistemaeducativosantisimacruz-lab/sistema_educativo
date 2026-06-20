<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EstudiantesSeccionesExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    protected $estudiantes;

    public function __construct($estudiantes)
    {
        $this->estudiantes = $estudiantes;
    }

    public function collection()
    {
        $index = 1;
        return $this->estudiantes->map(function ($est) use (&$index) {
            return [
                $index++,
                $est->dni,
                $est->grado,
                $est->seccion,
                ucfirst($est->nivel),
                $est->apellido_paterno . ' ' . $est->apellido_materno . ', ' . $est->nombres
            ];
        });
    }

    public function headings(): array
    {
        return [
            'N°',
            'DNI Estudiante',
            'Grado',
            'Sección',
            'Nivel',
            'Nombres y Apellidos'
        ];
    }

    public function title(): string
    {
        return 'Estudiantes';
    }

    public function styles(Worksheet $sheet)
    {
        // Estilos para la cabecera
        $sheet->getStyle('A1:F1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFF');
        $sheet->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('8B5CF6'); // Purple-500

        // Centrar columnas excepto nombres
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B2:B' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C2:C' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D2:D' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E2:E' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    }
}
