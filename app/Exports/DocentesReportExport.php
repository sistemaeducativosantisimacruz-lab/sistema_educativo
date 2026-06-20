<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DocentesReportExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Docentes';
    }

    public function collection()
    {
        $rows = collect();
        $index = 1;
        foreach ($this->data as $d) {
            // Nombres completos: Apellidos + Nombres
            $nombreCompleto = trim($d->apellido_paterno . ' ' . $d->apellido_materno . ', ' . $d->nombres);
            
            // Email from user relation
            $correo = $d->user ? $d->user->email : '—';
            
            // Cursos a cargo
            $cursosList = $d->nombresCursos();
            
            // Grado/seccion a cargo como tutor
            $tutorSecciones = $d->tutoriaSecciones->map(function($ts) {
                return ($ts->grado->nombre ?? '') . ' - ' . ($ts->seccion->nombre ?? '');
            })->filter()->join(', ') ?: '—';

            $rows->push([
                $index++,
                $nombreCompleto,
                $d->dni ?: '—',
                $d->celular ?: '—',
                $correo,
                $cursosList,
                $tutorSecciones
            ]);
        }
        return $rows;
    }

    public function headings(): array
    {
        return [
            'N°',
            'Docente (Apellidos y Nombres)',
            'DNI',
            'Celular',
            'Correo Electrónico',
            'Cursos a Cargo',
            'Sección a Cargo (Tutor)'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Styling header row
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFEE2'); // Soft yellow background
            
        // Alignments
        $highestRow = $sheet->getHighestRow();
        if ($highestRow > 1) {
            $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C2:D' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G2:G' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }
    }
}
