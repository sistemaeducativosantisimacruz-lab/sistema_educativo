<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('estudiantes', function (Blueprint $table) {
            $table->string('dni', 50)->change();
            $table->string('padre_dni', 50)->nullable()->change();
            $table->string('madre_dni', 50)->nullable()->change();
        });

        Schema::table('apoderados', function (Blueprint $table) {
            $table->string('dni', 50)->nullable()->change();
            $table->string('estudiante_dni', 50)->change();
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('estudiantes', function (Blueprint $table) {
            $table->string('dni', 8)->change();
            $table->string('padre_dni', 8)->nullable()->change();
            $table->string('madre_dni', 8)->nullable()->change();
        });

        Schema::table('apoderados', function (Blueprint $table) {
            $table->string('dni', 8)->change();
            $table->string('estudiante_dni', 8)->change();
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
