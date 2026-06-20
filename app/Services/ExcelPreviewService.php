<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * Filtro de lectura que solo carga las primeras N filas para previsualización.
 * Esto evita cargar archivos Excel completos en memoria.
 */
class PreviewReadFilter implements IReadFilter
{
    private int $maxRow;

    public function __construct(int $maxRow = 400)
    {
        $this->maxRow = $maxRow;
    }

    public function readCell($columnAddress, $row, $worksheetName = ''): bool
    {
        return $row <= $this->maxRow;
    }
}

class ExcelPreviewService
{
    public function parse($filePath)
    {
        // Detectar el tipo de archivo y crear el reader apropiado
        $inputFileType = IOFactory::identify($filePath);
        $reader = IOFactory::createReader($inputFileType);
        
        // Configurar el reader para leer solo las primeras 400 filas para preview amplio
        $reader->setReadFilter(new PreviewReadFilter(400));
        $reader->setReadDataOnly(true);
        
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $allRows = $sheet->toArray(null, true, true, false);

        // Liberar memoria del spreadsheet
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        if (empty($allRows)) {
            throw new \Exception('El archivo Excel está vacío o no se pudo leer.');
        }

        // Clean up empty rows
        $cleanedRows = collect($allRows)
            ->filter(function($row) {
                // Filter out completely empty rows
                return collect($row)->filter(fn($cell) => $cell !== null && $cell !== '')->isNotEmpty();
            })
            ->values();

        if ($cleanedRows->isEmpty()) {
            throw new \Exception('El archivo Excel está vacío o no se pudo leer.');
        }

        // Find the header row by looking for typical headers
        $headerIndex = 0;
        foreach ($cleanedRows as $idx => $row) {
            $rowText = implode(' ', array_map('strtolower', array_map('strval', $row)));
            if (str_contains($rowText, 'código') || str_contains($rowText, 'codigo') || str_contains($rowText, 'estudiante') || str_contains($rowText, 'apellidos') || str_contains($rowText, 'nombres') || str_contains($rowText, 'dni')) {
                $headerIndex = $idx;
                break;
            }
        }
        
        $headerRow = $cleanedRows[$headerIndex];
        $rawHeaders = collect($headerRow)->map(fn($h) => (string) ($h ?? ''))->toArray();
        
        // Ensure no duplicate or empty headers
        $seen = [];
        $headers = array_map(function($h, $idx) use (&$seen) {
            $base = trim($h) === '' ? 'Col_' . ($idx + 1) : trim($h);
            if (isset($seen[$base])) {
                $seen[$base]++;
                return $base . '_' . $seen[$base];
            }
            $seen[$base] = 1;
            return $base;
        }, $rawHeaders, array_keys($rawHeaders));

        // Map data rows to associative arrays with headers as keys
        $rows = $cleanedRows->slice($headerIndex + 1)->take(300)->values()->map(function($row) use ($headers) {
            $mapped = [];
            foreach ($headers as $idx => $h) {
                $mapped[$h] = $row[$idx] ?? null;
            }
            return $mapped;
        })->toArray();

        return [
            'headers' => array_values($headers),
            'rows' => $rows
        ];
    }

    public function parseComplete($filePath)
    {
        $inputFileType = IOFactory::identify($filePath);
        $reader = IOFactory::createReader($inputFileType);
        $reader->setReadDataOnly(true); // No filter -> read all rows

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        
        $allRows = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, false);

        $cleanedRows = collect($allRows)->filter(function ($row) {
            return count(array_filter($row)) > 0;
        })->values();

        if ($cleanedRows->isEmpty()) {
            throw new \Exception('El archivo Excel está vacío o no se pudo leer.');
        }

        $headerIndex = 0;
        foreach ($cleanedRows as $idx => $row) {
            $rowText = implode(' ', array_map('strtolower', array_map('strval', $row)));
            if (str_contains($rowText, 'código') || str_contains($rowText, 'codigo') || str_contains($rowText, 'estudiante') || str_contains($rowText, 'apellidos') || str_contains($rowText, 'nombres') || str_contains($rowText, 'dni')) {
                $headerIndex = $idx;
                break;
            }
        }
        
        $headerRow = $cleanedRows[$headerIndex];
        $rawHeaders = collect($headerRow)->map(fn($h) => (string) ($h ?? ''))->toArray();
        
        $seen = [];
        $headers = array_map(function($h, $idx) use (&$seen) {
            $base = trim($h) === '' ? 'Col_' . ($idx + 1) : trim($h);
            if (isset($seen[$base])) {
                $seen[$base]++;
                return $base . '_' . $seen[$base];
            }
            $seen[$base] = 1;
            return $base;
        }, $rawHeaders, array_keys($rawHeaders));

        // Read all rows below header, no take() limit
        $rows = $cleanedRows->slice($headerIndex + 1)->values()->map(function($row) use ($headers) {
            $mapped = [];
            foreach ($headers as $idx => $h) {
                $mapped[$h] = $row[$idx] ?? null;
            }
            return $mapped;
        })->toArray();

        return [
            'headers' => array_values($headers),
            'rows' => $rows
        ];
    }

    public function parseAllSheets($filePath)
    {
        // Usar un filtro de lectura limitado también para parseAllSheets
        $inputFileType = IOFactory::identify($filePath);
        $reader = IOFactory::createReader($inputFileType);
        $reader->setReadFilter(new PreviewReadFilter(50));
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($filePath);
        $sheetNames = $spreadsheet->getSheetNames();
        
        $sheets = [];
        foreach ($sheetNames as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $allRows = $sheet->toArray(null, true, true, false);
            
            if (empty($allRows)) {
                continue;
            }
            
            // Clean up completely empty rows
            $cleanedRows = collect($allRows)
                ->filter(function($row) {
                    return collect($row)->filter(fn($cell) => $cell !== null && $cell !== '')->isNotEmpty();
                })
                ->values();
                
            if ($cleanedRows->isEmpty()) {
                continue;
            }
            
            // Find the header row by looking for a row containing typical headers
            $headerIndex = 0;
            foreach ($cleanedRows as $idx => $row) {
                $rowText = implode(' ', array_map('strtolower', array_map('strval', $row)));
                if (str_contains($rowText, 'código') || str_contains($rowText, 'codigo') || str_contains($rowText, 'estudiante') || str_contains($rowText, 'apellidos')) {
                    $headerIndex = $idx;
                    break;
                }
            }
            
            $headerRow = $cleanedRows[$headerIndex];
            $headers = collect($headerRow)->map(fn($h) => (string) ($h ?? ''))->toArray();
            
            // Take first 10 data rows for preview to keep response light
            $dataRows = $cleanedRows->slice($headerIndex + 1)->take(10)->values()->toArray();
            
            $sheets[] = [
                'name' => $sheetName,
                'headers' => $headers,
                'rows' => $dataRows
            ];
        }

        // Liberar memoria
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        
        return $sheets;
    }
}


