<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EstudiantesCriticosExport implements \Maatwebsite\Excel\Concerns\WithMultipleSheets
{
    protected $dataExport;

    public function __construct($dataExport)
    {
        $this->dataExport = $dataExport;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->dataExport as $cursoData) {
            $sheets[] = new EstudiantesCriticosCursoSheetExport($cursoData);
        }

        return $sheets;
    }
}
