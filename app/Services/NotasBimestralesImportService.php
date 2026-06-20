<?php

namespace App\Services;

use App\Models\Estudiante;
use App\Models\Curso;
use App\Models\Competencia;
use App\Models\Calificacion;
use App\Models\ImportacionSiagie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotasBimestralesImportService
{
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

    public function import($filePath, $gradoSeccionId, $anoLectivoId, $bimestreId, $adminId, $fileName, array $selectedSheets)
    {
        DB::beginTransaction();
        try {
            // Register import log
            $importacion = ImportacionSiagie::create([
                'admin_id' => $adminId,
                'grado_seccion_id' => $gradoSeccionId,
                'ano_lectivo_id' => $anoLectivoId,
                'nombre_archivo' => $fileName,
                'tipo' => 'notas_bimestrales',
                'estado' => 'exitoso'
            ]);

            $importInstance = new \App\Imports\ActaNotasBimestralImport(
                $selectedSheets,
                $gradoSeccionId,
                $anoLectivoId,
                $bimestreId,
                $adminId
            );

            \Maatwebsite\Excel\Facades\Excel::import($importInstance, $filePath);

            $importadosCount = $importInstance->importadosCount;
            $errores = $importInstance->errores;

            $importacion->update([
                'estudiantes_importados' => $importadosCount,
                'errores' => count($errores) > 0 ? $errores : null,
                'estado' => count($errores) > 0 ? (count($errores) >= count($selectedSheets) ? 'fallido' : 'con_errores') : 'exitoso'
            ]);

            DB::commit();

            return [
                'success' => true,
                'importados' => $importadosCount,
                'errores' => $errores
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en importación de notas SIAGIE: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ocurrió un error crítico durante la importación: ' . $e->getMessage()
            ];
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
