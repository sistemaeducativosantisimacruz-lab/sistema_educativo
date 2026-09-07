<?php

namespace App\Services;

use App\Models\Apoderado;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\AnoLectivo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EstudianteService
{
    /**
     * Crea un estudiante, su usuario, sus apoderados y lo matricula en el año activo.
     *
     * @param array $data Los datos validados del request.
     * @param AnoLectivo $anoActivo El año lectivo activo.
     * @return Estudiante
     */
    public function crearEstudianteYMatricular(array $data, AnoLectivo $anoActivo): Estudiante
    {
        return DB::transaction(function () use ($data, $anoActivo) {
            // 1. Crear el usuario
            $user = User::create([
                'name'                 => mb_strtoupper("{$data['nombres']} {$data['apellido_paterno']}"),
                'email'                => $data['dni'],
                'dni'                  => $data['dni'],
                'password'             => Hash::make($data['dni']),
                'role_id'              => Role::where('nombre', 'estudiante')->value('id'),
                'must_change_password' => true,
            ]);

            // 2. Crear el estudiante
            $estudiante = Estudiante::create([
                'user_id'           => $user->id,
                'dni'               => $data['dni'],
                'codigo_estudiante' => $data['codigo_estudiante'] ?? null,
                'apellido_paterno'  => $data['apellido_paterno'],
                'apellido_materno'  => $data['apellido_materno'],
                'nombres'           => $data['nombres'],
                'fecha_nacimiento'  => $data['fecha_nacimiento'],
                'sexo'              => $data['sexo'],
                'nivel'             => $data['nivel'],
                'estado'            => 'activo',
                'colegio_inicial'   => $data['colegio_inicial'] ?? null,
            ]);

            // 3. Guardar familiares en tabla apoderados
            if (!empty($data['padre_dni']) || !empty($data['padre_nombres'])) {
                Apoderado::create([
                    'estudiante_dni' => $estudiante->dni,
                    'dni'            => $data['padre_dni'] ?? null,
                    'nombres'        => mb_strtoupper($data['padre_nombres'] ?? ''),
                    'telefono'       => $data['padre_telefono'] ?? null,
                    'parentesco'     => 'PADRE',
                    'es_apoderado'   => false,
                ]);
            }

            if (!empty($data['madre_dni']) || !empty($data['madre_nombres'])) {
                Apoderado::create([
                    'estudiante_dni' => $estudiante->dni,
                    'dni'            => $data['madre_dni'] ?? null,
                    'nombres'        => mb_strtoupper($data['madre_nombres'] ?? ''),
                    'telefono'       => $data['madre_telefono'] ?? null,
                    'parentesco'     => 'MADRE',
                    'es_apoderado'   => false,
                ]);
            }

            // 4. Registrar matrícula
            Matricula::create([
                'estudiante_id'    => $estudiante->id,
                'grado_seccion_id' => $data['grado_seccion_id'],
                'ano_lectivo_id'   => $anoActivo->id,
                'estado'           => 'matriculado',
                'tipo_matricula'   => $data['tipo_matricula'],
            ]);

            // 5. Guardar/Actualizar Apoderado Titular
            if (!empty($data['apoderado_dni']) && !empty($data['apoderado_nombres'])) {
                $esPadre = (!empty($data['padre_dni']) && $data['apoderado_dni'] === $data['padre_dni']);
                $esMadre = (!empty($data['madre_dni']) && $data['apoderado_dni'] === $data['madre_dni']);
                
                $parentesco = $data['apoderado_parentesco'] ?? null;
                if ($esPadre) $parentesco = 'PADRE';
                if ($esMadre) $parentesco = 'MADRE';

                Apoderado::updateOrCreate(
                    [
                        'estudiante_dni' => $estudiante->dni,
                        'dni'            => $data['apoderado_dni'],
                    ],
                    [
                        'nombres'          => $data['apoderado_nombres'],
                        'apellido_paterno' => $data['apoderado_apellido_paterno'] ?? null,
                        'apellido_materno' => $data['apoderado_apellido_materno'] ?? null,
                        'direccion'        => $data['apoderado_direccion'] ?? null,
                        'telefono'         => $data['apoderado_telefono'] ?? null,
                        'parentesco'       => mb_strtoupper($parentesco ?? 'OTRO'),
                        'es_apoderado'     => true,
                    ]
                );
            }

            return $estudiante;
        });
    }
}
