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
        Schema::table('bimestres', function (Blueprint $table) {
            $table->boolean('notas_publicadas_primaria')->default(false)->after('estado');
            $table->boolean('notas_publicadas_secundaria')->default(false)->after('notas_publicadas_primaria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bimestres', function (Blueprint $table) {
            $table->dropColumn(['notas_publicadas_primaria', 'notas_publicadas_secundaria']);
        });
    }
};
