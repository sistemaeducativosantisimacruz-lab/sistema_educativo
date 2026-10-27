<?php

namespace App\Services;

class DiagnosticoService
{
    /**
     * NOTA METODOLÓGICA SOBRE EL CÁLCULO DEL NIVEL GLOBAL:
     * PromedioService calcula los promedios al cierre formal del bimestre a nivel de competencia
     * (almacenándolos en la tabla 'promedios_bimestrales'). Mientras dicha tabla no sea poblada
     * formalmente, el cálculo en vivo convierte las calificaciones cualitativas a sus equivalentes
     * numéricos oficiales (AD=20, A=17, B=13, C=10), obtiene el promedio aritmético y aplica
     * los puntos de corte oficiales (AD >= 17.5, A >= 13.5, B >= 10.5, C < 10.5).
     * Dado que el promedio aritmético de calificaciones cualitativas convertidas a valores de
     * intervalo es una estimación continua y no un cálculo oficial cerrado en SIAGIE/CNEB,
     * este indicador tiene un carácter diagnóstico y pedagógico referencial.
     *
     * Convierte la calificación cualitativa a su valor numérico oficial en la I.E.P.
     * AD = 20, A = 17, B = 13, C = 10.
     */
    public function convertirLetraANumero(?string $letra): ?int
    {
        return match ($letra) {
            'AD' => 20,
            'A' => 17,
            'B' => 13,
            'C' => 10,
            default => null,
        };
    }

    /**
     * Convierte el promedio numérico a la escala literal de PromedioService.
     * AD >= 17.5, A >= 13.5, B >= 10.5, C < 10.5.
     */
    public function convertirNumeroAEscala(?float $promedio): ?string
    {
        if ($promedio === null) {
            return null;
        }

        if ($promedio >= 17.5) {
            return 'AD';
        }
        if ($promedio >= 13.5) {
            return 'A';
        }
        if ($promedio >= 10.5) {
            return 'B';
        }
        return 'C';
    }

    /**
     * Genera el diagnóstico cualitativo pedagógico y determinista para un estudiante
     * según los niveles alcanzados en el bimestre actual y su evolución respecto al anterior.
     *
     * @param array $itemsActuales Arreglo con elementos ['nombre' => ..., 'nivel' => 'AD'|'A'|'B'|'C']
     * @param bool $tieneBimestreAnterior
     * @param float|null $promedioActual
     * @param float|null $promedioAnterior
     * @return string
     */
    public function generarDiagnosticoEstudiante(
        array $itemsActuales,
        bool $tieneBimestreAnterior = false,
        ?float $promedioActual = null,
        ?float $promedioAnterior = null
    ): string {
        $validItems = array_filter($itemsActuales, fn($item) => !empty($item['nivel']));
        $total = count($validItems);

        if ($total === 0) {
            return "Sin calificaciones registradas para el periodo seleccionado.";
        }

        $conteo = ['AD' => 0, 'A' => 0, 'B' => 0, 'C' => 0];
        $nombresB = [];
        $nombresC = [];

        foreach ($validItems as $item) {
            $nivel = $item['nivel'];
            if (isset($conteo[$nivel])) {
                $conteo[$nivel]++;
            }
            if ($nivel === 'B') {
                $nombresB[] = $item['nombre'];
            } elseif ($nivel === 'C') {
                $nombresC[] = $item['nombre'];
            }
        }

        $logroCount = $conteo['AD'] + $conteo['A'];
        $porcentajeLogro = ($logroCount / $total) * 100;
        $umbralMayoria = (float) config('analitica.diagnostico_estudiante.umbral_mayoria', 50.0);

        $partes = [];

        // 1. Reconocimiento de nivel general
        if ($conteo['AD'] === $total) {
            $partes[] = "El estudiante demuestra un desempeño sobresaliente, alcanzando el nivel de Logro Destacado (AD) en todas las competencias evaluadas. Se sugiere proponer actividades de profundización y liderazgo académico.";
        } elseif ($porcentajeLogro >= $umbralMayoria) {
            $partes[] = "El estudiante consolida aprendizajes satisfactorios, alcanzando los niveles esperados (A/AD) en la mayoría de sus competencias. Se sugiere continuar estimulando su autonomía y pensamiento crítico.";
        } else {
            $partes[] = "El estudiante se encuentra en una etapa de consolidación de sus aprendizajes, requiriendo afianzar las competencias del área.";
        }

        // 2. Competencias en Proceso (B)
        if (!empty($nombresB)) {
            $partes[] = "Se recomienda fortalecer las competencias en proceso: " . implode(', ', $nombresB) . ".";
        }

        // 3. Competencias en Inicio (C)
        if (!empty($nombresC)) {
            $partes[] = "Asimismo, requiere acompañamiento pedagógico prioritario y coordinación estrecha con el apoderado en: " . implode(', ', $nombresC) . ".";
        }

        // 4. Frase de evolución respecto al bimestre anterior
        if ($tieneBimestreAnterior && $promedioActual !== null && $promedioAnterior !== null) {
            $delta = round($promedioActual - $promedioAnterior, 1);
            $umbralMejora = (float) config('analitica.diagnostico_estudiante.umbral_mejora', 1.0);
            $umbralDescenso = (float) config('analitica.diagnostico_estudiante.umbral_descenso', -1.0);

            if ($delta >= $umbralMejora) {
                $signo = $delta > 0 ? "+{$delta}" : "{$delta}";
                $partes[] = "Respecto al periodo anterior, muestra una evolución favorable con un incremento en su promedio general ({$signo} puntos).";
            } elseif ($delta <= $umbralDescenso) {
                $partes[] = "Respecto al periodo anterior, se observa un descenso en su promedio general ({$delta} puntos), por lo que se recomienda un plan de reforzamiento oportuno.";
            } else {
                $partes[] = "Mantiene un rendimiento académico estable respecto al periodo anterior.";
            }
        }

        return implode(' ', $partes);
    }
}
