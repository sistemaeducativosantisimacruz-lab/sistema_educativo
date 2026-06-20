<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Services/ExcelPreviewService.php';

header('Content-Type: text/plain');
try {
    $service = new App\Services\ExcelPreviewService();
    $res = $service->parse(__DIR__ . '/../vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Calculation/locale/Translations.xlsx');
    echo "SUCCESS\n";
    echo "Headers: " . count($res['headers']) . "\n";
    echo "Rows: " . count($res['rows']) . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
