<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('calificaciones');

        Schema::create('notas_bimestrales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->foreignId('competencia_id')->nullable()->constrained('competencias')->cascadeOnDelete();
            $table->foreignId('bimestre_id')->constrained('bimestres')->cascadeOnDelete();
            $table->string('nota', 5)->nullable(); // AD, A, B, C, etc.
            $table->text('conclusion_descriptiva')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_bimestrales');
    }
};
