<?php

use Illuminate\Support\Facades\Route;

// Rutas para el Módulo de Analítica Académica
Route::middleware(['auth'])->prefix('analitica')->name('analitica.')->group(function () {
    
    // Ruta principal del módulo
    Route::get('/', function () {
        return view('analitica.index');
    })->name('index');

});
