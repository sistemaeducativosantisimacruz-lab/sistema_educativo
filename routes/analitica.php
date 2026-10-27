<?php

use Illuminate\Support\Facades\Route;

// Rutas para el Módulo de Analítica Académica
Route::middleware(['auth'])->prefix('analitica')->name('analitica.')->group(function () {
    
    // Redirige al tablero principal de analítica administrativa si tiene permisos, o carga la vista
    Route::get('/', function () {
        if (auth()->user()?->role?->nombre === 'admin') {
            return redirect()->route('admin.analytics.index');
        }
        return view('analitica.index');
    })->name('index');

    // Endpoint de API: Agregación de notas bimestrales
    Route::get('/distribucion', [\App\Http\Controllers\AnaliticaController::class, 'distribucion'])->name('distribucion');

});
