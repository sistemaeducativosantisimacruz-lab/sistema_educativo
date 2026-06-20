<?php

namespace App\Services;

use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\User;
use App\Models\Role;
use App\Models\ImportacionSiagie;
use App\Models\GradoSeccion;
use App\Models\Apoderado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EstudianteImportService
{
    public function import(array $rows, array $mapping, $gradoSeccionId, $anoLectivoId, $adminId, $fileName, $tipo = 'estudiantes')
    {
        $roleEstudiante = Role::where('nombre', 'estudiante')->first();
        
        $errores = [];
        $importados = 0;

        DB::beginTransaction();
        try {
            // Register import attempt
            $importacion = ImportacionSiagie::create([
                'admin_id' => $adminId,
                'grado_seccion_id' => $gradoSeccionId,
                'ano_lectivo_id' => $anoLectivoId,
                'nombre_archivo' => $fileName,
                'tipo' => $tipo,
                'estado' => 'exitoso'
            ]);

            $gs = GradoSeccion::with('grado')->find($gradoSeccionId);
            $nivelDestino = $gs->grado->nivel;

            foreach ($rows as $index => $row) {
                try {
                    if ($tipo === 'directorio') {
                        $this->importarDirectorio($row, $mapping, $gradoSeccionId, $anoLectivoId, $roleEstudiante->id, $nivelDestino);
                    } elseif ($tipo === 'siagie') {
                        $this->importarSiagie($row, $mapping);
                    } elseif ($tipo === 'estudiantes') {
                        $this->importarEstudiante($row, $mapping, $gradoSeccionId, $anoLectivoId, $roleEstudiante->id, $nivelDestino);
                    } elseif ($tipo === 'notas') {
                        $this->importarNotas($row, $mapping, $gradoSeccionId, $anoLectivoId);
                    } else {
                        $this->importarPadre($row, $mapping);
                    }
                    $importados++;
                } catch (\Exception $e) {
                    $errores[] = "Fila " . ($index + 1) . ": " . $e->getMessage();
                }
            }



            $importacion->update([
                'estudiantes_importados' => $importados,
                'errores' => count($errores) > 0 ? json_encode($errores) : null,
                'estado' => count($errores) > 0 ? (count($errores) === count($rows) ? 'fallido' : 'con_errores') : 'exitoso'
            ]);

            DB::commit();

            return [
                'success' => true,
                'importados' => $importados,
                'errores' => $errores
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en importación: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ocurrió un error crítico durante la importación: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Separa un nombre completo en formato "AP_PAT AP_MAT NOMBRES..."
     * Asume que las primeras dos palabras son apellidos.
     */
    private function splitNombreCompleto(string $nombreCompleto): array
    {
        $partes = preg_split('/\s+/', trim($nombreCompleto));
        if (count($partes) < 2) {
            return ['ap_paterno' => $partes[0] ?? '', 'ap_materno' => '', 'nombres' => $partes[0] ?? ''];
        }
        $ap_paterno = $partes[0];
        $ap_materno = count($partes) >= 3 ? $partes[1] : '';
        $nombres    = implode(' ', array_slice($partes, count($partes) >= 3 ? 2 : 1));
        return compact('ap_paterno', 'ap_materno', 'nombres');
    }

    private function importarEstudiante($row, $mapping, $gradoSeccionId, $anoLectivoId, $roleId, $nivel)
    {
        $dni = $this->getValue($row, $mapping, 'dni');

        // Soportar nombre completo en una sola columna
        if (isset($mapping['nombre_completo']) && $mapping['nombre_completo'] !== '') {
            $raw = $this->getValue($row, $mapping, 'nombre_completo');
            $split = $this->splitNombreCompleto($raw ?? '');
            $nombres    = $split['nombres'];
            $ap_paterno = $split['ap_paterno'];
            $ap_materno = $split['ap_materno'];
        } else {
            $nombres    = $this->getValue($row, $mapping, 'nombres');
            $ap_paterno = $this->getValue($row, $mapping, 'apellido_paterno');
            $ap_materno = $this->getValue($row, $mapping, 'apellido_materno') ?? '';
        }

        $fecha_nacimiento = $this->getValue($row, $mapping, 'fecha_nacimiento');
        $sexo = strtoupper($this->getValue($row, $mapping, 'sexo') ?? '');

        if (!$dni || !$nombres || !$ap_paterno) {
            throw new \Exception("Faltan datos obligatorios del estudiante (DNI, Nombres o Apellido Paterno).");
        }

        $sexo = in_array($sexo, ['M', 'F']) ? $sexo : 'M';
        
        if (!$fecha_nacimiento) {
            $fecha_nacimiento = now()->subYears(15)->format('Y-m-d');
        } else {
            if (is_numeric($fecha_nacimiento)) {
                $fecha_nacimiento = \Carbon\Carbon::createFromDate(1900, 1, 1)->addDays($fecha_nacimiento - 2)->format('Y-m-d');
            } else {
                try {
                    if (strpos($fecha_nacimiento, '/') !== false) {
                        $fecha_nacimiento = \Carbon\Carbon::createFromFormat('d/m/Y', trim($fecha_nacimiento))->format('Y-m-d');
                    } else {
                        $fecha_nacimiento = date('Y-m-d', strtotime($fecha_nacimiento));
                    }
                } catch (\Exception $e) {
                    $fecha_nacimiento = now()->subYears(15)->format('Y-m-d');
                }
            }
        }

        $user = User::firstOrCreate(['dni' => $dni], [
            'name' => "$nombres $ap_paterno",
            'email' => $dni,
            'password' => Hash::make($dni),
            'role_id' => $roleId,
            'must_change_password' => true,
        ]);

        $dataEstudiante = [
            'user_id' => $user->id,
            'nombres' => $nombres,
            'apellido_paterno' => $ap_paterno,
            'apellido_materno' => $ap_materno,
            'fecha_nacimiento' => $fecha_nacimiento,
            'sexo' => $sexo,
            'nivel' => $nivel,
            'estado' => 'activo'
        ];

        // Quitado el if ($nivel === 'primaria') para que Secundaria también guarde estos datos
        $dataEstudiante['colegio_inicial'] = $this->getValue($row, $mapping, 'colegio_inicial');

        $estudiante = Estudiante::updateOrCreate(['dni' => $dni], $dataEstudiante);

        // Importar padre explícito si existe
        $padre_dni = $this->getValue($row, $mapping, 'padre_dni');
        $padre_nombres = $this->getValue($row, $mapping, 'padre_nombres');
        if ($padre_dni || $padre_nombres) {
            $split = $this->splitNombreCompleto($padre_nombres ?? '');
            Apoderado::updateOrCreate(
                ['estudiante_dni' => $dni, 'parentesco' => 'PADRE'],
                [
                    'dni' => $padre_dni,
                    'nombres' => mb_strtoupper($split['nombres']),
                    'apellido_paterno' => mb_strtoupper($split['ap_paterno']),
                    'apellido_materno' => mb_strtoupper($split['ap_materno']),
                    'telefono' => $this->getValue($row, $mapping, 'padre_telefono')
                ]
            );
        }

        // Importar madre explícita si existe
        $madre_dni = $this->getValue($row, $mapping, 'madre_dni');
        $madre_nombres = $this->getValue($row, $mapping, 'madre_nombres');
        if ($madre_dni || $madre_nombres) {
            $split = $this->splitNombreCompleto($madre_nombres ?? '');
            Apoderado::updateOrCreate(
                ['estudiante_dni' => $dni, 'parentesco' => 'MADRE'],
                [
                    'dni' => $madre_dni,
                    'nombres' => mb_strtoupper($split['nombres']),
                    'apellido_paterno' => mb_strtoupper($split['ap_paterno']),
                    'apellido_materno' => mb_strtoupper($split['ap_materno']),
                    'telefono' => $this->getValue($row, $mapping, 'madre_telefono')
                ]
            );
        }

        Matricula::firstOrCreate([
            'estudiante_id' => $estudiante->id,
            'ano_lectivo_id' => $anoLectivoId,
        ], [
            'grado_seccion_id' => $gradoSeccionId,
            'estado' => 'matriculado'
        ]);

        // Quitado el if ($nivel === 'primaria') para que Secundaria también vincule apoderado
        if (
            (isset($mapping['apoderado_dni']) && $mapping['apoderado_dni'] !== '') || 
            (isset($mapping['apoderado_nombres']) && $mapping['apoderado_nombres'] !== '')
        ) {
            $mappingApoderado = array_merge($mapping, ['estudiante_dni' => $mapping['dni']]);
            $this->importarPadre($row, $mappingApoderado);
        }
    }

    private function importarPadre($row, $mapping)
    {
        $estudiante_dni = $this->getValue($row, $mapping, 'estudiante_dni');
        $ap_dni = $this->getValue($row, $mapping, 'apoderado_dni');

        // Soportar nombre completo de la madre en una sola columna
        if (isset($mapping['nombre_completo_madre']) && $mapping['nombre_completo_madre'] !== '') {
            $raw = $this->getValue($row, $mapping, 'nombre_completo_madre');
            $split = $this->splitNombreCompleto($raw ?? '');
            $ap_nombres = $split['nombres'];
            $ap_paterno = $split['ap_paterno'];
            $ap_materno = $split['ap_materno'];
        } else {
            $ap_nombres = $this->getValue($row, $mapping, 'apoderado_nombres');
            $ap_paterno = $this->getValue($row, $mapping, 'apoderado_apellido_paterno');
            $ap_materno = $this->getValue($row, $mapping, 'apoderado_apellido_materno') ?? '';

            if ($ap_nombres && !$ap_paterno) {
                $split = $this->splitNombreCompleto($ap_nombres);
                $ap_nombres = $split['nombres'];
                $ap_paterno = $split['ap_paterno'];
                $ap_materno = $split['ap_materno'];
            }
        }

        $ap_parentesco = strtoupper($this->getValue($row, $mapping, 'apoderado_parentesco') ?? '') ?: 'MADRE';
        $ap_telefono = $this->getValue($row, $mapping, 'apoderado_telefono') ?? '';
        
        // Extraer los 9 primeros dígitos numéricos del teléfono
        if ($ap_telefono) {
            $soloNumeros = preg_replace('/[^0-9]/', '', $ap_telefono);
            $ap_telefono = substr($soloNumeros, 0, 9);
        }

        $ap_direccion = $this->getValue($row, $mapping, 'apoderado_direccion') ?? '';

        if (!$estudiante_dni) {
            throw new \Exception("Falta el DNI del estudiante para vincular al apoderado.");
        }

        if (!$ap_dni && !$ap_paterno && !$ap_nombres && !$ap_telefono && !$ap_direccion) {
            // No hay datos suficientes ni para crear un apoderado parcial
            return;
        }

        $estudiante = Estudiante::where('dni', $estudiante_dni)->first();
        if (!$estudiante) {
            throw new \Exception("El estudiante con DNI $estudiante_dni no está registrado.");
        }

        $criterioBusqueda = ['estudiante_dni' => $estudiante_dni];
        if ($ap_dni) {
            $criterioBusqueda['dni'] = $ap_dni;
        }

        Apoderado::updateOrCreate($criterioBusqueda, [
            'dni'              => $ap_dni ?: null,
            'nombres'          => $ap_nombres ?: null,
            'apellido_paterno' => $ap_paterno ?: null,
            'apellido_materno' => $ap_materno ?: null,
            'parentesco'       => $ap_parentesco,
            'telefono'         => $ap_telefono ?: null,
            'direccion'        => $ap_direccion ?: null,
        ]);
    }

    private function importarDirectorio($row, $mapping, $gradoSeccionId, $anoLectivoId, $roleId, $nivel)
    {
        // 1. Importar estudiante
        $this->importarEstudiante($row, $mapping, $gradoSeccionId, $anoLectivoId, $roleId, $nivel);

        // 2. Importar madre/apoderado si hay algun mapeo relacionado
        if (isset($mapping['apoderado_dni']) || isset($mapping['nombre_completo_madre']) || isset($mapping['apoderado_telefono'])) {
            $dni_estudiante = $this->getValue($row, $mapping, 'dni');
            $mappingMadre = array_merge($mapping, ['estudiante_dni' => $mapping['dni']]);
            $this->importarPadre($row, $mappingMadre);
        }
    }

    private function importarSiagie($row, $mapping)
    {
        $nombreCompleto = $this->getValue($row, $mapping, 'nombre_completo');
        $codigoEstudiante = $this->getValue($row, $mapping, 'codigo_estudiante');

        if (!$nombreCompleto || !$codigoEstudiante) {
            throw new \Exception("Faltan datos obligatorios (Nombre Completo o Cód. Estudiante).");
        }

        $partes = $this->splitNombreCompleto($nombreCompleto);
        $apPaterno = mb_strtoupper($partes['ap_paterno']);
        $apMaterno = mb_strtoupper($partes['ap_materno']);
        $nombres = mb_strtoupper($partes['nombres']);

        $estudiante = Estudiante::where('apellido_paterno', $apPaterno)
            ->where('apellido_materno', $apMaterno)
            ->where('nombres', $nombres)
            ->first();

        if (!$estudiante) {
            // Intento más relajado si hay error de tipeo
            $estudiante = Estudiante::where('apellido_paterno', 'ilike', "%{$apPaterno}%")
                ->where('nombres', 'ilike', "%{$nombres}%")
                ->first();
        }

        if (!$estudiante) {
            throw new \Exception("Estudiante no encontrado en la base de datos: " . $nombreCompleto);
        }

        $estudiante->update([
            'codigo_estudiante' => $codigoEstudiante
        ]);
    }

    private function importarNotas($row, $mapping, $gradoSeccionId, $anoLectivoId)
    {
        $dni = $this->getValue($row, $mapping, 'estudiante_dni');
        
        if (!$dni) {
            throw new \Exception("Falta DNI del estudiante para importar sus notas.");
        }

        $estudiante = Estudiante::where('dni', $dni)->first();
        if (!$estudiante) {
            throw new \Exception("Estudiante con DNI $dni no encontrado.");
        }

        // Si tienes datos de calificaciones en el mismo archivo, podrías leerlos aquí 
        // e insertar registros en la tabla notas_bimestrales vinculados al $estudiante->id.
        // Pero típicamente esto se hace en un import separado o usando pestañas/columnas específicas.
    }

    private function getValue($row, $mapping, $field)
    {
        if (isset($mapping[$field]) && isset($row[$mapping[$field]])) {
            return trim($row[$mapping[$field]]);
        }
        return null;
    }
}
