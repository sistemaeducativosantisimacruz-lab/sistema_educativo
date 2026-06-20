<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EstudiantesCriticosSeccionExport implements WithMultipleSheets
{
    protected $dataExport;

    public function __construct($dataExport)
    {
        $this->dataExport = $dataExport;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->dataExport as $seccionData) {
            $sheets[] = new EstudiantesCriticosSeccionSheetExport($seccionData);
        }

        return $sheets;
    }
}
