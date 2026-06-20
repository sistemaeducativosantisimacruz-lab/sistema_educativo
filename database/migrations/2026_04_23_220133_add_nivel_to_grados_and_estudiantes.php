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
        Schema::table('grados', function (Blueprint $table) {
            $table->enum('nivel', ['primaria', 'secundaria'])->default('secundaria')->after('nombre');
        });

        Schema::table('estudiantes', function (Blueprint $table) {
            $table->enum('nivel', ['primaria', 'secundaria'])->default('secundaria')->after('sexo');
        });
    }

    public function down(): void
    {
        Schema::table('grados', function (Blueprint $table) {
            $table->dropColumn('nivel');
        });

        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropColumn('nivel');
        });
    }
};
