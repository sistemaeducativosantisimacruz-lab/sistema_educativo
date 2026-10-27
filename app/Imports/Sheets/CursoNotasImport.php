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
    protected $estudiantesCache;

    public function __construct($bimestreId, $gradoSeccionId, $sheetName, $stats = null, $anoActivoId = null, $estudiantesCache = null)
    {
        $this->bimestreId = $bimestreId;
        $this->gradoSeccionId = $gradoSeccionId;
        $this->sheetName = $sheetName;
        $this->stats = $stats;
        $this->anoActivoId = $anoActivoId;
        $this->estudiantesCache = $estudiantesCache;
    }

    public function collection(Collection $rows)
    {
        // 1. Extraer código del curso del nombre de la hoja
        $parts = explode('-', $this->sheetName);
        if (count($parts) < 2) {
            return;
        }

        $codigoCurso = trim($parts[0]);
        $curso = Curso::where('codigo', $codigoCurso)->first();

        if (!$curso) {
            Log::warning("CursoNotasImport: Curso no encontrado para la hoja {$this->sheetName} (Código: {$codigoCurso})");
            return;
        }

        if ($rows->count() < 3) {
            return;
        }

        $row1 = $rows[0];
        $row2 = $rows[1];

        // 2. Pre-cargar en memoria las competencias del curso (1 sola consulta en vez de repetitivas)
        $todasCompetencias = Competencia::where('curso_id', $curso->id)->get();
        $competenciasByOrden = $todasCompetencias->keyBy('orden');

        $competenciaMap = [];
        $currentCompetenciaId = null;

        for ($i = 0; $i < count($row2); $i++) {
            $colVal1 = trim((string)($row1[$i] ?? ''));
            if (is_numeric($colVal1) && (int)$colVal1 > 0 && (int)$colVal1 < 20) {
                $orden = (int)$colVal1;
                $comp = $competenciasByOrden->get($orden);
                $currentCompetenciaId = $comp ? $comp->id : null;
            }

            $colVal2 = trim(strtoupper((string)($row2[$i] ?? '')));
            if ($colVal2 === 'NL' && $currentCompetenciaId) {
                $competenciaMap[] = [
                    'competencia_id' => $currentCompetenciaId,
                    'nota_index' => $i,
                    'conclusion_index' => $i + 1
                ];
            }
        }

        if (empty($competenciaMap)) {
            Log::warning("CursoNotasImport: No se encontraron columnas NL de competencias en la hoja {$this->sheetName}.");
            return;
        }

        // 3. Obtener o cargar el mapa en memoria de los estudiantes de la sección
        $byCodigo = $this->estudiantesCache['byCodigo'] ?? [];
        $byDni = $this->estudiantesCache['byDni'] ?? [];
        $matriculadosIds = $this->estudiantesCache['matriculadosIds'] ?? [];

        if (empty($byCodigo) && empty($byDni) && $this->gradoSeccionId && $this->anoActivoId) {
            $matriculas = \App\Models\Matricula::with('estudiante')
                ->where('grado_seccion_id', $this->gradoSeccionId)
                ->where('ano_lectivo_id', $this->anoActivoId)
                ->get();

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
        }

        // 4. Pre-cargar todas las notas existentes en 1 sola consulta agrupada para evitar miles de SELECT individuales
        $estudianteIdsInScope = array_keys($matriculadosIds);
        $existingNotas = collect();
        if (!empty($estudianteIdsInScope)) {
            $existingNotas = NotaBimestral::where('curso_id', $curso->id)
                ->where('bimestre_id', $this->bimestreId)
                ->whereIn('estudiante_id', $estudianteIdsInScope)
                ->get()
                ->keyBy(fn($item) => $item->estudiante_id . '_' . $item->competencia_id);
        }

        $estudiantesProcesados = 0;
        $newRecordsToInsert = [];

        // Ejecutar todo el procesamiento dentro de una transacción para máxima velocidad
        \Illuminate\Support\Facades\DB::transaction(function () use (
            $rows,
            &$byCodigo,
            &$byDni,
            &$matriculadosIds,
            $competenciaMap,
            $todasCompetencias,
            $curso,
            $existingNotas,
            &$estudiantesProcesados,
            &$newRecordsToInsert
        ) {
            for ($r = 2; $r < $rows->count(); $r++) {
                $row = $rows[$r];

                // Condición de fin: "LEYENDA"
                $colA = trim(strtoupper((string)($row[0] ?? '')));
                $colB = trim(strtoupper((string)($row[1] ?? '')));
                $colC = trim(strtoupper((string)($row[2] ?? '')));

                if (str_contains($colA, 'LEYENDA') || str_contains($colB, 'LEYENDA') || str_contains($colC, 'LEYENDA')) {
                    break;
                }

                $codigoEstudiante = trim((string)($row[1] ?? ''));
                if (empty($codigoEstudiante)) {
                    continue;
                }

                // Búsqueda instantánea en memoria (0 consultas SQL)
                $estudiante = $byCodigo[$codigoEstudiante] ?? ($byDni[$codigoEstudiante] ?? null);

                if (!$estudiante) {
                    $estudiante = Estudiante::where('codigo_estudiante', $codigoEstudiante)->first()
                        ?: Estudiante::where('dni', $codigoEstudiante)->first();

                    if (!$estudiante) {
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

                    if ($estudiante) {
                        if (!empty($estudiante->codigo_estudiante)) {
                            $byCodigo[$estudiante->codigo_estudiante] = $estudiante;
                        }
                        if (!empty($estudiante->dni)) {
                            $byDni[$estudiante->dni] = $estudiante;
                        }
                    }
                }

                if (!$estudiante) {
                    if ($this->stats) {
                        $this->stats->errores[] = "Hoja {$this->sheetName}, Fila " . ($r + 1) . ": Estudiante con código/DNI {$codigoEstudiante} no encontrado en BD.";
                    }
                    continue;
                }

                // Verificación de pertenencia a la sección en memoria
                if ($this->gradoSeccionId && $this->anoActivoId) {
                    if (!isset($matriculadosIds[$estudiante->id])) {
                        $matriculaExiste = \App\Models\Matricula::where('estudiante_id', $estudiante->id)
                            ->where('ano_lectivo_id', $this->anoActivoId)
                            ->where('grado_seccion_id', $this->gradoSeccionId)
                            ->exists();

                        if (!$matriculaExiste) {
                            if ($this->stats) {
                                $this->stats->errores[] = "Hoja {$this->sheetName}, Fila " . ($r + 1) . ": El estudiante {$estudiante->nombres} {$estudiante->apellido_paterno} no pertenece a la sección seleccionada.";
                            }
                            continue;
                        }
                        $matriculadosIds[$estudiante->id] = true;
                    }
                }

                if ($this->stats && !in_array($estudiante->id, $this->stats->procesados)) {
                    $this->stats->procesados[] = $estudiante->id;
                }

                if (empty($estudiante->codigo_estudiante)) {
                    $estudiante->update(['codigo_estudiante' => $codigoEstudiante]);
                }

                $estudiantesProcesados++;

                // Procesar calificaciones de las competencias
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

                    $key = $estudiante->id . '_' . $comp->id;
                    $existing = $existingNotas->get($key);

                    if ($existing) {
                        // Actualizar solo si hay cambios reales para ahorrar queries de escritura
                        if ($existing->nota !== $nota || $existing->conclusion_descriptiva !== $conclusion) {
                            $existing->update([
                                'nota' => $nota,
                                'conclusion_descriptiva' => $conclusion,
                            ]);
                        }
                    } else {
                        // Agrupar en lote para inserción masiva (bulk insert)
                        $newRecordsToInsert[] = [
                            'estudiante_id' => $estudiante->id,
                            'curso_id' => $curso->id,
                            'competencia_id' => $comp->id,
                            'bimestre_id' => $this->bimestreId,
                            'nota' => $nota,
                            'conclusion_descriptiva' => $conclusion,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            // Inserción en bloques (Bulk Insert) de notas nuevas
            if (!empty($newRecordsToInsert)) {
                foreach (array_chunk($newRecordsToInsert, 500) as $chunk) {
                    NotaBimestral::insert($chunk);
                }
            }
        });

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
