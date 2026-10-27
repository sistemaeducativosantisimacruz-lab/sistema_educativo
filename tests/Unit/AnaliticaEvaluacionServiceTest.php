<?php

namespace Tests\Unit;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Services\AnaliticaEvaluacionService;

class AnaliticaEvaluacionServiceTest extends TestCase
{
    private AnaliticaEvaluacionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AnaliticaEvaluacionService();
    }

    #[Test]
    public function calcular_distribucion_escalas_returns_correct_structure_and_values(): void
    {
        $notas = ['AD', 'AD', 'A', 'B', 'C'];

        $resultado = $this->service->calcularDistribucionEscalas($notas);

        $esperado = [
            'AD' => ['cantidad' => 2, 'porcentaje' => 40.0],
            'A'  => ['cantidad' => 1, 'porcentaje' => 20.0],
            'B'  => ['cantidad' => 1, 'porcentaje' => 20.0],
            'C'  => ['cantidad' => 1, 'porcentaje' => 20.0]
        ];

        $this->assertEquals($esperado, $resultado);
    }

    #[Test]
    public function calcular_distribucion_con_arreglo_vacio_retorna_vacio(): void
    {
        $resultado = $this->service->calcularDistribucionEscalas([]);
        $this->assertEmpty($resultado);
    }

    #[Test]
    public function procesar_estadisticas_calcula_frecuencias_y_porcentajes_por_competencia(): void
    {
        $rows = [
            (object) [
                'curso_id' => 1,
                'area' => 'Comunicación',
                'competencia_id' => 101,
                'competencia' => 'Lee diversos tipos de textos',
                'nivel_logro' => 'AD',
                'cantidad' => 4
            ],
            (object) [
                'curso_id' => 1,
                'area' => 'Comunicación',
                'competencia_id' => 101,
                'competencia' => 'Lee diversos tipos de textos',
                'nivel_logro' => 'A',
                'cantidad' => 6
            ],
        ];

        $areas = $this->service->procesarEstadisticas($rows);

        $this->assertArrayHasKey(1, $areas);
        $comp = $areas[1]['competencias']['Lee diversos tipos de textos'];

        // 4 AD de 10 = 40.0%, 6 A de 10 = 60.0%
        $this->assertEquals(4, $comp['AD']);
        $this->assertEquals(6, $comp['A']);
        $this->assertEquals(0, $comp['B']);
        $this->assertEquals(0, $comp['C']);
        $this->assertEquals(10, $comp['total']);

        $this->assertEquals(40.0, $comp['porcentajes']['AD']);
        $this->assertEquals(60.0, $comp['porcentajes']['A']);
        $this->assertEquals(0.0, $comp['porcentajes']['B']);
        $this->assertEquals(0.0, $comp['porcentajes']['C']);
    }

    #[Test]
    public function procesar_estadisticas_con_lista_vacia_retorna_arreglo_vacio(): void
    {
        $resultado = $this->service->procesarEstadisticas([]);
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    #[Test]
    public function calcular_resumen_global_acumula_conteos_y_calcula_porcentajes_globales(): void
    {
        $areas = [
            1 => [
                'nombre' => 'Matemática',
                'competencias' => [
                    'Comp 1' => [
                        'AD' => 2, 'A' => 3, 'B' => 3, 'C' => 2, 'total' => 10,
                        'porcentajes' => ['AD' => 20.0, 'A' => 30.0, 'B' => 30.0, 'C' => 20.0]
                    ],
                    'Comp 2' => [
                        'AD' => 2, 'A' => 3, 'B' => 3, 'C' => 2, 'total' => 10,
                        'porcentajes' => ['AD' => 20.0, 'A' => 30.0, 'B' => 30.0, 'C' => 20.0]
                    ],
                ]
            ]
        ];

        $resumen = $this->service->calcularResumenGlobal($areas);

        // Total 20 evaluaciones: 4 AD, 6 A, 6 B, 4 C
        $this->assertEquals(20, $resumen['total']);
        $this->assertEquals(4, $resumen['AD']['cantidad']);
        $this->assertEquals(20.0, $resumen['AD']['porcentaje']);

        $this->assertEquals(6, $resumen['A']['cantidad']);
        $this->assertEquals(30.0, $resumen['A']['porcentaje']);

        $this->assertEquals(6, $resumen['B']['cantidad']);
        $this->assertEquals(30.0, $resumen['B']['porcentaje']);

        $this->assertEquals(4, $resumen['C']['cantidad']);
        $this->assertEquals(20.0, $resumen['C']['porcentaje']);
    }

    #[Test]
    public function calcular_resumen_global_vacio_retorna_ceros(): void
    {
        $resumen = $this->service->calcularResumenGlobal([]);

        $this->assertEquals(0, $resumen['total']);
        $this->assertEquals(0, $resumen['AD']['cantidad']);
        $this->assertEquals(0.0, $resumen['AD']['porcentaje']);
        $this->assertEquals(0, $resumen['A']['cantidad']);
        $this->assertEquals(0.0, $resumen['A']['porcentaje']);
        $this->assertEquals(0, $resumen['B']['cantidad']);
        $this->assertEquals(0.0, $resumen['B']['porcentaje']);
        $this->assertEquals(0, $resumen['C']['cantidad']);
        $this->assertEquals(0.0, $resumen['C']['porcentaje']);
    }

    #[Test]
    public function supera_umbral_alerta_detecta_cuando_c_mas_b_supera_el_40_por_ciento(): void
    {
        config(['analitica.umbral_alerta_riesgo' => 40.0]);

        // C (15%) + B (30%) = 45% > 40% -> Debe alertar
        $this->assertTrue($this->service->superaUmbralAlerta(['C' => 15.0, 'B' => 30.0]));

        // C (10%) + B (20%) = 30% <= 40% -> No debe alertar
        $this->assertFalse($this->service->superaUmbralAlerta(['C' => 10.0, 'B' => 20.0]));

        // C (10%) + B (30%) = 40% (justo en el umbral) -> No supera
        $this->assertFalse($this->service->superaUmbralAlerta(['C' => 10.0, 'B' => 30.0]));
    }
}
