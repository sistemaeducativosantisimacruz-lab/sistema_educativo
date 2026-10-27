<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Umbral de Alerta de Riesgo Pedagógico
    |--------------------------------------------------------------------------
    |
    | Define el porcentaje mínimo acumulado de estudiantes en niveles de
    | inicio (C) y proceso (B) a partir del cual se resalta una competencia
    | o se activa una alerta pedagógica en el panel. Por defecto: 40 %.
    |
    */
    'umbral_alerta_riesgo' => 40.0,

    /*
    |--------------------------------------------------------------------------
    | Paleta de Colores de Niveles de Logro
    |--------------------------------------------------------------------------
    |
    | Configuración visual institucional para asegurar legibilidad, contraste
    | y cumplimiento de estándares de diseño inclusivo.
    | - C: Rojo oscuro con texto blanco
    | - B: Ámbar con texto oscuro
    | - A: Verde azulado con texto blanco
    | - AD: Azul con texto blanco
    |
    */
    'colores' => [
        'C' => [
            'nombre' => 'En Inicio',
            'bg' => '#b91c1c', // red-700
            'text' => '#ffffff',
            'badge' => 'bg-[#b91c1c] text-white',
        ],
        'B' => [
            'nombre' => 'En Proceso',
            'bg' => '#f59e0b', // amber-500
            'text' => '#1f2937',
            'badge' => 'bg-[#f59e0b] text-gray-900',
        ],
        'A' => [
            'nombre' => 'Logro Esperado',
            'bg' => '#0f766e', // teal-700
            'text' => '#ffffff',
            'badge' => 'bg-[#0f766e] text-white',
        ],
        'AD' => [
            'nombre' => 'Logro Destacado',
            'bg' => '#1e40af', // blue-800
            'text' => '#ffffff',
            'badge' => 'bg-[#1e40af] text-white',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reglas Base para Diagnóstico Cualitativo de Aula
    |--------------------------------------------------------------------------
    */
    'diagnostico' => [
        'alerta_urgente' => 50.0,
        'atencion_focalizada' => 30.0,
        'logro_destacado' => 80.0,
        'logro_favorable' => 50.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Reglas para Diagnóstico Individual por Estudiante
    |--------------------------------------------------------------------------
    */
    'diagnostico_estudiante' => [
        'umbral_mayoria' => 50.0,    // 50 % o más de competencias en A/AD
        'umbral_mejora' => 1.0,      // Variación >= +1.0 en promedio numérico
        'umbral_descenso' => -1.0,   // Variación <= -1.0 en promedio numérico
    ],
];
