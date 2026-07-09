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
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE docentes ALTER COLUMN nivel DROP NOT NULL');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE docentes ALTER COLUMN tipo DROP NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE docentes ALTER COLUMN nivel SET DEFAULT 'secundaria'");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE docentes ALTER COLUMN tipo SET DEFAULT 'especialista'");
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE docentes ALTER COLUMN nivel SET NOT NULL');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE docentes ALTER COLUMN tipo SET NOT NULL');
    }
};
