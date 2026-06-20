<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ejecutar la migración.
     *
     * Crea la tabla de apoderados y migra los datos existentes
     * desde la tabla estudiantes. Luego elimina las columnas
     * de apoderado que ya no pertenecen a estudiantes.
     */
    public function up(): void
    {
        // 1. Crear tabla apoderados
        Schema::create('apoderados', function (Blueprint $table) {
            $table->id();
            // Relación con el estudiante mediante DNI del estudiante
            $table->string('estudiante_dni', 8);
            $table->foreign('estudiante_dni')
                  ->references('dni')
                  ->on('estudiantes')
                  ->onDelete('cascade');
            // Datos del apoderado
            $table->string('dni', 8)->nullable();
            $table->string('nombres')->nullable();
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('parentesco', 50)->default('APODERADO');
            $table->timestamps();
        });

        // 2. Migrar datos existentes de estudiantes hacia apoderados
        $estudiantes = DB::table('estudiantes')
            ->whereNotNull('apoderado_dni')
            ->orWhereNotNull('apoderado_nombre')
            ->orWhereNotNull('direccion')
            ->orWhereNotNull('telefono')
            ->get();

        foreach ($estudiantes as $est) {
            $tieneApoderado = !empty($est->apoderado_dni)
                || !empty($est->apoderado_nombre)
                || !empty($est->direccion)
                || !empty($est->telefono);

            if ($tieneApoderado) {
                DB::table('apoderados')->insert([
                    'estudiante_dni'   => $est->dni,
                    'dni'              => $est->apoderado_dni,
                    'nombres'          => $est->apoderado_nombre,
                    'apellido_paterno' => null,
                    'apellido_materno' => null,
                    'direccion'        => $est->direccion,
                    'telefono'         => $est->telefono,
                    'parentesco'       => 'APODERADO',
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }

        // 3. Eliminar columnas de apoderado y dirección de la tabla estudiantes
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropColumn(['apoderado_dni', 'apoderado_nombre', 'direccion', 'telefono']);
        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        // Restaurar columnas en estudiantes
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->string('direccion')->nullable()->after('sexo');
            $table->string('telefono', 15)->nullable()->after('direccion');
            $table->string('apoderado_nombre')->nullable()->after('telefono');
            $table->string('apoderado_dni', 8)->nullable()->after('apoderado_nombre');
        });

        // Restaurar datos desde apoderados hacia estudiantes
        $apoderados = DB::table('apoderados')->get();
        foreach ($apoderados as $apod) {
            DB::table('estudiantes')
                ->where('dni', $apod->estudiante_dni)
                ->update([
                    'apoderado_dni'    => $apod->dni,
                    'apoderado_nombre' => $apod->nombres,
                    'direccion'        => $apod->direccion,
                    'telefono'         => $apod->telefono,
                ]);
        }

        Schema::dropIfExists('apoderados');
    }
};
