<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class EstudiantesFaltanNotasExport implements FromView, WithTitle, WithColumnWidths, WithStyles
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

    public function view(): View
    {
        $estudiantesFaltan = collect();

        $estudiantesQuery = DB::table('matriculas')
            ->join('estudiantes', 'matriculas.estudiante_id', '=', 'estudiantes.id')
            ->join('grado_secciones', 'matriculas.grado_seccion_id', '=', 'grado_secciones.id')
            ->join('grados', 'grado_secciones.grado_id', '=', 'grados.id')
            ->join('secciones', 'grado_secciones.seccion_id', '=', 'secciones.id')
            ->where('matriculas.ano_lectivo_id', $this->anoLectivoId)
            ->where('grados.nivel', $this->nivel)
            ->where('matriculas.estado', '!=', 'retirado')
            ->select(
                'grados.nombre as grado',
                'secciones.nombre as seccion',
                'estudiantes.dni',
                'estudiantes.codigo_estudiante as codigo_modular',
                'estudiantes.nombres',
                'estudiantes.apellido_paterno',
                'estudiantes.apellido_materno',
                'estudiantes.id as estudiante_id'
            )
            ->orderBy('grados.orden')
            ->orderBy('secciones.nombre')
            ->orderBy('estudiantes.apellido_paterno')
            ->orderBy('estudiantes.apellido_materno')
            ->get();

        $cursos = DB::table('cursos')
            ->where('activo', true)
            ->whereIn('nivel', [$this->nivel, 'ambos', 'none']) // Include 'none' if used for general
            ->pluck('id');

        $totalCompetencias = DB::table('competencias')
            ->whereIn('curso_id', $cursos)
            ->count();

        $notasConteo = DB::table('notas_bimestrales')
            ->where('bimestre_id', $this->bimestre->id)
            ->whereIn('curso_id', $cursos)
            ->whereNotNull('nota')
            ->select('estudiante_id', DB::raw('count(*) as total_notas'))
            ->groupBy('estudiante_id')
            ->pluck('total_notas', 'estudiante_id');

        foreach ($estudiantesQuery as $estudiante) {
            $notasEstudiante = $notasConteo[$estudiante->estudiante_id] ?? 0;
            if ($notasEstudiante < $totalCompetencias) {
                // Add property for missing notes if needed, but not strictly requested
                $estudiante->faltantes = $totalCompetencias - $notasEstudiante;
                $estudiantesFaltan->push($estudiante);
            }
        }

        return view('exports.estudiantes_faltan_notas', [
            'estudiantes' => $estudiantesFaltan,
            'nivel' => ucfirst($this->nivel),
            'bimestre' => $this->bimestre
        ]);
    }

    public function title(): string
    {
        return 'Estudiantes faltan notas';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // Grado
            'B' => 10, // Seccion
            'C' => 15, // DNI
            'D' => 40, // Codigo Modular
            'E' => 40, // Apellidos y Nombres
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
