<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega 'curso_id' a asignaciones_docente.
     * Un docente especialista puede tener múltiples asignaciones en la misma
     * sección (una por cada curso que enseña). Para polidocentes curso_id = NULL.
     *
     * El nuevo constraint de unicidad es: docente + sección + curso + año.
     */
    public function up(): void
    {
        Schema::table('asignaciones_docente', function (Blueprint $table) {
            // MySQL no permite drop unique si hay una FK que lo usa.
            // Hay que soltar las FKs primero, luego el índice, luego recrearlas.
            $table->dropForeign(['docente_id']);
            $table->dropForeign(['grado_seccion_id']);
            $table->dropForeign(['ano_lectivo_id']);

            // Eliminar constraint único anterior
            $table->dropUnique('asignaciones_docente_unique');

            // Restaurar las FKs
            $table->foreign('docente_id')->references('id')->on('docentes')->cascadeOnDelete();
            $table->foreign('grado_seccion_id')->references('id')->on('grado_secciones')->cascadeOnDelete();
            $table->foreign('ano_lectivo_id')->references('id')->on('anos_lectivos')->cascadeOnDelete();

            // Agregar curso_id nullable (NULL = polidocente cubre todo)
            $table->foreignId('curso_id')
                  ->nullable()
                  ->after('grado_seccion_id')
                  ->constrained('cursos')
                  ->nullOnDelete();

            // Nuevo constraint único: docente + sección + curso + año
            $table->unique(
                ['docente_id', 'grado_seccion_id', 'curso_id', 'ano_lectivo_id'],
                'asignaciones_docente_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('asignaciones_docente', function (Blueprint $table) {
            $table->dropForeign(['docente_id']);
            $table->dropForeign(['grado_seccion_id']);
            $table->dropForeign(['ano_lectivo_id']);
            $table->dropForeign(['curso_id']);
            $table->dropUnique('asignaciones_docente_unique');
            $table->dropColumn('curso_id');

            $table->foreign('docente_id')->references('id')->on('docentes')->cascadeOnDelete();
            $table->foreign('grado_seccion_id')->references('id')->on('grado_secciones')->cascadeOnDelete();
            $table->foreign('ano_lectivo_id')->references('id')->on('anos_lectivos')->cascadeOnDelete();

            $table->unique(
                ['docente_id', 'grado_seccion_id', 'ano_lectivo_id'],
                'asignaciones_docente_unique'
            );
        });
    }
};
