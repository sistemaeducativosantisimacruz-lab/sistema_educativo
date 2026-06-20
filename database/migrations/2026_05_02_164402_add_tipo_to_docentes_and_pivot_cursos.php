<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cambios:
     * 1. Agrega 'tipo' a docentes: 'especialista' (enseña curso(s) específico(s))
     *    o 'polidocente' (enseña todo a un grado, como en primaria 1°–4°).
     * 2. Hace 'curso_id' nullable (los polidocentes no tienen curso específico).
     * 3. Crea tabla pivote 'docente_cursos' para que un especialista
     *    pueda enseñar múltiples cursos.
     */
    public function up(): void
    {
        // 1. Modificar tabla docentes
        Schema::table('docentes', function (Blueprint $table) {
            // Tipo de docencia
            $table->enum('tipo', ['especialista', 'polidocente'])
                  ->default('especialista')
                  ->after('nivel');

            // curso_id pasa a ser nullable (polidocentes no tienen curso específico)
            $table->foreignId('curso_id')
                  ->nullable()
                  ->change();
        });

        // 2. Crear tabla pivote docente_cursos (many-to-many docentes ↔ cursos)
        Schema::create('docente_cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')
                  ->constrained('docentes')
                  ->cascadeOnDelete();
            $table->foreignId('curso_id')
                  ->constrained('cursos')
                  ->cascadeOnDelete();
            $table->timestamps();

            // Un docente no puede tener el mismo curso dos veces
            $table->unique(['docente_id', 'curso_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docente_cursos');

        Schema::table('docentes', function (Blueprint $table) {
            $table->dropColumn('tipo');
            // Restaurar curso_id como NOT NULL
            $table->foreignId('curso_id')
                  ->nullable(false)
                  ->change();
        });
    }
};
