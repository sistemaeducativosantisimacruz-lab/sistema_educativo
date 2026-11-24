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
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AdmisionController;
use App\Http\Controllers\Admin\DirectivoController;

Route::middleware(['auth', 'role:admin,directivo', 'directivo.activo', 'password.change'])->prefix('admin')->name('admin.')->group(function () {

    // ==========================================
    // RUTAS DE CONSULTA / LECTURA (Admin y Directivo)
    // ==========================================

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Grados y Secciones (Lectura)
    Route::get('grado-secciones', [GradoSeccionController::class, 'index'])->name('grado-secciones.index');
    Route::get('grado-secciones/{gradoSeccion}/tutores', [GradoSeccionController::class, 'getTutores'])->name('grado-secciones.get-tutores');
    Route::get('grado-secciones/{gradoSeccion}/detalle', [GradoSeccionController::class, 'detalle'])->name('grado-secciones.detalle');
    Route::get('grado-secciones/{gradoSeccion}/exportar', [GradoSeccionController::class, 'exportar'])->name('grado-secciones.exportar');

    // Docentes (Lectura)
    Route::get('docentes', [DocenteController::class, 'index'])->name('docentes.index');
    Route::get('docentes/{docente}', [DocenteController::class, 'show'])->name('docentes.show');

    // Estudiantes (Lectura)
    Route::get('estudiantes', [EstudianteController::class, 'index'])->name('estudiantes.index');
    Route::get('estudiantes/{estudiante}', [EstudianteController::class, 'show'])->name('estudiantes.show');

    // Bimestres (Lectura)
    Route::get('bimestres', [BimestreController::class, 'index'])->name('bimestres.index');
    Route::get('bimestres/{bimestre}', [BimestreController::class, 'show'])->name('bimestres.show');

    // Años Lectivos (Lectura)
    Route::get('anos-lectivos', [AnoLectivoController::class, 'index'])->name('anos-lectivos.index');

    // Analítica (Lectura completa / Informes PDF)
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/estudiantes/{id}/diagnostico', [AnalyticsController::class, 'diagnosticoEstudiante'])->name('analytics.estudiante.diagnostico');
    Route::get('analytics/reporte-estudiante-pdf', [AnalyticsController::class, 'reporteEstudiantePdf'])->name('analytics.reporte_estudiante_pdf');
    Route::get('analytics/reporte-comparativo-pdf', [AnalyticsController::class, 'reporteComparativoPdf'])->name('analytics.reporte_comparativo_pdf');

    // Rendimiento / Reportes (Lectura y descargas)
    Route::get('rendimiento', [RendimientoController::class, 'index'])->name('rendimiento.index');
    Route::get('rendimiento/exportar', [RendimientoController::class, 'exportar'])->name('rendimiento.exportar');
    Route::get('rendimiento/exportar-estudiante', [RendimientoController::class, 'exportarReporteEstudiante'])->name('rendimiento.exportar_estudiante');
    Route::get('rendimiento/exportar-consolidado', [RendimientoController::class, 'exportarConsolidado'])->name('rendimiento.exportar_consolidado');
    Route::get('rendimiento/exportar-docentes', [RendimientoController::class, 'exportarDocentes'])->name('rendimiento.exportar_docentes');
    Route::get('rendimiento/exportar-criticos', [RendimientoController::class, 'exportarCriticos'])->name('rendimiento.exportar_criticos');
    Route::get('rendimiento/exportar-secciones', [RendimientoController::class, 'exportarSecciones'])->name('rendimiento.exportar_secciones');
    Route::get('rendimiento/exportar-deudas', [RendimientoController::class, 'exportarDeudas'])->name('rendimiento.exportar_deudas');

    // Mensualidades (Lectura)
    Route::get('mensualidades', [MensualidadController::class, 'index'])->name('mensualidades.index');

    // Admisión (Lectura)
    Route::prefix('admision')->name('admision.')->group(function () {
        Route::get('/', [AdmisionController::class, 'index'])->name('index');
        Route::get('/buscar-estudiantes', [AdmisionController::class, 'buscarEstudiantes'])->name('buscar-estudiantes');
        Route::get('/plantilla', [AdmisionController::class, 'descargarPlantilla'])->name('plantilla');
        Route::get('/{entrevista}', [AdmisionController::class, 'show'])->name('show');
    });

    // ==========================================
    // RUTAS DE ESCRITURA Y ACCIONES (Solo rol:admin)
    // ==========================================
    Route::middleware(['role:admin'])->group(function () {
        // Grados y Secciones (Escritura)
        Route::post('grado-secciones', [GradoSeccionController::class, 'store'])->name('grado-secciones.store');
        Route::get('grado-secciones/create', [GradoSeccionController::class, 'create'])->name('grado-secciones.create');
        Route::get('grado-secciones/{gradoSeccion}/edit', [GradoSeccionController::class, 'edit'])->name('grado-secciones.edit');
        Route::put('grado-secciones/{gradoSeccion}', [GradoSeccionController::class, 'update'])->name('grado-secciones.update');
        Route::delete('grado-secciones/{gradoSeccion}', [GradoSeccionController::class, 'destroy'])->name('grado-secciones.destroy');
        Route::patch('grado-secciones/{gradoSeccion}/tutores', [GradoSeccionController::class, 'updateTutores'])->name('grado-secciones.tutores');
        Route::patch('grado-secciones/{gradoSeccion}/docentes', [GradoSeccionController::class, 'updateDocentes'])->name('grado-secciones.update-docentes');

        // Docentes (Escritura)
        Route::post('docentes', [DocenteController::class, 'store'])->name('docentes.store');
        Route::get('docentes/create', [DocenteController::class, 'create'])->name('docentes.create');
        Route::get('docentes/{docente}/edit', [DocenteController::class, 'edit'])->name('docentes.edit');
        Route::put('docentes/{docente}', [DocenteController::class, 'update'])->name('docentes.update');
        Route::delete('docentes/{docente}', [DocenteController::class, 'destroy'])->name('docentes.destroy');
        Route::post('docentes/{docente}/asignar', [DocenteController::class, 'asignar'])->name('docentes.asignar');
        Route::post('docentes/{docente}/verificar-conflictos', [DocenteController::class, 'verificarConflictos'])->name('docentes.verificarConflictos');
        Route::delete('docentes/{docente}/desasignar/{asignacion}', [DocenteController::class, 'desasignar'])->name('docentes.desasignar');
        Route::post('docentes/{docente}/retirar', [DocenteController::class, 'retirar'])->name('docentes.retirar');
        Route::post('docentes/{docente}/reincorporar', [DocenteController::class, 'reincorporar'])->name('docentes.reincorporar');

        // Directivos (Escritura)
        Route::post('directivos', [DirectivoController::class, 'store'])->name('directivos.store');
        Route::put('directivos/{directivo}', [DirectivoController::class, 'update'])->name('directivos.update');
        Route::delete('directivos/{directivo}', [DirectivoController::class, 'destroy'])->name('directivos.destroy');
        Route::post('directivos/{directivo}/reemplazar', [DirectivoController::class, 'reemplazar'])->name('directivos.reemplazar');
        Route::post('directivos/{directivo}/quitar', [DirectivoController::class, 'quitar'])->name('directivos.quitar');
        Route::post('directivos/{directivo}/reincorporar', [DirectivoController::class, 'reincorporar'])->name('directivos.reincorporar');

        // Estudiantes (Escritura)
        Route::post('estudiantes', [EstudianteController::class, 'store'])->name('estudiantes.store');
        Route::get('estudiantes/create', [EstudianteController::class, 'create'])->name('estudiantes.create');
        Route::get('estudiantes/{estudiante}/edit', [EstudianteController::class, 'edit'])->name('estudiantes.edit');
        Route::put('estudiantes/{estudiante}', [EstudianteController::class, 'update'])->name('estudiantes.update');
        Route::delete('estudiantes/{estudiante}', [EstudianteController::class, 'destroy'])->name('estudiantes.destroy');
        Route::patch('estudiantes/{estudiante}/mover', [EstudianteController::class, 'mover'])->name('estudiantes.mover');
        Route::post('estudiantes/{estudiante}/retirar', [EstudianteController::class, 'retirar'])->name('estudiantes.retirar');
        Route::post('estudiantes/{estudiante}/reincorporar', [EstudianteController::class, 'reincorporar'])->name('estudiantes.reincorporar');

        // Bimestres (Escritura)
        Route::post('bimestres', [BimestreController::class, 'store'])->name('bimestres.store');
        Route::put('bimestres/{bimestre}', [BimestreController::class, 'update'])->name('bimestres.update');
        Route::get('bimestres/{bimestre}/confirmar-cierre', [BimestreController::class, 'confirmarCierre'])->name('bimestres.confirmar_cierre');
        Route::post('bimestres/{bimestre}/abrir', [BimestreController::class, 'abrir'])->name('bimestres.abrir');
        Route::post('bimestres/{bimestre}/cerrar', [BimestreController::class, 'cerrar'])->name('bimestres.cerrar');
        Route::post('bimestres/{bimestre}/toggle-publicacion', [BimestreController::class, 'togglePublicacionNotas'])->name('bimestres.toggle_publicacion');

        // Años Lectivos (Escritura)
        Route::post('anos-lectivos', [AnoLectivoController::class, 'store'])->name('anos-lectivos.store');
        Route::post('anos-lectivos/{ano_lectivo}/activar', [AnoLectivoController::class, 'activar'])->name('anos-lectivos.activar');

        // Importaciones
        Route::get('importaciones/create', [ImportController::class, 'create'])->name('importaciones.create');
        Route::post('importaciones/preview', [ImportController::class, 'preview'])->name('importaciones.preview');
        Route::post('importaciones/check-notas', [ImportController::class, 'checkNotasExist'])->name('importaciones.check_notas');
        Route::post('importaciones/confirmar', [ImportController::class, 'confirmar'])->name('importaciones.confirmar');
        Route::delete('importaciones/{id}/revertir', [ImportController::class, 'revertir'])->name('importaciones.revertir');
        Route::get('importar', [ImportController::class, 'createImportar'])->name('importar');

        // Mensualidades (Escritura)
        Route::patch('mensualidades/{mensualidad}', [MensualidadController::class, 'update'])->name('mensualidades.update');
        Route::post('mensualidades/generar', [MensualidadController::class, 'generar'])->name('mensualidades.generar');
        Route::post('mensualidades/masivo', [MensualidadController::class, 'actualizarMasivo'])->name('mensualidades.masivo');

        // Admisión (Escritura)
        Route::prefix('admision')->name('admision.')->group(function () {
            Route::get('/entrevista/crear', [AdmisionController::class, 'create'])->name('create');
            Route::get('/entrevista/{entrevista}/editar', [AdmisionController::class, 'edit'])->name('edit');
            Route::post('/entrevista/guardar', [AdmisionController::class, 'guardarEnLinea'])->name('guardar-en-linea');
            Route::post('/entrevista/{entrevista}/cerrar', [AdmisionController::class, 'cerrarEntrevista'])->name('cerrar');
            Route::post('/importar', [AdmisionController::class, 'importar'])->name('importar');
            Route::post('/docentes/{docente}/toggle-entrevistador', [AdmisionController::class, 'toggleEntrevistador'])->name('docentes.toggle');
            Route::post('/{entrevista}/matricular', [AdmisionController::class, 'matricular'])->name('matricular');
            Route::delete('/{entrevista}', [AdmisionController::class, 'destroy'])->name('destroy');
        });
    });
});
