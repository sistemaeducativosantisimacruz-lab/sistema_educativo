<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Illuminate\Support\Facades\Log;

class ActaNotasBimestralImport implements WithMultipleSheets, SkipsUnknownSheets
{
    protected $selectedSheets;
    protected $gradoSeccionId;
    protected $anoLectivoId;
    protected $bimestreId;
    protected $adminId;

    public $errores = [];
    public $importadosCount = 0;

    public function __construct(array $selectedSheets, $gradoSeccionId, $anoLectivoId, $bimestreId, $adminId)
    {
        $this->selectedSheets = $selectedSheets;
        $this->gradoSeccionId = $gradoSeccionId;
        $this->anoLectivoId = $anoLectivoId;
        $this->bimestreId = $bimestreId;
        $this->adminId = $adminId;
    }

    public function sheets(): array
    {
        $validSheets = [
            '0001-ART Y CULT',
            '0002-CAST SEGNL',
            '0004-CIENC TEC',
            '0010-DESARR PCC',
            '014-CCSS',
            '017-COMU',
            '031-EFIS',
            '032-ETRA',
            '035-EREL',
            '057-INGL',
            '063-MATE',
            '0006-DESEN TIC',
            '0007-GEST AUTO',
        ];

        $sheets = [];
        foreach ($validSheets as $sheetName) {
            if (in_array($sheetName, $this->selectedSheets)) {
                $sheets[$sheetName] = new CursoHojaImport(
                    $sheetName,
                    $this->gradoSeccionId,
                    $this->anoLectivoId,
                    $this->bimestreId,
                    $this->adminId,
                    $this
                );
            }
        }

        return $sheets;
    }

    public function onUnknownSheet($sheetName)
    {
        Log::info("Ignoring unknown sheet: " . $sheetName);
    }

    public function addError($error)
    {
        $this->errores[] = $error;
    }

    public function incrementImportedCount($count = 1)
    {
        $this->importadosCount += $count;
    }
}
