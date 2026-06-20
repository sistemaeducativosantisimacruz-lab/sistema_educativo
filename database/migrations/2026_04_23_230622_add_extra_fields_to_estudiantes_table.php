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
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->string('direccion')->nullable()->after('sexo');
            $table->string('telefono', 15)->nullable()->after('direccion');
            $table->string('apoderado_nombre')->nullable()->after('telefono');
            $table->string('apoderado_dni', 8)->nullable()->after('apoderado_nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropColumn(['direccion', 'telefono', 'apoderado_nombre', 'apoderado_dni']);
        });
    }
};
