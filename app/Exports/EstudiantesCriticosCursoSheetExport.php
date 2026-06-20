<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EstudiantesCriticosCursoSheetExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithTitle
{
    protected $curso;

    public function __construct($curso)
    {
        $this->curso = $curso;
    }

    public function title(): string
    {
        // Max 31 characters, remove invalid chars
        $cleanName = str_replace(['*', ':', '/', '\\', '?', '[', ']'], '', $this->curso->curso_nombre);
        $suffix = '-' . ($this->curso->curso_codigo ?? $this->curso->curso_id);
        $maxNameLength = 31 - strlen($suffix);
        $title = substr($cleanName, 0, $maxNameLength) . $suffix;
        return $title;
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->curso->competencias as $comp) {
            // Fila de competencia
            $rows->push([
                '  Competencia: ' . $comp->competencia_nombre,
                '', '', '', ''
            ]);

            if ($comp->estudiantes->isEmpty()) {
                $rows->push([
                    '    (Ningún estudiante con esta calificación)',
                    '', '', '', ''
                ]);
            } else {
                foreach ($comp->estudiantes as $est) {
                    $rows->push([
                        '    ' . $est->dni,
                        $est->apellido_paterno . ' ' . $est->apellido_materno . ', ' . $est->nombres,
                        $est->grado_nombre . ' - ' . $est->seccion_nombre,
                        $est->nota_logro ?? '', // Valor real dinámico
                        $est->conclusion_descriptiva ?? ''
                    ]);
                }
            }
            
            // Fila vacía separadora
            $rows->push(['', '', '', '', '']);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'DNI / Información',
            'Nombres y Apellidos',
            'Grado y Sección',
            'Calificación',
            'Conclusión Descriptiva'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 45, // Competencia / DNI
            'B' => 40, // Nombres y Apellidos
            'C' => 25, // Grado y Sección
            'D' => 15, // Calificación
            'E' => 50, // Conclusión Descriptiva
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Aplicar estilos a la cabecera
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('A1:E1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD2D2'); // Color rojo/rosado claro

        // Estilos para las filas de Curso y Competencias
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:E' . $highestRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1:E' . $highestRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        
        for ($i = 2; $i <= $highestRow; $i++) {
            $cellValue = $sheet->getCell('A' . $i)->getValue();
            if (str_starts_with(trim((string)$cellValue), 'Competencia:')) {
                // Fila de competencia
                $sheet->getStyle('A' . $i . ':E' . $i)->getFont()->setBold(true)->setItalic(true);
                $sheet->getStyle('A' . $i . ':E' . $i)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('F3F4F6'); // Gris claro
            } elseif (trim((string)$cellValue) !== '' && !str_starts_with(trim((string)$cellValue), 'DNI') && !str_starts_with(trim((string)$cellValue), '(Ningún')) {
                // Fila de estudiante (DNI en Col A)
                $sheet->getStyle('D' . $i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $i)->getFont()->setBold(true);
            }
        }
    }
}
