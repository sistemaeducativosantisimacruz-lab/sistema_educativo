<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Agregar rol 'directivo' si no existe
        $roleExiste = DB::table('roles')->where('nombre', 'directivo')->exists();
        if (!$roleExiste) {
            DB::table('roles')->insert([
                'nombre' => 'directivo',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Agregar columna 'activo' a directivos si no existe
        if (Schema::hasTable('directivos') && !Schema::hasColumn('directivos', 'activo')) {
            Schema::table('directivos', function (Blueprint $table) {
                $table->boolean('activo')->default(true)->after('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('directivos') && Schema::hasColumn('directivos', 'activo')) {
            Schema::table('directivos', function (Blueprint $table) {
                $table->dropColumn('activo');
            });
        }

        DB::table('roles')->where('nombre', 'directivo')->delete();
    }
};
