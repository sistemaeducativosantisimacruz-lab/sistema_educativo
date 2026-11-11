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
        Schema::table('entrevistas_admision', function (Blueprint $table) {
            $table->char('estudiante_sexo', 1)->nullable()->after('estudiante_fecha_nacimiento'); // 'M', 'F'
            $table->string('estado_entrevista', 30)->default('completada')->after('estado'); // 'en_progreso', 'completada'
            $table->json('hermanos_datos')->nullable()->after('respuestas_cuestionario'); // Detalle de hermanos en la IE
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entrevistas_admision', function (Blueprint $table) {
            $table->dropColumn(['estudiante_sexo', 'estado_entrevista', 'hermanos_datos']);
        });
    }
};
