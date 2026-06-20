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
            $table->string('colegio_inicial')->nullable()->after('nivel');
            
            // Datos del Padre
            $table->string('padre_dni', 8)->nullable()->after('colegio_inicial');
            $table->string('padre_nombres')->nullable()->after('padre_dni');
            $table->string('padre_telefono', 20)->nullable()->after('padre_nombres');
            
            // Datos de la Madre
            $table->string('madre_dni', 8)->nullable()->after('padre_telefono');
            $table->string('madre_nombres')->nullable()->after('madre_dni');
            $table->string('madre_telefono', 20)->nullable()->after('madre_nombres');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropColumn([
                'colegio_inicial',
                'padre_dni',
                'padre_nombres',
                'padre_telefono',
                'madre_dni',
                'madre_nombres',
                'madre_telefono'
            ]);
        });
    }
};
