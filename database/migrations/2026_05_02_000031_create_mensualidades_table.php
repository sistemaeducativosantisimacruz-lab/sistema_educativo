<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensualidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matriculas')->onDelete('cascade');
            $table->tinyInteger('mes');      // 1-12
            $table->year('anio');
            $table->enum('estado', ['DEBE', 'PAGÓ', 'EXONERADO', 'BENEFICIADO'])->default('DEBE');
            $table->text('observacion')->nullable();
            $table->timestamps();

            // Un estudiante (matrícula) solo puede tener un registro por mes/año
            $table->unique(['matricula_id', 'mes', 'anio']);
            $table->index(['mes', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensualidades');
    }
};
