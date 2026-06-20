<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Mensualidad;

class DeudoresSheetExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    protected $mensualidades;
    protected $title;

    public function __construct($mensualidades, $title = 'Mensualidades')
    {
        $this->mensualidades = $mensualidades;
        $this->title = $title;
    }

    public function collection()
    {
        $rows = collect();
        $meses = Mensualidad::meses();

        foreach ($this->mensualidades as $m) {
            $estudiante = $m->matricula->estudiante;
            $gradoSeccion = $m->matricula->gradoSeccion;
            
            $rows->push([
                $estudiante->dni,
                $estudiante->apellido_paterno . ' ' . $estudiante->apellido_materno . ', ' . $estudiante->nombres,
                ucfirst($gradoSeccion->grado->nivel),
                $gradoSeccion->grado->nombre . ' - ' . $gradoSeccion->seccion->nombre,
                $meses[$m->mes] ?? $m->mes,
                $m->estado,
                $m->observacion
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'DNI',
            'Apellidos y Nombres',
            'Nivel',
            'Grado y Sección',
            'Mes',
            'Estado',
            'Observación'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Cabecera en negrita y fondo azul
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['argb' => 'FF4F46E5'] // Indigo-600
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ]
        ]);
        
        $highestRow = $sheet->getHighestRow();
        
        // Estilos para los estados
        for ($i = 2; $i <= $highestRow; $i++) {
            $estado = $sheet->getCell('F' . $i)->getValue();
            if ($estado === 'DEBE') {
                $sheet->getStyle('F' . $i)->getFont()->getColor()->setARGB('FFDC2626'); // Rojo
            } elseif ($estado === 'PAGÓ') {
                $sheet->getStyle('F' . $i)->getFont()->getColor()->setARGB('FF16A34A'); // Verde
            } elseif ($estado === 'EXONERADO') {
                $sheet->getStyle('F' . $i)->getFont()->getColor()->setARGB('FF2563EB'); // Azul
            } elseif ($estado === 'BENEFICIADO') {
                $sheet->getStyle('F' . $i)->getFont()->getColor()->setARGB('FF9333EA'); // Morado
            }
        }
    }

    public function title(): string
    {
        // Limitar a 31 caracteres
        return substr(str_replace(['*', ':', '/', '\\', '?', '[', ']'], '', $this->title), 0, 31);
    }
}
