<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnoLectivoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('anos_lectivos')->insert([
            'anio' => 2026,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
