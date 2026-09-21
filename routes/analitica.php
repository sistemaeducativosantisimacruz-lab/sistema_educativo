<?php

use Illuminate\Support\Facades\Route;

// Rutas para el Módulo de Analítica Académica
Route::middleware(['auth'])->prefix('analitica')->name('analitica.')->group(function () {
    
    // Ruta principal del módulo
    Route::get('/', function () {
        return view('analitica.index');
    })->name('index');

    // Endpoint de Release 1: Agregación de notas bimestrales
    Route::get('/distribucion', [\App\Http\Controllers\AnaliticaController::class, 'distribucion'])->name('distribucion');

});
