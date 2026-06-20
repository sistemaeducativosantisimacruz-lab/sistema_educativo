<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Redirección post-login según rol
Route::get('/dashboard', function () {
    $role = auth()->user()?->role?->nombre;
    return match($role) {
        'admin'      => redirect()->route('admin.dashboard'),
        'docente'    => redirect()->route('docente.dashboard'),
        'estudiante' => redirect()->route('estudiante.dashboard'),
        default      => redirect()->route('login'),
    };
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth', 'password.change'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/docente.php';
require __DIR__.'/estudiante.php';
