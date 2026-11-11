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
            $table->string('resultado_conformidad', 50)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entrevistas_admision', function (Blueprint $table) {
            $table->string('resultado_conformidad', 50)->default('CONFORME')->nullable(false)->change();
        });
    }
};
