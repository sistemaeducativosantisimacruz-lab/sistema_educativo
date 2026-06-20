<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EstudiantesCriticosSeccionSheetExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithTitle
{
    protected $seccion;

    public function __construct($seccion)
    {
        $this->seccion = $seccion;
    }

    public function title(): string
    {
        // Example: "1ro Secundaria - A"
        $title = str_replace(['*', ':', '/', '\\', '?', '[', ']'], '', $this->seccion->seccion_nombre);
        
        // Shorten to fit within 31 chars and follow user request "número de grado seguido de la letra"
        // Let's replace words with short versions
        $title = str_ireplace(
            [' Secundaria', ' Primaria', 'ro ', 'do ', 'ero ', 'to ', 'mo ', 'vo ', 'no ', ' '], 
            [' Sec', ' Prim', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '], 
            $title
        );
        $title = trim(str_replace(' - ', ' ', $title)); // e.g. "1 Sec A"
        
        return substr($title, 0, 31);
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->seccion->cursos as $curso) {
            // Check if this course has any students with C in this section
            $hasStudents = false;
            foreach ($curso->competencias as $comp) {
                if ($comp->estudiantes->isNotEmpty()) {
                    $hasStudents = true;
                    break;
                }
            }

            // Optional: Skip course entirely if no students? The user said "lista de cursos con competencia con los estudiantes que tienen C"
            // It's cleaner to only show courses that actually have students with C.
            if (!$hasStudents) {
                continue;
            }

            // Fila de título de curso
            $rows->push([
                'Curso: ' . $curso->curso_nombre,
                '', '', '', ''
            ]);

            foreach ($curso->competencias as $comp) {
                if ($comp->estudiantes->isEmpty()) {
                    continue; // Skip competencies with no C students in this section
                }

                // Fila de competencia
                $rows->push([
                    '  Competencia: ' . $comp->competencia_nombre,
                    '', '', '', ''
                ]);

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

        if ($rows->isEmpty()) {
            $rows->push([
                'No hay estudiantes con la calificación solicitada en esta sección.',
                '', '', '', ''
            ]);
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
            ->getStartColor()->setARGB('FFD2D2');

        // Estilos para las filas de Curso y Competencias
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:E' . $highestRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1:E' . $highestRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        for ($i = 2; $i <= $highestRow; $i++) {
            $cellValue = $sheet->getCell('A' . $i)->getValue();
            if (str_starts_with(trim((string)$cellValue), 'Curso:')) {
                // Fila de curso
                $sheet->getStyle('A' . $i . ':E' . $i)->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FFFFFF');
                $sheet->getStyle('A' . $i . ':E' . $i)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('4F46E5'); // Indigo-600
            } elseif (str_starts_with(trim((string)$cellValue), 'Competencia:')) {
                // Fila de competencia
                $sheet->getStyle('A' . $i . ':E' . $i)->getFont()->setBold(true)->setItalic(true);
                $sheet->getStyle('A' . $i . ':E' . $i)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('F3F4F6'); // Gris claro
            } elseif (trim((string)$cellValue) !== '' && !str_starts_with(trim((string)$cellValue), 'DNI') && !str_starts_with(trim((string)$cellValue), 'No hay')) {
                // Fila de estudiante (DNI en Col A)
                $sheet->getStyle('D' . $i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $i)->getFont()->setBold(true);
            }
        }
    }
}
