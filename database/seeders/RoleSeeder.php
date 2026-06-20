<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            ['nombre' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'docente', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'estudiante', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
