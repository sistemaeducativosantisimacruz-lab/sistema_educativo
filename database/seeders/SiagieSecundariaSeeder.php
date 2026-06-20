<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Curso;
use App\Models\Competencia;

class SiagieSecundariaSeeder extends Seeder
{
    public function run()
    {
        $cursosSiagie = [
            [
                'codigo' => '0001',
                'nombre' => 'ARTE Y CULTURA',
                'competencias' => [
                    'Aprecia de manera crítica manifestaciones artístico-culturales.',
                    'Crea proyectos desde los lenguajes artísticos.'
                ]
            ],
            [
                'codigo' => '0002',
                'nombre' => 'CASTELLANO COMO SEGUNDA LENGUA',
                'competencias' => [
                    'Se comunica oralmente en castellano como segunda lengua.',
                    'Lee diversos tipos de textos escritos en castellano como segunda lengua.',
                    'Escribe diversos tipos de textos en castellano como segunda lengua.'
                ]
            ],
            [
                'codigo' => '0004',
                'nombre' => 'CIENCIA Y TECNOLOGÍA',
                'competencias' => [
                    'Indaga mediante métodos científicos para construir conocimientos.',
                    'Explica el mundo físico basándose en conocimientos sobre los seres vivos, materia y energía, biodiversidad, Tierra y universo.',
                    'Diseña y construye soluciones tecnológicas para resolver problemas de su entorno.'
                ]
            ],
            [
                'codigo' => '0010',
                'nombre' => 'DESARROLLO PERSONAL, CIUDADANÍA Y CÍVICA',
                'competencias' => [
                    'Construye su identidad.',
                    'Convive y participa democráticamente en la búsqueda del bien común.'
                ]
            ],
            [
                'codigo' => '014',
                'nombre' => 'CIENCIAS SOCIALES',
                'competencias' => [
                    'Construye interpretaciones históricas.',
                    'Gestiona responsablemente el espacio y el ambiente.',
                    'Gestiona responsablemente los recursos económicos.'
                ]
            ],
            [
                'codigo' => '017',
                'nombre' => 'COMUNICACIÓN',
                'competencias' => [
                    'Se comunica oralmente en su lengua materna.',
                    'Lee diversos tipos de textos escritos en su lengua materna.',
                    'Escribe diversos tipos de textos en su lengua materna.'
                ]
            ],
            [
                'codigo' => '031',
                'nombre' => 'EDUCACIÓN FÍSICA',
                'competencias' => [
                    'Se desenvuelve de manera autónoma a través de su motricidad.',
                    'Asume una vida saludable.',
                    'Interactúa a través de sus habilidades sociomotrices.'
                ]
            ],
            [
                'codigo' => '032',
                'nombre' => 'EDUCACIÓN PARA EL TRABAJO',
                'competencias' => [
                    'Gestiona proyectos de emprendimiento económico o social.'
                ]
            ],
            [
                'codigo' => '035',
                'nombre' => 'EDUCACIÓN RELIGIOSA',
                'competencias' => [
                    'Construye su identidad como persona humana, amada por Dios, digna, libre y trascendente, comprendiendo la doctrina de su propia religión, abierto al diálogo con las que le son cercanas.',
                    'Asume la experiencia del encuentro personal y comunitario con Dios en su proyecto de vida en coherencia con su creencia religiosa.'
                ]
            ],
            [
                'codigo' => '057',
                'nombre' => 'INGLÉS',
                'competencias' => [
                    'Se comunica oralmente en inglés como idioma extranjero.',
                    'Lee diversos tipos de textos escritos en inglés como idioma extranjero.',
                    'Escribe diversos tipos de textos en inglés como idioma extranjero.'
                ]
            ],
            [
                'codigo' => '063',
                'nombre' => 'MATEMÁTICA',
                'competencias' => [
                    'Resuelve problemas de cantidad.',
                    'Resuelve problemas de regularidad, equivalencia y cambio.',
                    'Resuelve problemas de forma, movimiento y localización.',
                    'Resuelve problemas de gestión de datos e incertidumbre.'
                ]
            ],
            [
                'codigo' => '0006',
                'nombre' => 'DESENVOLVIMIENTO EN TIC',
                'competencias' => [
                    'Se desenvuelve en entornos virtuales generados por las TIC.'
                ]
            ],
            [
                'codigo' => '0007',
                'nombre' => 'GESTIONA SU APRENDIZAJE',
                'competencias' => [
                    'Gestiona su aprendizaje de manera autónoma.'
                ]
            ],
        ];

        foreach ($cursosSiagie as $cursoData) {
            $nivel = in_array($cursoData['nombre'], [
                'DESARROLLO PERSONAL, CIUDADANÍA Y CÍVICA', 
                'CIENCIAS SOCIALES', 
                'EDUCACIÓN PARA EL TRABAJO'
            ]) ? 'secundaria' : 'ambos';

            $curso = Curso::updateOrCreate(
                ['codigo' => $cursoData['codigo']],
                [
                    'nombre' => $cursoData['nombre'],
                    'nivel' => $nivel
                ]
            );

            // Crear competencias
            foreach ($cursoData['competencias'] as $index => $compName) {
                Competencia::updateOrCreate(
                    [
                        'curso_id' => $curso->id,
                        'orden' => $index + 1
                    ],
                    [
                        'nombre' => $compName
                    ]
                );
            }
        }
    }
}
