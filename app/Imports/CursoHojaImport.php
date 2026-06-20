<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\BeforeSheet;
use Illuminate\Support\Collection;
use App\Models\Estudiante;
use App\Models\Curso;
use App\Models\Competencia;
use App\Models\NotaBimestral;

class CursoHojaImport implements ToCollection, WithStartRow, WithEvents
{
    use RegistersEventListeners;

    protected $sheetName;
    protected $gradoSeccionId;
    protected $anoLectivoId;
    protected $bimestreId;
    protected $adminId;
    protected $parentImport;
    protected $headers = [];

    private $competenciasPorCurso = [
        'ART' => [
            'Aprecia de manera crítica manifestaciones artístico-culturales.',
            'Crea proyectos desde los lenguajes artísticos.'
        ],
        'CAS' => [
            'Se comunica oralmente en castellano como segunda lengua.',
            'Lee diversos tipos de textos escritos en castellano como segunda lengua.',
            'Escribe diversos tipos de textos en castellano como segunda lengua.'
        ],
        'CTA' => [
            'Indaga mediante métodos científicos para construir conocimientos.',
            'Explica el mundo físico basándose en conocimientos sobre los seres vivos, materia y energía, biodiversidad, Tierra y universo.',
            'Diseña y construye soluciones tecnológicas para resolver problemas de su entorno.'
        ],
        'DPCC' => [
            'Construye su identidad.',
            'Convive y participa democráticamente en la búsqueda del bien común.'
        ],
        'CS' => [
            'Construye interpretaciones históricas.',
            'Gestiona responsablemente el espacio y el ambiente.',
            'Gestiona responsablemente los recursos económicos.'
        ],
        'COM' => [
            'Se comunica oralmente en su lengua materna.',
            'Lee diversos tipos de textos escritos en su lengua materna.',
            'Escribe diversos tipos de textos en su lengua materna.'
        ],
        'EF' => [
            'Se desenvuelve de manera autónoma a través de su motricidad.',
            'Asume una vida saludable.',
            'Interactúa a través de sus habilidades sociomotrices.'
        ],
        'EPT' => [
            'Gestiona proyectos de emprendimiento económico o social.'
        ],
        'ER' => [
            'Construye su identidad como persona humana, amada por Dios, digna, libre y trascendente, comprendiendo la doctrina de su propia religión, abierto al diálogo con las que le son cercanas.',
            'Asume la experiencia del encuentro personal y comunitario con Dios en su proyecto de vida en coherencia con su creencia religiosa.'
        ],
        'ING' => [
            'Se comunica oralmente en inglés como idioma extranjero.',
            'Lee diversos tipos de textos escritos en inglés como idioma extranjero.',
            'Escribe diversos tipos de textos en inglés como idioma extranjero.'
        ],
        'MAT' => [
            'Resuelve problemas de cantidad.',
            'Resuelve problemas de regularidad, equivalencia y cambio.',
            'Resuelve problemas de forma, movimiento y localización.',
            'Resuelve problemas de gestión de datos e incertidumbre.'
        ],
        'TIC' => [
            'Se desenvuelve en entornos virtuales generados por las TIC.'
        ],
        'AUT' => [
            'Gestiona su aprendizaje de manera autónoma.'
        ]
    ];

    public function __construct($sheetName, $gradoSeccionId, $anoLectivoId, $bimestreId, $adminId, ActaNotasBimestralImport $parentImport)
    {
        $this->sheetName = $sheetName;
        $this->gradoSeccionId = $gradoSeccionId;
        $this->anoLectivoId = $anoLectivoId;
        $this->bimestreId = $bimestreId;
        $this->adminId = $adminId;
        $this->parentImport = $parentImport;
    }

    public function startRow(): int
    {
        return 3;
    }

    public function beforeSheet(BeforeSheet $event)
    {
        $worksheet = $event->sheet->getDelegate();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        
        $this->headers = [];
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $this->headers[] = $worksheet->getCellByColumnAndRow($col, 2)->getValue();
        }
    }

    public function collection(Collection $rows)
    {
        if (empty($this->headers)) {
            $this->parentImport->addError("Error procesando hoja '{$this->sheetName}': No se pudieron leer las cabeceras en la fila 2.");
            return;
        }

        // Find Student Code column index
        $codeColIndex = $this->findStudentCodeColumn($this->headers);
        
        // Find Grade/Competency columns
        $compColumns = $this->getCompetencyColumns($this->headers, $codeColIndex);

        if (empty($compColumns)) {
            $this->parentImport->addError("No se detectaron columnas de competencias en la hoja '{$this->sheetName}'.");
            return;
        }

        // Get or create course
        $curso = $this->obtenerCursoPorNombreHoja($this->sheetName);

        // Get competencies definitions for this course
        $competenciasList = $this->competenciasPorCurso[$curso->codigo] ?? [];

        // For each student row
        foreach ($rows as $rowIdx => $row) {
            $rowArray = $row->toArray();
            
            $studentCode = isset($rowArray[$codeColIndex]) ? trim((string)$rowArray[$codeColIndex]) : null;
            
            if (!$studentCode) {
                continue; // Skip rows without student code (could be empty spaces or sub-totals)
            }

            // Find student by code
            $estudiante = Estudiante::where('codigo_estudiante', $studentCode)->first();
            if (!$estudiante) {
                // Since startRow is 3, $rowIdx = 0 corresponds to row 3 of the sheet
                $realRowNumber = $rowIdx + 3;
                $this->parentImport->addError("Hoja '{$this->sheetName}', Fila {$realRowNumber}: Estudiante con código '{$studentCode}' no está registrado en el sistema.");
                continue;
            }

            // Save grades for each detected competency column
            foreach ($compColumns as $compIndex => $colInfo) {
                $val = isset($rowArray[$colInfo['index']]) ? trim((string)$rowArray[$colInfo['index']]) : null;
                
                // Normalizar la nota a letras válidas (por ejemplo, AD, A, B, C)
                if ($val !== null && $val !== '') {
                    $val = strtoupper($val);
                } else {
                    continue; // Skip empty grades
                }

                // Detectar si hay columna de conclusión descriptiva / observación a la derecha
                $obsVal = null;
                $nextIdx = $colInfo['index'] + 1;
                if (isset($this->headers[$nextIdx])) {
                    $nextHeader = mb_strtolower(trim((string)$this->headers[$nextIdx]), 'UTF-8');
                    if (
                        $nextHeader === '' || 
                        str_contains($nextHeader, 'conclusión') || 
                        str_contains($nextHeader, 'conclusion') || 
                        str_contains($nextHeader, 'descriptiva') || 
                        str_contains($nextHeader, 'observa')
                    ) {
                        $obsVal = isset($rowArray[$nextIdx]) ? trim((string)$rowArray[$nextIdx]) : null;
                        if ($obsVal !== null && trim($obsVal) === '') {
                            $obsVal = null;
                        }
                    }
                }

                // Find or create competency in database by order
                $compName = $competenciasList[$compIndex] ?? "Competencia " . ($compIndex + 1);
                $competencia = Competencia::where('curso_id', $curso->id)
                    ->where('orden', $compIndex + 1)
                    ->first();

                if (!$competencia) {
                    $competencia = Competencia::create([
                        'curso_id' => $curso->id,
                        'nombre'   => $compName,
                        'orden'    => $compIndex + 1
                    ]);
                }

                // Upsert grade
                NotaBimestral::updateOrCreate([
                    'estudiante_id'  => $estudiante->id,
                    'curso_id'       => $curso->id,
                    'competencia_id' => $competencia->id,
                    'bimestre_id'    => $this->bimestreId,
                ], [
                    'nota'                   => $val,
                    'conclusion_descriptiva' => $obsVal,
                ]);

                $this->parentImport->incrementImportedCount();
            }
        }
    }

    private function findStudentCodeColumn(array $headers)
    {
        foreach ($headers as $idx => $header) {
            $h = mb_strtolower(trim((string)$header), 'UTF-8');
            if (
                str_contains($h, 'código') || 
                str_contains($h, 'codigo') || 
                $h === 'cod' || 
                $h === 'cod.' || 
                (str_contains($h, 'estudiante') && (str_contains($h, 'cod') || str_contains($h, 'id')))
            ) {
                return $idx;
            }
        }
        return 1; // Default fallback to column index 1
    }

    private function getCompetencyColumns(array $headers, int $codeColIndex)
    {
        // Find Nombres column index to ignore all student detail columns before it
        $nameColIndex = -1;
        foreach ($headers as $idx => $header) {
            $h = mb_strtolower(trim((string)$header), 'UTF-8');
            if (str_contains($h, 'nombre') || str_contains($h, 'apellido')) {
                $nameColIndex = $idx;
                break;
            }
        }

        $columns = [];
        foreach ($headers as $idx => $header) {
            if ($idx === $codeColIndex) continue;
            if ($nameColIndex !== -1 && $idx <= $nameColIndex) continue;
            
            $h = mb_strtolower(trim((string)$header), 'UTF-8');
            
            if (
                $h === '' || 
                $h === 'id' ||
                $h === 'dni' ||
                $h === 'n°' || 
                $h === 'nro' || 
                $h === 'nº' || 
                $h === 'orden' ||
                str_contains($h, 'estudiante') || 
                str_contains($h, 'alumno') || 
                str_contains($h, 'nombre') || 
                str_contains($h, 'apellido') || 
                str_contains($h, 'conclusión') || 
                str_contains($h, 'conclusion') || 
                str_contains($h, 'descriptiva') || 
                str_contains($h, 'observa') || 
                str_contains($h, 'promedio') || 
                str_contains($h, 'nota final') || 
                str_contains($h, 'situación') || 
                str_contains($h, 'situacion')
            ) {
                continue;
            }
            
            $columns[] = [
                'index' => $idx,
                'name' => $header
            ];
        }
        return $columns;
    }

    private function obtenerCursoPorNombreHoja($sheetName)
    {
        $cleaned = trim($sheetName);
        if (preg_match('/^\d+\s*-\s*(.+)$/', $cleaned, $matches)) {
            $cleaned = trim($matches[1]);
        }
        
        $mapeo = [
            'ART Y CULT'  => 'ART',
            'ART'         => 'ART',
            'CAST SEGNL'  => 'CAS',
            'CIENC TEC'   => 'CTA',
            'CTA'         => 'CTA',
            'DESARR PCC'  => 'DPCC',
            'DPCC'        => 'DPCC',
            'CCSS'        => 'CS',
            'COMU'        => 'COM',
            'COM'         => 'COM',
            'EFIS'        => 'EF',
            'EF'          => 'EF',
            'ETRA'        => 'EPT',
            'EPT'         => 'EPT',
            'EREL'        => 'ER',
            'ER'          => 'ER',
            'INGL'        => 'ING',
            'ING'         => 'ING',
            'MATE'        => 'MAT',
            'MAT'         => 'MAT',
            'DESEN TIC'   => 'TIC',
            'GEST AUTO'   => 'AUT',
            'TOE'         => 'TOE',
        ];
        
        $codigoBuscado = $mapeo[strtoupper($cleaned)] ?? strtoupper($cleaned);
        
        $curso = Curso::where('codigo', $codigoBuscado)->first();
        
        if (!$curso) {
            $curso = Curso::where('nombre', 'ilike', "%{$cleaned}%")->first();
        }
        
        if (!$curso) {
            $nombresAmigables = [
                'CAS' => 'Castellano como Segunda Lengua',
                'TIC' => 'Se desenvuelve en entornos virtuales generados por las TIC',
                'AUT' => 'Gestiona su aprendizaje de manera autónoma',
            ];
            
            $nombreCurso = $nombresAmigables[$codigoBuscado] ?? ucwords(strtolower($cleaned));
            
            $curso = Curso::create([
                'codigo' => $codigoBuscado,
                'nombre' => $nombreCurso,
                'activo' => true,
                'nivel'  => 'ambos'
            ]);
        }
        
        return $curso;
    }
}
