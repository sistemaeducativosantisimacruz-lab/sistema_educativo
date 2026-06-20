<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotasController extends Controller
{
    public function index()
    {
        return redirect()->route('estudiante.dashboard');
    }

    public function show($asignacion)
    {
        return redirect()->route('estudiante.dashboard');
    }

    public function sesion($asignacion, $sesion)
    {
        return redirect()->route('estudiante.dashboard');
    }

    public function promedios($asignacion)
    {
        return redirect()->route('estudiante.dashboard');
    }
}
