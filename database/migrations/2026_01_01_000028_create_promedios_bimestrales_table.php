<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promedios_bimestrales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matriculas')->cascadeOnDelete();
            $table->foreignId('competencia_id')->constrained('competencias')->cascadeOnDelete();
            $table->foreignId('bimestre_id')->constrained('bimestres')->cascadeOnDelete();
            $table->decimal('promedio_numero', 5, 2)->nullable();
            $table->enum('promedio_letra', ['AD', 'A', 'B', 'C'])->nullable();
            $table->timestamp('calculado_en')->nullable();
            $table->timestamps();

            $table->unique(['matricula_id', 'competencia_id', 'bimestre_id'], 'promedios_bimestrales_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promedios_bimestrales');
    }
};
