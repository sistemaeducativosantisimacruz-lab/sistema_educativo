<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            GradoSeeder::class,
            CursoSeeder::class,
            AnoLectivoSeeder::class,
            SiagieSecundariaSeeder::class,
        ]);
    }
}
