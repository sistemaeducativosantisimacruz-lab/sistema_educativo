<?php

namespace Tests\Unit;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Services\DiagnosticoService;

class DiagnosticoServiceTest extends TestCase
{
    private DiagnosticoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DiagnosticoService();
    }

    #[Test]
    public function conversion_de_letras_y_escalas_es_consistente(): void
    {
        $this->assertEquals(20, $this->service->convertirLetraANumero('AD'));
        $this->assertEquals(17, $this->service->convertirLetraANumero('A'));
        $this->assertEquals(13, $this->service->convertirLetraANumero('B'));
        $this->assertEquals(10, $this->service->convertirLetraANumero('C'));
        $this->assertNull($this->service->convertirLetraANumero(null));

        $this->assertEquals('AD', $this->service->convertirNumeroAEscala(18.5));
        $this->assertEquals('AD', $this->service->convertirNumeroAEscala(17.5));
        $this->assertEquals('A', $this->service->convertirNumeroAEscala(17.4));
        $this->assertEquals('A', $this->service->convertirNumeroAEscala(13.5));
        $this->assertEquals('B', $this->service->convertirNumeroAEscala(13.4));
        $this->assertEquals('B', $this->service->convertirNumeroAEscala(10.5));
        $this->assertEquals('C', $this->service->convertirNumeroAEscala(10.4));
        $this->assertNull($this->service->convertirNumeroAEscala(null));
    }

    #[Test]
    public function perfil_alto_sobresaliente_todas_en_ad(): void
    {
        $items = [
            ['nombre' => 'Competencia 1', 'nivel' => 'AD'],
            ['nombre' => 'Competencia 2', 'nivel' => 'AD'],
        ];

        $texto = $this->service->generarDiagnosticoEstudiante($items, false);

        $this->assertStringContainsString('desempeño sobresaliente', $texto);
        $this->assertStringContainsString('Logro Destacado (AD)', $texto);
    }

    #[Test]
    public function perfil_alto_mayoria_en_a_o_ad(): void
    {
        $items = [
            ['nombre' => 'Competencia 1', 'nivel' => 'A'],
            ['nombre' => 'Competencia 2', 'nivel' => 'AD'],
            ['nombre' => 'Competencia 3', 'nivel' => 'B'],
        ];

        $texto = $this->service->generarDiagnosticoEstudiante($items, false);

        $this->assertStringContainsString('consolida aprendizajes satisfactorios', $texto);
        $this->assertStringContainsString('Se recomienda fortalecer las competencias en proceso: Competencia 3.', $texto);
    }

    #[Test]
    public function perfil_medio_bajo_con_competencias_en_inicio_y_proceso(): void
    {
        $items = [
            ['nombre' => 'Álgebra', 'nivel' => 'B'],
            ['nombre' => 'Geometría', 'nivel' => 'C'],
            ['nombre' => 'Estadística', 'nivel' => 'C'],
        ];

        $texto = $this->service->generarDiagnosticoEstudiante($items, false);

        $this->assertStringContainsString('etapa de consolidación', $texto);
        $this->assertStringContainsString('fortalecer las competencias en proceso: Álgebra.', $texto);
        $this->assertStringContainsString('requiere acompañamiento pedagógico prioritario y coordinación estrecha con el apoderado en: Geometría, Estadística.', $texto);
    }

    #[Test]
    public function periodo_inicial_bimestre_1_omite_frase_de_evolucion(): void
    {
        $items = [
            ['nombre' => 'Lectura', 'nivel' => 'A'],
            ['nombre' => 'Escritura', 'nivel' => 'A'],
        ];

        // tieneBimestreAnterior = false
        $texto = $this->service->generarDiagnosticoEstudiante($items, false, 17.0, null);

        $this->assertStringNotContainsString('Respecto al periodo anterior', $texto);
        $this->assertStringNotContainsString('Mantiene un rendimiento académico estable', $texto);
    }

    #[Test]
    public function evolucion_favorable_calcula_delta_adecuado(): void
    {
        $items = [
            ['nombre' => 'Lectura', 'nivel' => 'A'],
        ];

        // Promedio anterior = 13.0, actual = 15.0 (delta = +2.0 >= umbral 1.0)
        $texto = $this->service->generarDiagnosticoEstudiante($items, true, 15.0, 13.0);

        $this->assertStringContainsString('Respecto al periodo anterior, muestra una evolución favorable con un incremento en su promedio general (+2 puntos).', $texto);
    }

    #[Test]
    public function evolucion_descendente_alerta_reforzamiento(): void
    {
        $items = [
            ['nombre' => 'Lectura', 'nivel' => 'B'],
        ];

        // Promedio anterior = 16.0, actual = 14.0 (delta = -2.0 <= umbral -1.0)
        $texto = $this->service->generarDiagnosticoEstudiante($items, true, 14.0, 16.0);

        $this->assertStringContainsString('Respecto al periodo anterior, se observa un descenso en su promedio general (-2 puntos)', $texto);
    }

    #[Test]
    public function evolucion_estable_dentro_del_rango(): void
    {
        $items = [
            ['nombre' => 'Lectura', 'nivel' => 'A'],
        ];

        // Delta = 0.5 (entre -1.0 y 1.0)
        $texto = $this->service->generarDiagnosticoEstudiante($items, true, 14.5, 14.0);

        $this->assertStringContainsString('Mantiene un rendimiento académico estable respecto al periodo anterior.', $texto);
    }

    #[Test]
    public function estudiante_sin_notas_retorna_mensaje_predeterminado(): void
    {
        $items = [];
        $texto = $this->service->generarDiagnosticoEstudiante($items, false);

        $this->assertEquals('Sin calificaciones registradas para el periodo seleccionado.', $texto);

        $itemsNulos = [
            ['nombre' => 'Lectura', 'nivel' => null],
            ['nombre' => 'Escritura', 'nivel' => ''],
        ];
        $textoNulos = $this->service->generarDiagnosticoEstudiante($itemsNulos, false);
        $this->assertEquals('Sin calificaciones registradas para el periodo seleccionado.', $textoNulos);
    }
}
