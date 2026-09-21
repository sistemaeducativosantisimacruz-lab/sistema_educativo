<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\AnaliticaEvaluacionService;

class AnaliticaEvaluacionServiceTest extends TestCase
{
    /**
     * @test
     */
    public function calcular_distribucion_escalas_returns_correct_structure_and_values()
    {
        $service = new AnaliticaEvaluacionService();

        $notas = ['AD', 'AD', 'A', 'B', 'C'];

        $resultado = $service->calcularDistribucionEscalas($notas);

        $esperado = [
            'AD' => ['cantidad' => 2, 'porcentaje' => 40.0],
            'A'  => ['cantidad' => 1, 'porcentaje' => 20.0],
            'B'  => ['cantidad' => 1, 'porcentaje' => 20.0],
            'C'  => ['cantidad' => 1, 'porcentaje' => 20.0]
        ];

        $this->assertEquals($esperado, $resultado);
    }
}
