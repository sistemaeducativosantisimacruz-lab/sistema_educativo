<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\AnoLectivo;
use App\Models\AsignacionDocente;
use App\Models\Estudiante;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $estudiante = Estudiante::with([
            'apoderado',
            'matriculas' => function ($q) {
                $q->whereHas('anoLectivo', fn ($a) => $a->where('activo', true));
            },
            'matriculas.gradoSeccion.grado',
            'matriculas.gradoSeccion.seccion',
            'matriculas.gradoSeccion.tutor.cursos',
            'matriculas.gradoSeccion.cotutor.cursos',
            'matriculas.gradoSeccion.asignacionesDocente.docente',
            'matriculas.gradoSeccion.asignacionesDocente.curso',
            'matriculas.anoLectivo',
        ])->where('user_id', $user->id)->first();

        $matriculaActiva = $estudiante?->matriculas->first();

        return view('estudiante.dashboard', compact('estudiante', 'matriculaActiva'));
    }
}
