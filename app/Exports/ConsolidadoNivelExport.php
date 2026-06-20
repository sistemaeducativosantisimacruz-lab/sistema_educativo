<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\Curso;

class ConsolidadoNivelExport implements WithMultipleSheets
{
    use Exportable;

    protected $nivel;
    protected $anoLectivoId;
    protected $anio;
    protected $bimestre;

    public function __construct($nivel, $anoLectivoId, $anio, $bimestre)
    {
        $this->nivel = $nivel;
        $this->anoLectivoId = $anoLectivoId;
        $this->anio = $anio;
        $this->bimestre = $bimestre;
    }

    public function sheets(): array
    {
        $sheets = [];

        $cursos = Curso::where('activo', true)
            ->paraNivel($this->nivel)
            ->soloCursos()
            ->orderBy('nombre')
            ->get();

        foreach ($cursos as $curso) {
            $sheets[] = new ConsolidadoCursoSheetExport($curso, $this->nivel, $this->anoLectivoId, $this->anio, $this->bimestre);
        }

        return $sheets;
    }
}
