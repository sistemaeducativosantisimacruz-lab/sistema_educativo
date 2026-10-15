<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EstudiantesCriticosSeccionExport implements WithMultipleSheets
{
    protected $seccionesData;
    protected $notaActual;

    public function __construct($seccionesData, $notaActual = 'C')
    {
        $this->seccionesData = $seccionesData;
        $this->notaActual = $notaActual;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->seccionesData as $seccionData) {
            $sheets[] = new EstudiantesCriticosSeccionSheetExport($seccionData, $this->notaActual);
        }

        return $sheets;
    }
}
