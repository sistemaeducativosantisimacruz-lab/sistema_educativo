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

    public function __construct($bimestreId, $gradoSeccionId, $filePath, $stats, $anoActivoId)
    {
        $this->bimestreId = $bimestreId;
        $this->gradoSeccionId = $gradoSeccionId;
        $this->filePath = $filePath;
        $this->stats = $stats;
        $this->anoActivoId = $anoActivoId;
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
                $sheets[$sheetName] = new CursoNotasImport($this->bimestreId, $this->gradoSeccionId, trim($sheetName), $this->stats, $this->anoActivoId);
            }
        }

        return $sheets;
    }
}
