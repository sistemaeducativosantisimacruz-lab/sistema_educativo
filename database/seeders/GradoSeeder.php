<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('grados')->insert([
            // Primaria
            ['nombre' => '1ro Primaria', 'nivel' => 'primaria', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => '2do Primaria', 'nivel' => 'primaria', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => '3ro Primaria', 'nivel' => 'primaria', 'orden' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => '4to Primaria', 'nivel' => 'primaria', 'orden' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => '5to Primaria', 'nivel' => 'primaria', 'orden' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => '6to Primaria', 'nivel' => 'primaria', 'orden' => 6, 'created_at' => now(), 'updated_at' => now()],
            
            // Secundaria
            ['nombre' => '1ro Secundaria', 'nivel' => 'secundaria', 'orden' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => '2do Secundaria', 'nivel' => 'secundaria', 'orden' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => '3ro Secundaria', 'nivel' => 'secundaria', 'orden' => 9, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => '4to Secundaria', 'nivel' => 'secundaria', 'orden' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => '5to Secundaria', 'nivel' => 'secundaria', 'orden' => 11, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
