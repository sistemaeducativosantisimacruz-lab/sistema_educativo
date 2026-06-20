<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Estudiante\DashboardController;
use App\Http\Controllers\Estudiante\NotasController;
use App\Http\Controllers\Estudiante\CursoController;

Route::middleware(['auth', 'role:estudiante', 'password.change'])->prefix('estudiante')->name('estudiante.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Cursos
    Route::get('/cursos', [CursoController::class, 'index'])->name('cursos.index');
    Route::get('/cursos/{asignacion}/{curso?}', [CursoController::class, 'show'])->name('cursos.show');

    // Notas
    Route::get('notas', [NotasController::class, 'index'])->name('notas.index');
    Route::get('notas/{asignacion}', [NotasController::class, 'show'])->name('notas.show');
    Route::get('notas/{asignacion}/sesion/{sesion}', [NotasController::class, 'sesion'])->name('notas.sesion');
    Route::get('notas/{asignacion}/promedios', [NotasController::class, 'promedios'])->name('notas.promedios');
});

