<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeccionSeeder extends Seeder
{
    public function run(): void
    {
        $secciones = ['A', 'B', 'C', 'D', 'E'];
        foreach ($secciones as $nombre) {
            DB::table('secciones')->insert([
                'nombre' => $nombre,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
