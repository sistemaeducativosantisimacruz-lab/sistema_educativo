<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Docente\DashboardController;
use App\Http\Controllers\Docente\NotasController;

Route::middleware(['auth', 'role:docente', 'password.change'])->prefix('docente')->name('docente.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Secciones a cargo
    Route::get('/secciones', [DashboardController::class, 'secciones'])->name('secciones');

    // Notas por Sección y Estudiante
    Route::get('/seccion/{grado_seccion_id}/estudiantes', [NotasController::class, 'estudiantes'])->name('seccion.estudiantes');
    Route::get('/seccion/{grado_seccion_id}/estudiantes/{estudiante_id}/notas', [NotasController::class, 'verNotas'])->name('estudiante.notas');

    // Importación de Notas (Solo para tutores)
    Route::get('/importar', [\App\Http\Controllers\Docente\ImportController::class, 'create'])->name('importar');
    Route::post('/importar/preview', [\App\Http\Controllers\Docente\ImportController::class, 'preview'])->name('importar.preview');
    Route::post('/importar/confirmar', [\App\Http\Controllers\Docente\ImportController::class, 'confirmar'])->name('importar.confirmar');
    Route::delete('/importar/{id}', [\App\Http\Controllers\Docente\ImportController::class, 'revertir'])->name('importar.revertir');

});
