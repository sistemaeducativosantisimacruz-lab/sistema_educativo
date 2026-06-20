<?php

namespace App\Exports;

use App\Models\Estudiante;
use App\Models\Curso;
use App\Models\NotaBimestral;
use App\Models\Bimestre;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReporteBimestralEstudianteExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $estudianteId;
    protected $anoLectivoId;

    public function __construct($estudianteId, $anoLectivoId)
    {
        $this->estudianteId = $estudianteId;
        $this->anoLectivoId = $anoLectivoId;
    }

    public function view(): View
    {
        $estudiante = Estudiante::with(['matriculas' => function($q) {
            $q->where('ano_lectivo_id', $this->anoLectivoId)->with('gradoSeccion.grado', 'gradoSeccion.seccion');
        }])->findOrFail($this->estudianteId);

        $matricula = $estudiante->matriculas->first();
        
        $nivel = $matricula ? $matricula->gradoSeccion->grado->nivel : null;

        $cursosQuery = Curso::where('activo', true);
        if ($nivel) {
            $cursosQuery->where(function($q) use ($nivel) {
                $q->where('nivel', $nivel)
                  ->orWhere('nivel', 'ambos');
            });
        }
        
        $cursos = $cursosQuery->with(['competencias' => function($q) {
                $q->orderBy('orden');
            }])
            ->orderBy('nombre')
            ->get();
            
        $bimestres = Bimestre::where('ano_lectivo_id', $this->anoLectivoId)->orderBy('numero')->get();
        
        $notasRaw = NotaBimestral::where('estudiante_id', $this->estudianteId)
            ->whereIn('bimestre_id', $bimestres->pluck('id'))
            ->with('competencia')
            ->get();
            
        $notasMap = [];
        foreach ($notasRaw as $nota) {
            if ($nota->competencia) {
                $notasMap[$nota->curso_id][$nota->competencia->nombre][$nota->bimestre_id] = $nota;
            }
        }

        // Evitar competencias duplicadas por curso
        foreach ($cursos as $curso) {
            $curso->setRelation('competencias', $curso->competencias->unique('nombre')->values());
        }

        return view('exports.reporte_bimestral_estudiante', compact(
            'estudiante', 'matricula', 'cursos', 'bimestres', 'notasMap'
        ));
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();
        
        // Estilo general para toda la hoja
        $sheet->getStyle("A1:{$highestCol}{$highestRow}")->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 10,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Estilos para la cabecera (Filas 1 a 4 aproximadamente)
        $sheet->getStyle('A1:F4')->applyFromArray([
            'font' => ['bold' => true],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE]
            ]
        ]);
        
        // Estilo para la fila de títulos de la tabla
        $sheet->getStyle('A6:F6')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FF4F46E5'] // Indigo-600
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ]);

        // Wrap text for competencies (ajustar contenido) y ancho fijo a 14.48
        $sheet->getStyle("B7:B{$highestRow}")->getAlignment()->setWrapText(true);
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(14.48);
        
        // Centrar columnas de bimestres
        $sheet->getStyle("C7:F{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
