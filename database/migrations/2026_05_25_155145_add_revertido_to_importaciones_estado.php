<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE importaciones_siagie MODIFY COLUMN estado ENUM('exitoso', 'con_errores', 'fallido', 'revertido') NOT NULL DEFAULT 'exitoso'");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE importaciones_siagie DROP CONSTRAINT IF EXISTS importaciones_siagie_estado_check");
            DB::statement("ALTER TABLE importaciones_siagie ADD CONSTRAINT importaciones_siagie_estado_check CHECK (estado::text = ANY (ARRAY['exitoso'::character varying, 'con_errores'::character varying, 'fallido'::character varying, 'revertido'::character varying]::text[]))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE importaciones_siagie MODIFY COLUMN estado ENUM('exitoso', 'con_errores', 'fallido') NOT NULL DEFAULT 'exitoso'");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE importaciones_siagie DROP CONSTRAINT IF EXISTS importaciones_siagie_estado_check");
            DB::statement("ALTER TABLE importaciones_siagie ADD CONSTRAINT importaciones_siagie_estado_check CHECK (estado::text = ANY (ARRAY['exitoso'::character varying, 'con_errores'::character varying, 'fallido'::character varying]::text[]))");
        }
    }
};
