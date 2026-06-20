<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

if (!function_exists('separarNombresCompletosMigration')) {
    function separarNombresCompletosMigration($nombreCompleto)
    {
        $nombreCompleto = trim(str_replace('  ', ' ', $nombreCompleto));
        $res = ['apellido_paterno' => '', 'apellido_materno' => '', 'nombres' => ''];
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
}

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('apoderados', function (Blueprint $table) {
            $table->boolean('es_apoderado')->default(false)->after('parentesco');
        });

        DB::table('apoderados')->update(['es_apoderado' => true]);

        $estudiantes = DB::table('estudiantes')->get();

        foreach ($estudiantes as $est) {
            // Migrar Padre
            if (!empty($est->padre_dni) || !empty($est->padre_nombres)) {
                $exists = false;
                if (!empty($est->padre_dni)) {
                    $exists = DB::table('apoderados')->where('estudiante_dni', $est->dni)->where('dni', $est->padre_dni)->exists();
                }

                if (!$exists) {
                    $apellidosNombres = separarNombresCompletosMigration($est->padre_nombres ?? '');
                    DB::table('apoderados')->insert([
                        'estudiante_dni' => $est->dni,
                        'dni' => $est->padre_dni,
                        'nombres' => mb_strtoupper($apellidosNombres['nombres']),
                        'apellido_paterno' => mb_strtoupper($apellidosNombres['apellido_paterno']),
                        'apellido_materno' => mb_strtoupper($apellidosNombres['apellido_materno']),
                        'telefono' => $est->padre_telefono,
                        'parentesco' => 'PADRE',
                        'es_apoderado' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('apoderados')
                        ->where('estudiante_dni', $est->dni)
                        ->where('dni', $est->padre_dni)
                        ->update(['parentesco' => 'PADRE']);
                }
            }

            // Migrar Madre
            if (!empty($est->madre_dni) || !empty($est->madre_nombres)) {
                $exists = false;
                if (!empty($est->madre_dni)) {
                    $exists = DB::table('apoderados')->where('estudiante_dni', $est->dni)->where('dni', $est->madre_dni)->exists();
                }
                
                if (!$exists) {
                    $apellidosNombres = separarNombresCompletosMigration($est->madre_nombres ?? '');
                    DB::table('apoderados')->insert([
                        'estudiante_dni' => $est->dni,
                        'dni' => $est->madre_dni,
                        'nombres' => mb_strtoupper($apellidosNombres['nombres']),
                        'apellido_paterno' => mb_strtoupper($apellidosNombres['apellido_paterno']),
                        'apellido_materno' => mb_strtoupper($apellidosNombres['apellido_materno']),
                        'telefono' => $est->madre_telefono,
                        'parentesco' => 'MADRE',
                        'es_apoderado' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('apoderados')
                        ->where('estudiante_dni', $est->dni)
                        ->where('dni', $est->madre_dni)
                        ->update(['parentesco' => 'MADRE']);
                }
            }
        }

        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropColumn([
                'padre_dni',
                'padre_nombres',
                'padre_telefono',
                'madre_dni',
                'madre_nombres',
                'madre_telefono'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apoderados', function (Blueprint $table) {
            //
        });
    }
};
