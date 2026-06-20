<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GradoSeccionController;
use App\Http\Controllers\Admin\DocenteController;
use App\Http\Controllers\Admin\EstudianteController;
use App\Http\Controllers\Admin\BimestreController;
use App\Http\Controllers\Admin\RendimientoController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\MensualidadController;
use App\Http\Controllers\Admin\AnoLectivoController;

Route::middleware(['auth', 'role:admin', 'password.change'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Grados y Secciones
    Route::resource('grado-secciones', GradoSeccionController::class);
    Route::patch('grado-secciones/{grado_seccione}/tutores', [GradoSeccionController::class, 'updateTutores'])->name('grado-secciones.tutores');
    Route::get('grado-secciones/{grado_seccione}/tutores', [GradoSeccionController::class, 'getTutores'])->name('grado-secciones.get-tutores');
    Route::get('grado-secciones/{gradoSeccion}/detalle', [GradoSeccionController::class, 'detalle'])->name('grado-secciones.detalle');
    Route::get('grado-secciones/{gradoSeccion}/exportar', [GradoSeccionController::class, 'exportar'])->name('grado-secciones.exportar');
    Route::patch('grado-secciones/{gradoSeccion}/docentes', [GradoSeccionController::class, 'updateDocentes'])->name('grado-secciones.update-docentes');

    // Docentes y asignaciones
    Route::resource('docentes', DocenteController::class);
    Route::post('docentes/{docente}/asignar', [DocenteController::class, 'asignar'])->name('docentes.asignar');
    Route::post('docentes/{docente}/verificar-conflictos', [DocenteController::class, 'verificarConflictos'])->name('docentes.verificarConflictos');
    Route::delete('docentes/{docente}/desasignar/{asignacion}', [DocenteController::class, 'desasignar'])->name('docentes.desasignar');

    // Estudiantes
    Route::resource('estudiantes', EstudianteController::class);
    Route::patch('estudiantes/{estudiante}/mover', [EstudianteController::class, 'mover'])->name('estudiantes.mover');
    Route::post('estudiantes/{estudiante}/retirar', [EstudianteController::class, 'retirar'])->name('estudiantes.retirar');


    // Bimestres
    Route::resource('bimestres', BimestreController::class)->only(['index', 'store', 'show', 'update']);
    Route::get('bimestres/{bimestre}/confirmar-cierre', [BimestreController::class, 'confirmarCierre'])->name('bimestres.confirmar_cierre');
    Route::post('bimestres/{bimestre}/abrir', [BimestreController::class, 'abrir'])->name('bimestres.abrir');
    Route::post('bimestres/{bimestre}/cerrar', [BimestreController::class, 'cerrar'])->name('bimestres.cerrar');
    Route::post('bimestres/{bimestre}/toggle-publicacion', [BimestreController::class, 'togglePublicacionNotas'])->name('bimestres.toggle_publicacion');

    // Años Lectivos
    Route::resource('anos-lectivos', AnoLectivoController::class)->only(['index', 'store']);
    Route::post('anos-lectivos/{ano_lectivo}/activar', [AnoLectivoController::class, 'activar'])->name('anos-lectivos.activar');

    // Rendimiento / Reportes
    Route::get('rendimiento', [RendimientoController::class, 'index'])->name('rendimiento.index');
    Route::get('rendimiento/exportar', [RendimientoController::class, 'exportar'])->name('rendimiento.exportar');
    Route::get('rendimiento/exportar-estudiante', [RendimientoController::class, 'exportarReporteEstudiante'])->name('rendimiento.exportar_estudiante');
    Route::get('rendimiento/exportar-consolidado', [RendimientoController::class, 'exportarConsolidado'])->name('rendimiento.exportar_consolidado');
    Route::get('rendimiento/exportar-docentes', [RendimientoController::class, 'exportarDocentes'])->name('rendimiento.exportar_docentes');
    Route::get('rendimiento/exportar-criticos', [RendimientoController::class, 'exportarCriticos'])->name('rendimiento.exportar_criticos');
    Route::get('rendimiento/exportar-secciones', [RendimientoController::class, 'exportarSecciones'])->name('rendimiento.exportar_secciones');
    Route::get('rendimiento/exportar-deudas', [RendimientoController::class, 'exportarDeudas'])->name('rendimiento.exportar_deudas');

    // Importaciones
    Route::get('importaciones/create', [ImportController::class, 'create'])->name('importaciones.create');
    Route::post('importaciones/preview', [ImportController::class, 'preview'])->name('importaciones.preview');
    Route::post('importaciones/confirmar', [ImportController::class, 'confirmar'])->name('importaciones.confirmar');
    Route::delete('importaciones/{id}/revertir', [ImportController::class, 'revertir'])->name('importaciones.revertir');
    
    // New Import page
    Route::get('importar', [ImportController::class, 'createImportar'])->name('importar');

    Route::get('mensualidades', [MensualidadController::class, 'index'])->name('mensualidades.index');
    Route::patch('mensualidades/{mensualidad}', [MensualidadController::class, 'update'])->name('mensualidades.update');
    Route::post('mensualidades/generar', [MensualidadController::class, 'generar'])->name('mensualidades.generar');
    Route::post('mensualidades/masivo', [MensualidadController::class, 'actualizarMasivo'])->name('mensualidades.masivo');
});
