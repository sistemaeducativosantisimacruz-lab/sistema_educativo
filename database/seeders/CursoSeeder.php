<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CursoSeeder extends Seeder
{
    /**
     * Cursos del currículo nacional peruano para escuelas públicas.
     * Clasificados por nivel educativo.
     */
    public function run(): void
    {
        // Deshabilitar verificación de claves foráneas para poder limpiar la tabla
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('cursos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('cursos')->insert([
            // ─── CURSOS EXCLUSIVOS DE PRIMARIA ───────────────────────────────
            ['nombre' => 'Personal Social',            'codigo' => 'PS',   'activo' => true, 'nivel' => 'primaria',   'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Ciencia y Ambiente',         'codigo' => 'CA',   'activo' => true, 'nivel' => 'primaria',   'created_at' => now(), 'updated_at' => now()],

            // ─── CURSOS EXCLUSIVOS DE SECUNDARIA ─────────────────────────────
            ['nombre' => 'Ciencias Sociales',          'codigo' => 'CS',   'activo' => true, 'nivel' => 'secundaria', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Historia, Geografía y Economía', 'codigo' => 'HGE', 'activo' => true, 'nivel' => 'secundaria', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Ciencia, Tecnología y Ambiente', 'codigo' => 'CTA', 'activo' => true, 'nivel' => 'secundaria', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Educación para el Trabajo',  'codigo' => 'EPT',  'activo' => true, 'nivel' => 'secundaria', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Formación Ciudadana y Cívica', 'codigo' => 'FCC', 'activo' => true, 'nivel' => 'secundaria', 'created_at' => now(), 'updated_at' => now()],

            // ─── CURSOS COMUNES (primaria y secundaria) ───────────────────────
            ['nombre' => 'Comunicación',                'codigo' => 'COM',  'activo' => true, 'nivel' => 'ambos', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Matemática',                  'codigo' => 'MAT',  'activo' => true, 'nivel' => 'ambos', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Inglés',                      'codigo' => 'ING',  'activo' => true, 'nivel' => 'ambos', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Educación Física',            'codigo' => 'EF',   'activo' => true, 'nivel' => 'ambos', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Educación Artística',         'codigo' => 'ART',  'activo' => true, 'nivel' => 'ambos', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Educación Religiosa',         'codigo' => 'ER',   'activo' => true, 'nivel' => 'ambos', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Tutoría y Orientación Educativa', 'codigo' => 'TOE', 'activo' => true, 'nivel' => 'ambos', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
