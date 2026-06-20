<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DeudoresSeccionExport implements WithMultipleSheets
{
    protected $mensualidades;

    public function __construct($mensualidades)
    {
        $this->mensualidades = $mensualidades;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Agrupar por sección
        $agrupado = $this->mensualidades->groupBy(function ($m) {
            $gs = $m->matricula->gradoSeccion;
            return $gs->grado->nombre . ' - ' . $gs->seccion->nombre;
        });

        foreach ($agrupado as $nombreSeccion => $mensualidadesSeccion) {
            $sheets[] = new DeudoresSheetExport($mensualidadesSeccion, $nombreSeccion);
        }

        return $sheets;
    }
}
