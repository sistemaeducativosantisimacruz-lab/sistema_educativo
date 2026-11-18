<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\Curso;
use App\Models\Grado;
use Illuminate\Support\Facades\DB;

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
        // 1. Grados del nivel
        $grados = Grado::where('nivel', $this->nivel)->orderBy('orden')->get();
        $gradoIds = $grados->pluck('id')->toArray();

        // 2. Secciones por grado del año lectivo
        $seccionesRaw = DB::table('grado_secciones as gs')
            ->join('secciones as s', 'gs.seccion_id', '=', 's.id')
            ->whereIn('gs.grado_id', $gradoIds)
            ->where('gs.ano_lectivo_id', $this->anoLectivoId)
            ->orderBy('s.nombre')
            ->select('gs.grado_id', 'gs.id as grado_seccion_id', 's.nombre as seccion_nombre')
            ->get();
        $seccionesPorGrado = $seccionesRaw->groupBy('grado_id');

        // 3. Cursos del nivel con sus competencias ordenadas
        $cursos = Curso::where('activo', true)
            ->paraNivel($this->nivel)
            ->soloCursos()
            ->with(['competencias' => function ($q) {
                $q->orderBy('orden');
            }])
            ->orderBy('nombre')
            ->get();
        $cursoIds = $cursos->pluck('id')->toArray();

        // 4. Todas las matriculas activas del nivel en el año lectivo
        $matriculas = DB::table('matriculas')
            ->join('grado_secciones', 'matriculas.grado_seccion_id', '=', 'grado_secciones.id')
            ->where('matriculas.ano_lectivo_id', $this->anoLectivoId)
            ->whereIn('grado_secciones.grado_id', $gradoIds)
            ->select(
                'matriculas.estudiante_id',
                'matriculas.estado',
                'grado_secciones.grado_id',
                'matriculas.grado_seccion_id'
            )
            ->get();

        // Indexar estudiantes en memoria
        $estudiantesNivel = [];
        $estudiantesPorGrado = [];
        $estudiantesPorSeccion = [];
        $estudianteIdsSet = [];

        foreach ($matriculas as $m) {
            $estItem = ['id' => $m->estudiante_id, 'estado' => $m->estado];
            $estudiantesNivel[] = $estItem;
            $estudiantesPorGrado[$m->grado_id][] = $estItem;
            $estudiantesPorSeccion[$m->grado_seccion_id][] = $estItem;
            $estudianteIdsSet[$m->estudiante_id] = true;
        }

        $estudianteIds = array_keys($estudianteIdsSet);

        // 5. Pre-cargar todas las notas bimestrales del nivel y bimestre en una sola consulta
        $notasIndexadas = [];
        if (!empty($estudianteIds) && !empty($cursoIds)) {
            $notasRaw = DB::table('notas_bimestrales')
                ->where('bimestre_id', $this->bimestre->id)
                ->whereIn('curso_id', $cursoIds)
                ->whereIn('estudiante_id', $estudianteIds)
                ->whereNotNull('nota')
                ->select('curso_id', 'competencia_id', 'estudiante_id', 'nota')
                ->get();

            foreach ($notasRaw as $nr) {
                if (!isset($notasIndexadas[$nr->curso_id][$nr->competencia_id][$nr->estudiante_id])) {
                    $notasIndexadas[$nr->curso_id][$nr->competencia_id][$nr->estudiante_id] = $nr->nota;
                }
            }
        }

        $sheets = [];

        // Hoja 1: Resumen de Nivel
        $sheets[] = new ConsolidadoResumenNivelSheetExport(
            $this->nivel,
            $this->anoLectivoId,
            $this->anio,
            $this->bimestre,
            $cursos,
            $estudiantesNivel,
            $notasIndexadas
        );

        // Hojas por Curso
        foreach ($cursos as $curso) {
            $sheets[] = new ConsolidadoCursoSheetExport(
                $curso,
                $this->nivel,
                $this->anoLectivoId,
                $this->anio,
                $this->bimestre,
                $this->separarSecciones,
                $grados,
                $seccionesPorGrado,
                $estudiantesPorGrado,
                $estudiantesPorSeccion,
                $notasIndexadas[$curso->id] ?? []
            );
        }

        return $sheets;
    }
}
