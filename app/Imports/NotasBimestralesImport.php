<?php

namespace App\Imports;

use App\Imports\Sheets\CursoNotasImport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\IOFactory;

class NotasBimestralesImport implements WithMultipleSheets
{
    protected $bimestreId;
    protected $gradoSeccionId;
    protected $filePath;
    protected $stats;
    protected $anoActivoId;
    protected $estudiantesCache = null;

    public function __construct($bimestreId, $gradoSeccionId, $filePath, $stats, $anoActivoId)
    {
        $this->bimestreId = $bimestreId;
        $this->gradoSeccionId = $gradoSeccionId;
        $this->filePath = $filePath;
        $this->stats = $stats;
        $this->anoActivoId = $anoActivoId;

        // Pre-cargar todos los estudiantes de la sección en memoria una sola vez para todas las hojas
        if ($this->gradoSeccionId && $this->anoActivoId) {
            $matriculas = \App\Models\Matricula::with('estudiante')
                ->where('grado_seccion_id', $this->gradoSeccionId)
                ->where('ano_lectivo_id', $this->anoActivoId)
                ->get();

            $byCodigo = [];
            $byDni = [];
            $matriculadosIds = [];

            foreach ($matriculas as $mat) {
                if ($mat->estudiante) {
                    $est = $mat->estudiante;
                    $matriculadosIds[$est->id] = true;
                    if (!empty($est->codigo_estudiante)) {
                        $byCodigo[$est->codigo_estudiante] = $est;
                    }
                    if (!empty($est->dni)) {
                        $byDni[$est->dni] = $est;
                    }
                }
            }

            $this->estudiantesCache = [
                'byCodigo' => $byCodigo,
                'byDni' => $byDni,
                'matriculadosIds' => $matriculadosIds
            ];
        }
    }

    public function sheets(): array
    {
        $inputFileType = IOFactory::identify($this->filePath);
        $reader = IOFactory::createReader($inputFileType);
        $sheetNames = $reader->listWorksheetNames($this->filePath);

        $sheets = [];

        foreach ($sheetNames as $sheetName) {
            // Only process sheets that start with 3 or 4 digits followed by a hyphen
            if (preg_match('/^\d{3,4}-/', trim($sheetName))) {
                $sheets[$sheetName] = new CursoNotasImport(
                    $this->bimestreId, 
                    $this->gradoSeccionId, 
                    trim($sheetName), 
                    $this->stats, 
                    $this->anoActivoId, 
                    $this->estudiantesCache
                );
            }
        }

        return $sheets;
    }
}
