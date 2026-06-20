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
        Schema::table('apoderados', function (Blueprint $table) {
            $table->dropForeign('apoderados_estudiante_dni_foreign');
            
            $table->foreign('estudiante_dni')
                  ->references('dni')
                  ->on('estudiantes')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apoderados', function (Blueprint $table) {
            $table->dropForeign('apoderados_estudiante_dni_foreign');
            
            $table->foreign('estudiante_dni')
                  ->references('dni')
                  ->on('estudiantes')
                  ->onDelete('cascade');
        });
    }
};
