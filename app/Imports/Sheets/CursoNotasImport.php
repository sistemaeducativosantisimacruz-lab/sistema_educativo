<?php

namespace App\Imports\Sheets;

use App\Models\Competencia;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\NotaBimestral;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithTitle;

class CursoNotasImport implements ToCollection, WithTitle
{
    protected $bimestreId;
    protected $gradoSeccionId;
    protected $sheetName;
    protected $stats;
    protected $anoActivoId;

    public function __construct($bimestreId, $gradoSeccionId, $sheetName, $stats = null, $anoActivoId = null)
    {
        $this->bimestreId = $bimestreId;
        $this->gradoSeccionId = $gradoSeccionId;
        $this->sheetName = $sheetName;
        $this->stats = $stats;
        $this->anoActivoId = $anoActivoId;
    }

    public function collection(Collection $rows)
    {
        // 1. Extract course code from sheet name, e.g. "0001-ART Y CULT" -> "0001" or "014-CCSS" -> "014"
        // Wait, some codes are 4 digits, some are 3. We can just explode by "-" and take the first part.
        $parts = explode('-', $this->sheetName);
        if (count($parts) < 2) {
            // Not a valid course sheet, ignore.
            return;
        }

        $codigoCurso = trim($parts[0]);
        $curso = Curso::where('codigo', $codigoCurso)->first();

        if (!$curso) {
            Log::warning("CursoNotasImport: Curso no encontrado para la hoja {$this->sheetName} (Código: {$codigoCurso})");
            return;
        }

        // 2. Map competencies dynamically.
        // Row 0 in the collection is Row 1 in Excel.
        // Row 1 in the collection is Row 2 in Excel.
        if ($rows->count() < 3) {
            return; // Not enough data
        }

        $row1 = $rows[0]; // Competencies codes (01, 02, etc.)
        $row2 = $rows[1]; // "NL" and "conclusion_descriptiva"

        $competenciaMap = []; // index => competencia_id
        
        // Find competencies mapping
        foreach ($row1 as $index => $cellValue) {
            $val = trim((string)$cellValue);
            if (is_numeric($val) && strlen($val) === 2) { // "01", "02", etc.
                // It's a competency header
                $orden = (int)$val;
                // Find this competency for this course
                // Assuming `orden` matches the "01", "02" number
                $competencia = Competencia::where('curso_id', $curso->id)->where('orden', $orden)->first();
                if ($competencia) {
                    // Check if row 2 has "NL" below it or slightly shifted. Usually it's in the same column or next.
                    // The user said: "donde la columna diga 'NL' para saber el índice exacto de la nota, y el índice + 1 para la conclusión descriptiva"
                    // Let's search row 2 around this index for "NL"
                    // Actually, let's just find "NL" in row 2 and link it to the closest previous competence code in row 1.
                }
            }
        }

        // Better approach for mapping:
        // Iterate through row 2, find "NL".
        // When we find "NL" at index $i, we look up in row 1 for the competency code (it might be merged, so it might be at $i or earlier).
        $currentCompetenciaId = null;
        for ($i = 0; $i < count($row2); $i++) {
            $colVal1 = trim((string)($row1[$i] ?? ''));
            // SIAGIE headers are "01", "02". Excel might parse them as numbers (1, 2)
            if (is_numeric($colVal1) && (int)$colVal1 > 0 && (int)$colVal1 < 20) {
                $orden = (int)$colVal1;
                $comp = Competencia::where('curso_id', $curso->id)->where('orden', $orden)->first();
                $currentCompetenciaId = $comp ? $comp->id : null;
            }

            $colVal2 = trim(strtoupper((string)($row2[$i] ?? '')));
            if ($colVal2 === 'NL' && $currentCompetenciaId) {
                $competenciaMap[] = [
                    'competencia_id' => $currentCompetenciaId,
                    'nota_index' => $i,
                    'conclusion_index' => $i + 1 // as per user instruction
                ];
            }
        }

        // 3. Process students
        // Start from row 2 (which is Excel Row 3)
        Log::info("CursoNotasImport: Procesando hoja {$this->sheetName}. Competencias mapeadas: " . count($competenciaMap));
        
        $estudiantesProcesados = 0;
        for ($r = 2; $r < $rows->count(); $r++) {
            $row = $rows[$r];
            
            // Check for stop condition: "LEYENDA"
            $colA = trim(strtoupper((string)($row[0] ?? '')));
            $colB = trim(strtoupper((string)($row[1] ?? '')));
            $colC = trim(strtoupper((string)($row[2] ?? '')));

            if (str_contains($colA, 'LEYENDA') || str_contains($colB, 'LEYENDA') || str_contains($colC, 'LEYENDA')) {
                Log::info("CursoNotasImport: LEYENDA encontrada en la fila $r, deteniendo.");
                break;
            }

            // Student code is in column B (index 1)
            // But wait, sometimes SIAGIE puts code in column A or column C depending on the format.
            // Let's assume Column B (index 1) is "CÓDIGO DEL ESTUDIANTE", Column C (index 2) is "NOMBRES Y APELLIDOS".
            $codigoEstudiante = trim((string)($row[1] ?? ''));
            if (empty($codigoEstudiante)) {
                continue;
            }

            $estudiante = Estudiante::where('codigo_estudiante', $codigoEstudiante)->first();
            
            if (!$estudiante) {
                // Fallback 1: Try to match by DNI
                $estudiante = Estudiante::where('dni', $codigoEstudiante)->first();
            }
            
            if (!$estudiante) {
                // Fallback 2: Try to match by Name
                $nombresApe = trim((string)($row[2] ?? ''));
                if ($nombresApe) {
                    $partes = $this->separarNombresCompletos($nombresApe);
                    if ($partes['apellido_paterno']) {
                        $estudiante = Estudiante::where('apellido_paterno', 'ilike', $partes['apellido_paterno'] . '%')
                            ->where('nombres', 'ilike', '%' . $partes['nombres'] . '%')
                            ->first();
                    }
                }
            }

            if (!$estudiante) {
                if ($this->stats) {
                    $this->stats->errores[] = "Hoja {$this->sheetName}, Fila " . ($r + 1) . ": Estudiante con código/DNI {$codigoEstudiante} no encontrado en BD.";
                }
                Log::warning("CursoNotasImport: Estudiante con código/DNI {$codigoEstudiante} no encontrado en BD.");
                continue;
            }

            // Verify if student belongs to the destination section
            if ($this->gradoSeccionId && $this->anoActivoId) {
                $matricula = \App\Models\Matricula::where('estudiante_id', $estudiante->id)
                    ->where('ano_lectivo_id', $this->anoActivoId)
                    ->where('grado_seccion_id', $this->gradoSeccionId)
                    ->first();
                if (!$matricula) {
                    if ($this->stats) {
                        $this->stats->errores[] = "Hoja {$this->sheetName}, Fila " . ($r + 1) . ": El estudiante {$estudiante->nombres} {$estudiante->apellido_paterno} no pertenece a la sección seleccionada.";
                    }
                    continue; // Skip processing grades for this student
                }
            }

            if ($this->stats && !in_array($estudiante->id, $this->stats->procesados)) {
                $this->stats->procesados[] = $estudiante->id;
            }

            // Optional: If we matched by DNI or Name but it didn't have a code, let's update it to save future trouble
            if (empty($estudiante->codigo_estudiante)) {
                $estudiante->update(['codigo_estudiante' => $codigoEstudiante]);
            }

            $estudiantesProcesados++;
            
            // Save grades
            $todasCompetencias = Competencia::where('curso_id', $curso->id)->get();
            
            foreach ($todasCompetencias as $comp) {
                $mapped = collect($competenciaMap)->firstWhere('competencia_id', $comp->id);
                
                $nota = '0';
                $conclusion = null;
                
                if ($mapped) {
                    $valNota = trim((string)($row[$mapped['nota_index']] ?? ''));
                    $valConc = trim((string)($row[$mapped['conclusion_index']] ?? ''));
                    
                    if ($valNota !== '') {
                        $nota = $valNota;
                    }
                    if ($valConc !== '') {
                        $conclusion = $valConc;
                    }
                }

                NotaBimestral::updateOrCreate(
                    [
                        'estudiante_id' => $estudiante->id,
                        'curso_id' => $curso->id,
                        'competencia_id' => $comp->id,
                        'bimestre_id' => $this->bimestreId,
                    ],
                    [
                        'nota' => $nota,
                        'conclusion_descriptiva' => $conclusion,
                    ]
                );
            }
        }
        Log::info("CursoNotasImport: Hoja {$this->sheetName} finalizada. Estudiantes procesados: $estudiantesProcesados.");
    }

    private function separarNombresCompletos($nombreCompleto)
    {
        $nombreCompleto = trim(str_replace('  ', ' ', $nombreCompleto));
        $res = [
            'apellido_paterno' => '',
            'apellido_materno' => '',
            'nombres' => ''
        ];
        
        if (empty($nombreCompleto)) return $res;

        if (str_contains($nombreCompleto, ',')) {
            $parts = explode(',', $nombreCompleto);
            $apellidos = trim($parts[0]);
            $res['nombres'] = trim($parts[1] ?? '');
            
            $apParts = explode(' ', $apellidos);
            if (count($apParts) >= 2) {
                $res['apellido_paterno'] = array_shift($apParts);
                $res['apellido_materno'] = implode(' ', $apParts);
            } else {
                $res['apellido_paterno'] = $apellidos;
            }
        } else {
            $parts = explode(' ', $nombreCompleto);
            if (count($parts) >= 3) {
                $res['apellido_paterno'] = array_shift($parts);
                $res['apellido_materno'] = array_shift($parts);
                $res['nombres'] = implode(' ', $parts);
            } elseif (count($parts) == 2) {
                $res['apellido_paterno'] = $parts[0];
                $res['nombres'] = $parts[1];
            } else {
                $res['nombres'] = $nombreCompleto;
            }
        }
        return $res;
    }

    public function title(): string
    {
        return $this->sheetName;
    }
}
