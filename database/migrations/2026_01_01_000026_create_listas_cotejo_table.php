<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listas_cotejo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_id')->constrained('sesiones')->cascadeOnDelete();
            $table->foreignId('asignacion_docente_id')->constrained('asignaciones_docente')->cascadeOnDelete();
            $table->foreignId('plantilla_id')->constrained('plantillas_lista_cotejo')->cascadeOnDelete();
            $table->enum('estado', ['borrador', 'publicada'])->default('borrador');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['sesion_id', 'asignacion_docente_id'], 'listas_cotejo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listas_cotejo');
    }
};
