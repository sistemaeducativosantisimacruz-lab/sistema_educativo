<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Docente\DashboardController;
use App\Http\Controllers\Docente\NotasController;
use App\Http\Controllers\Docente\AdmisionController;

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

    // Entrevistas de Admisión (Solo docentes con rol de entrevistador asignado)
    Route::prefix('admision')->name('admision.')->middleware('entrevistador')->group(function () {
        Route::get('/', [AdmisionController::class, 'index'])->name('index');
        Route::get('/buscar-estudiantes', [AdmisionController::class, 'buscarEstudiantes'])->name('buscar-estudiantes');
        Route::get('/entrevista/crear', [AdmisionController::class, 'create'])->name('create');
        Route::get('/entrevista/{entrevista}/editar', [AdmisionController::class, 'edit'])->name('edit');
        Route::post('/entrevista/guardar', [AdmisionController::class, 'guardarEnLinea'])->name('guardar-en-linea');
        Route::post('/entrevista/{entrevista}/cerrar', [AdmisionController::class, 'cerrarEntrevista'])->name('cerrar');
        Route::get('/plantilla', [AdmisionController::class, 'descargarPlantilla'])->name('plantilla');
        Route::post('/importar', [AdmisionController::class, 'importar'])->name('importar');
        Route::get('/{entrevista}', [AdmisionController::class, 'show'])->name('show');
    });

});
