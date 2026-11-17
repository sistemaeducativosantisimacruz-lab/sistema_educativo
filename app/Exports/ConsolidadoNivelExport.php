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
    protected $separarSecciones;

    public function __construct($nivel, $anoLectivoId, $anio, $bimestre, $separarSecciones = false)
    {
        $this->nivel = $nivel;
        $this->anoLectivoId = $anoLectivoId;
        $this->anio = $anio;
        $this->bimestre = $bimestre;
        $this->separarSecciones = $separarSecciones;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new ConsolidadoResumenNivelSheetExport($this->nivel, $this->anoLectivoId, $this->anio, $this->bimestre);

        $cursos = Curso::where('activo', true)
            ->paraNivel($this->nivel)
            ->soloCursos()
            ->orderBy('nombre')
            ->get();

        foreach ($cursos as $curso) {
            $sheets[] = new ConsolidadoCursoSheetExport($curso, $this->nivel, $this->anoLectivoId, $this->anio, $this->bimestre, $this->separarSecciones);
        }

        return $sheets;
    }
}
