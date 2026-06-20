<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\DB;

class ConsolidadoGraficoExport implements FromCollection, WithHeadings, WithMapping
{
    protected $nivel;
    protected $anoLectivoId;
    protected $bimestre;

    public function __construct($nivel, $anoLectivoId, $bimestre)
    {
        $this->nivel = $nivel;
        $this->anoLectivoId = $anoLectivoId;
        $this->bimestre = $bimestre;
    }

    public function collection()
    {
        return DB::table('notas_bimestrales')
            ->join('estudiantes', 'notas_bimestrales.estudiante_id', '=', 'estudiantes.id')
            ->join('matriculas', 'estudiantes.id', '=', 'matriculas.estudiante_id')
            ->join('grado_secciones', 'matriculas.grado_seccion_id', '=', 'grado_secciones.id')
            ->join('grados', 'grado_secciones.grado_id', '=', 'grados.id')
            ->join('cursos', 'notas_bimestrales.curso_id', '=', 'cursos.id')
            ->join('competencias', 'notas_bimestrales.competencia_id', '=', 'competencias.id')
            ->where('matriculas.ano_lectivo_id', $this->anoLectivoId)
            ->where('grados.nivel', $this->nivel)
            ->where('notas_bimestrales.bimestre_id', $this->bimestre->id)
            ->whereNotNull('notas_bimestrales.nota')
            ->whereNotIn('notas_bimestrales.nota', ['0', '0.0', '0.00', '-', ''])
            ->select(
                'grados.nombre as grado_nombre',
                'cursos.nombre as curso_nombre',
                'competencias.nombre as competencia_nombre',
                'notas_bimestrales.nota as nivel_logro',
                DB::raw('count(*) as cantidad_alumnos')
            )
            ->groupBy(
                'grados.nombre',
                'cursos.nombre',
                'competencias.nombre',
                'notas_bimestrales.nota'
            )
            ->orderBy('grados.nombre')
            ->orderBy('cursos.nombre')
            ->orderBy('competencias.nombre')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Grado',
            'Curso',
            'Competencia',
            'Nivel de Logro',
            'Cantidad de Alumnos'
        ];
    }

    public function map($row): array
    {
        return [
            $row->grado_nombre,
            $row->curso_nombre,
            $row->competencia_nombre,
            $row->nivel_logro,
            $row->cantidad_alumnos,
        ];
    }
}
