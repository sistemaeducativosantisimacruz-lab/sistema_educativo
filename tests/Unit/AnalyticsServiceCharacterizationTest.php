<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AnalyticsService;
use ReflectionClass;

class AnalyticsServiceCharacterizationTest extends TestCase
{
    /**
     * Prueba de caracterización: Captura y verifica la estructura exacta de claves y valores
     * que produce el procesamiento de estadísticas de AnalyticsService.
     */
    public function test_procesar_estadisticas_produce_estructura_y_valores_esperados(): void
    {
        $service = new AnalyticsService();

        // Datos simulados idénticos a los que devuelve la consulta SQL agrupada
        $rows = [
            (object) [
                'curso_id' => 1,
                'area' => 'Matemática',
                'competencia_id' => 10,
                'competencia' => 'Resuelve problemas de cantidad',
                'nivel_logro' => 'AD',
                'cantidad' => 5
            ],
            (object) [
                'curso_id' => 1,
                'area' => 'Matemática',
                'competencia_id' => 10,
                'competencia' => 'Resuelve problemas de cantidad',
                'nivel_logro' => 'A',
                'cantidad' => 10
            ],
            (object) [
                'curso_id' => 1,
                'area' => 'Matemática',
                'competencia_id' => 10,
                'competencia' => 'Resuelve problemas de cantidad',
                'nivel_logro' => 'B',
                'cantidad' => 3
            ],
            (object) [
                'curso_id' => 1,
                'area' => 'Matemática',
                'competencia_id' => 10,
                'competencia' => 'Resuelve problemas de cantidad',
                'nivel_logro' => 'C',
                'cantidad' => 2
            ],
        ];

        // Invocamos el método mediante reflection para caracterizar la salida interna
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('procesarEstadisticas');
        $method->setAccessible(true);
        $resultado = $method->invoke($service, $rows);

        // Verificamos estructura de claves esperada por la vista
        $this->assertArrayHasKey(1, $resultado);
        $this->assertEquals('Matemática', $resultado[1]['nombre']);
        $this->assertArrayHasKey('competencias', $resultado[1]);
        $this->assertArrayHasKey('Resuelve problemas de cantidad', $resultado[1]['competencias']);

        $comp = $resultado[1]['competencias']['Resuelve problemas de cantidad'];

        // Conteos exactos
        $this->assertEquals(5, $comp['AD']);
        $this->assertEquals(10, $comp['A']);
        $this->assertEquals(3, $comp['B']);
        $this->assertEquals(2, $comp['C']);
        $this->assertEquals(20, $comp['total']);

        // Porcentajes exactos
        $this->assertEquals(25.0, $comp['porcentajes']['AD']);
        $this->assertEquals(50.0, $comp['porcentajes']['A']);
        $this->assertEquals(15.0, $comp['porcentajes']['B']);
        $this->assertEquals(10.0, $comp['porcentajes']['C']);

        // Interpretación generada (75% logro A+AD -> Favorable)
        $this->assertStringContainsString('Favorable', $comp['interpretacion']);
    }
}
