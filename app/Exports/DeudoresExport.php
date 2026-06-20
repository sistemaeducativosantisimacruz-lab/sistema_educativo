<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DeudoresExport implements WithMultipleSheets
{
    protected $mensualidades;
    protected $title;

    public function __construct($mensualidades, $title = 'Reporte Mensualidades')
    {
        $this->mensualidades = $mensualidades;
        $this->title = $title;
    }

    public function sheets(): array
    {
        return [
            new DeudoresSheetExport($this->mensualidades, $this->title)
        ];
    }
}
