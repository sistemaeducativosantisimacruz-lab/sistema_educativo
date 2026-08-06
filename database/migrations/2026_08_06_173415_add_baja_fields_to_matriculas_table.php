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
        Schema::table('matriculas', function (Blueprint $table) {
            $table->date('fecha_baja')->nullable()->after('tipo_matricula');
            $table->string('motivo_baja')->nullable()->after('fecha_baja');
            $table->text('observaciones_baja')->nullable()->after('motivo_baja');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropColumn(['fecha_baja', 'motivo_baja', 'observaciones_baja']);
        });
    }
};
