<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lista_cotejo_id')->constrained('listas_cotejo')->cascadeOnDelete();
            $table->foreignId('competencia_id')->constrained('competencias')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->enum('calificacion_letra', ['AD', 'A', 'B', 'C'])->nullable();
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->unique(['lista_cotejo_id', 'competencia_id', 'estudiante_id'], 'calificaciones_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};
