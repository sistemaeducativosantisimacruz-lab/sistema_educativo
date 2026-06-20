<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega el campo 'nivel' a las tablas cursos y docentes.
     * Los cursos se clasifican por nivel educativo (primaria / secundaria / ambos).
     * Los docentes indican en qué nivel enseñan principalmente.
     */
    public function up(): void
    {
        // Añadir nivel a la tabla cursos
        Schema::table('cursos', function (Blueprint $table) {
            $table->enum('nivel', ['primaria', 'secundaria', 'ambos'])
                  ->default('ambos')
                  ->after('activo');
        });

        // Añadir nivel a la tabla docentes
        Schema::table('docentes', function (Blueprint $table) {
            $table->enum('nivel', ['primaria', 'secundaria'])
                  ->default('secundaria')
                  ->after('curso_id');
        });
    }

    /**
     * Revertir los cambios.
     */
    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn('nivel');
        });

        Schema::table('docentes', function (Blueprint $table) {
            $table->dropColumn('nivel');
        });
    }
};
