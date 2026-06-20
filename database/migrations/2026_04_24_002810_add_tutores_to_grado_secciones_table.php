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
        Schema::table('grado_secciones', function (Blueprint $table) {
            $table->foreignId('tutor_id')->nullable()->constrained('docentes')->nullOnDelete();
            $table->foreignId('cotutor_id')->nullable()->constrained('docentes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grado_secciones', function (Blueprint $table) {
            $table->dropForeign(['tutor_id']);
            $table->dropForeign(['cotutor_id']);
            $table->dropColumn(['tutor_id', 'cotutor_id']);
        });
    }
};
