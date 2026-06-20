<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('importaciones_siagie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('grado_seccion_id')->constrained('grado_secciones')->cascadeOnDelete();
            $table->foreignId('ano_lectivo_id')->constrained('anos_lectivos')->cascadeOnDelete();
            $table->string('nombre_archivo');
            $table->string('tipo')->default('excel');
            $table->integer('estudiantes_importados')->default(0);
            $table->json('errores')->nullable();
            $table->enum('estado', ['exitoso', 'con_errores', 'fallido'])->default('exitoso');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('importaciones_siagie');
    }
};
