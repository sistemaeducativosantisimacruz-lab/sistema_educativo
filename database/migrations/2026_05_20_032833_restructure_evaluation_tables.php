<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop old tables
        Schema::dropIfExists('promedios_bimestrales');
        Schema::dropIfExists('calificaciones');
        Schema::dropIfExists('listas_cotejo');
        Schema::dropIfExists('competencias');
        Schema::dropIfExists('criterios');
        Schema::dropIfExists('plantillas_lista_cotejo');
        Schema::dropIfExists('sesiones');

        // 2. Create new tables
        Schema::create('competencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->string('nombre');
            $table->unsignedSmallInteger('orden')->default(1);
            $table->timestamps();
        });

        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->foreignId('competencia_id')->nullable()->constrained('competencias')->cascadeOnDelete();
            $table->foreignId('bimestre_id')->constrained('bimestres')->cascadeOnDelete();
            $table->string('calificacion_letra', 10)->nullable(); // AD, A, B, C, etc.
            $table->text('observacion')->nullable();
            $table->timestamps();
            
            // Unique index for a grade per student, course, competency, and bimestre
            // Using a partial index or just standard unique might fail if competencia_id is null.
            // But usually SIAGIE imports have a grade per competency. Let's make it not nullable for now, 
            // or if we need a final grade per course, we leave it nullable and rely on application logic.
            // $table->unique(['estudiante_id', 'curso_id', 'competencia_id', 'bimestre_id'], 'calificacion_unica');
        });
    }

    public function down(): void
    {
        // It's hard to revert this since we drop many tables, but we can drop the new ones.
        Schema::dropIfExists('calificaciones');
        Schema::dropIfExists('competencias');
    }
};
