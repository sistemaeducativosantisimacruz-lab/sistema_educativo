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
        Schema::create('entrevistas_admision', function (Blueprint $table) {
            $table->id();
            $table->integer('ano_lectivo_postula')->default(2027);
            $table->string('nivel', 20)->default('primaria'); // 'primaria', 'secundaria'
            $table->string('grado_postula', 50); // ej. '1° Primaria', '1° Secundaria'
            
            // Datos del Estudiante Postulante
            $table->string('estudiante_dni', 15)->index();
            $table->string('estudiante_nombres', 100);
            $table->string('estudiante_apellido_paterno', 100)->nullable();
            $table->string('estudiante_apellido_materno', 100)->nullable();
            $table->date('estudiante_fecha_nacimiento')->nullable();
            $table->string('colegio_procedencia', 150)->nullable();
            
            // Datos del Padre
            $table->string('padre_dni', 15)->nullable();
            $table->string('padre_nombres', 150)->nullable();
            $table->string('padre_telefono', 25)->nullable();
            $table->string('padre_ocupacion', 100)->nullable();
            $table->string('padre_grado_estudios', 100)->nullable();
            $table->text('padre_domicilio')->nullable();
            
            // Datos de la Madre
            $table->string('madre_dni', 15)->nullable();
            $table->string('madre_nombres', 150)->nullable();
            $table->string('madre_telefono', 25)->nullable();
            $table->string('madre_ocupacion', 100)->nullable();
            $table->string('madre_grado_estudios', 100)->nullable();
            $table->text('madre_domicilio')->nullable();
            
            // Datos del Apoderado (si aplica)
            $table->string('apoderado_dni', 15)->nullable();
            $table->string('apoderado_nombres', 150)->nullable();
            $table->string('apoderado_telefono', 25)->nullable();
            $table->string('apoderado_ocupacion', 100)->nullable();
            $table->string('apoderado_grado_estudios', 100)->nullable();
            $table->text('apoderado_domicilio')->nullable();
            $table->string('apoderado_parentesco', 50)->nullable();
            
            // Respuestas completas de la entrevista (fe, salud, axiología, normas, compromisos, autorizaciones)
            $table->json('respuestas_cuestionario')->nullable();
            
            // Control del Proceso de Admisión
            $table->foreignId('docente_entrevistador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_entrevista')->nullable();
            $table->string('resultado_conformidad', 50)->default('CONFORME'); // 'CONFORME', 'NO CONFORME'
            $table->string('estado', 30)->default('nuevo_ingresante'); // 'nuevo_ingresante', 'matriculado', 'rechazado'
            $table->text('observaciones')->nullable();
            
            // Vinculación cuando se formalice la matrícula asignándole sección
            $table->foreignId('estudiante_id')->nullable()->constrained('estudiantes')->nullOnDelete();
            $table->foreignId('matricula_id')->nullable()->constrained('matriculas')->nullOnDelete();
            $table->foreignId('grado_seccion_asignada_id')->nullable()->constrained('grado_secciones')->nullOnDelete();
            
            $table->string('archivo_excel_origen', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entrevistas_admision');
    }
};

