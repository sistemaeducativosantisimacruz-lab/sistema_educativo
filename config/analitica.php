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
            'bg' => '#991b1b', // red-800
            'text' => '#ffffff',
            'badge' => 'bg-red-800 text-white',
        ],
        'B' => [
            'nombre' => 'En Proceso',
            'bg' => '#f59e0b', // amber-500
            'text' => '#1f2937',
            'badge' => 'bg-amber-400 text-gray-900',
        ],
        'A' => [
            'nombre' => 'Logro Esperado',
            'bg' => '#0d9488', // teal-600
            'text' => '#ffffff',
            'badge' => 'bg-teal-600 text-white',
        ],
        'AD' => [
            'nombre' => 'Logro Destacado',
            'bg' => '#2563eb', // blue-600
            'text' => '#ffffff',
            'badge' => 'bg-blue-600 text-white',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reglas Base para Diagnóstico Cualitativo
    |--------------------------------------------------------------------------
    |
    | Umbrales y textos pedagógicos utilizados para la interpretación
    | automática a nivel de competencia y de aula.
    |
    */
    'diagnostico' => [
        'alerta_urgente' => 50.0,
        'atencion_focalizada' => 30.0,
        'logro_destacado' => 80.0,
        'logro_favorable' => 50.0,
    ],
];
