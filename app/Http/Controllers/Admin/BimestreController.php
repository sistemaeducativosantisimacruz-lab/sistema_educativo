<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnoLectivo;
use App\Models\Bimestre;
use App\Models\GradoSeccion;
use App\Models\NotaBimestral;
use App\Services\PromedioService;
use Illuminate\Http\Request;

class BimestreController extends Controller
{
    protected $promedioService;

    public function __construct(PromedioService $promedioService)
    {
        $this->promedioService = $promedioService;
    }

    public function index()
    {
        $anoActivo = AnoLectivo::where('activo', true)->first();
        $bimestres = collect();

        if ($anoActivo) {
            $bimestres = Bimestre::where('ano_lectivo_id', $anoActivo->id)
                ->orderBy('numero')
                ->get();
        }

        return view('admin.bimestres.index', compact('bimestres', 'anoActivo'));
    }

    public function store(Request $request)
    {
        $anoActivo = AnoLectivo::where('activo', true)->first();
        if (!$anoActivo) {
            return back()->with('error', 'No hay año lectivo activo.');
        }

        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);

        // Contar cuántos bimestres ya existen para este año
        $existentes = Bimestre::where('ano_lectivo_id', $anoActivo->id)->count();
        
        if ($existentes >= 4) {
            return back()->with('error', 'Los 4 bimestres ya están inicializados para el año actual.');
        }

        $nuevoNumero = $existentes + 1;

        Bimestre::create([
            'ano_lectivo_id' => $anoActivo->id,
            'numero' => $nuevoNumero,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'estado' => 'abierto'
        ]);

        return redirect()->route('admin.bimestres.index')->with('success', "Bimestre {$nuevoNumero} aperturado correctamente.");
    }

    public function abrir(Request $request, Bimestre $bimestre)
    {
        $bimestre->update([
            'estado' => 'abierto',
            'cerrado_en' => null,
            'cerrado_por' => null,
        ]);

        return back()->with('success', "Bimestre {$bimestre->numero} ha sido reabierto.");
    }

    public function cerrar(Request $request, Bimestre $bimestre)
    {
        if ($bimestre->estado === 'cerrado') {
            return redirect()->route('admin.bimestres.index')->with('info', "El Bimestre {$bimestre->numero} ya se encuentra cerrado.");
        }

        // 1. Cambiar estado
        $bimestre->update([
            'estado' => 'cerrado',
            'cerrado_en' => now(),
            'cerrado_por' => auth()->id(),
        ]);

        // 2. Calcular Promedios
        try {
            $this->promedioService->calcularPorBimestre($bimestre);
            return redirect()->route('admin.bimestres.index')->with('success', "Bimestre {$bimestre->numero} cerrado. Se calcularon los promedios bimestrales exitosamente.");
        } catch (\Exception $e) {
            // Rollback visual si falla
            $bimestre->update(['estado' => 'abierto', 'cerrado_en' => null, 'cerrado_por' => null]);
            return redirect()->route('admin.bimestres.index')->with('error', 'Ocurrió un error al calcular los promedios: ' . $e->getMessage());
        }
    }

    public function confirmarCierre(Bimestre $bimestre)
    {
        if ($bimestre->estado === 'cerrado') {
            return redirect()->route('admin.bimestres.index')->with('info', "El Bimestre {$bimestre->numero} ya se encuentra cerrado.");
        }

        $anoActivo = AnoLectivo::where('activo', true)->first();
        if (!$anoActivo) {
            return redirect()->route('admin.bimestres.index')->with('error', "No hay año lectivo activo configurado.");
        }

        // Obtener todas las secciones del año activo con sus grados y secciones
        $secciones = GradoSeccion::with(['grado', 'seccion'])
            ->where('ano_lectivo_id', $anoActivo->id)
            ->get();

        $resumen = [];
        foreach ($secciones as $seccion) {
            // Estudiantes matriculados activos en esta seccion
            $estudianteIds = $seccion->matriculas()
                ->where('estado', 'matriculado')
                ->pluck('estudiante_id')
                ->toArray();

            $totalMatriculados = count($estudianteIds);

            if ($totalMatriculados === 0) {
                continue; // no students in section
            }

            // Estudiantes que sí tienen calificaciones en este bimestre
            $estudiantesConNotasCount = NotaBimestral::where('bimestre_id', $bimestre->id)
                ->whereIn('estudiante_id', $estudianteIds)
                ->distinct('estudiante_id')
                ->count('estudiante_id');

            $sinNotasCount = $totalMatriculados - $estudiantesConNotasCount;

            $resumen[] = [
                'seccion_nombre' => ($seccion->grado->nombre ?? '') . ' - ' . ($seccion->seccion->nombre ?? ''),
                'total_estudiantes' => $totalMatriculados,
                'estudiantes_sin_notas' => $sinNotasCount,
            ];
        }

        return view('admin.bimestres.confirmar-cierre', compact('bimestre', 'resumen', 'anoActivo'));
    }

    public function update(Request $request, Bimestre $bimestre)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);

        $bimestre->update([
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);

        return redirect()->route('admin.bimestres.index')->with('success', "Fechas del Bimestre {$bimestre->numero} actualizadas correctamente.");
    }

    public function togglePublicacionNotas(Request $request, Bimestre $bimestre)
    {
        $request->validate([
            'nivel' => 'required|in:primaria,secundaria,ambos',
            'accion' => 'required|in:publicar,ocultar',
        ]);

        $estado = $request->accion === 'publicar' ? true : false;
        
        $updates = [];
        if ($request->nivel === 'primaria' || $request->nivel === 'ambos') {
            $updates['notas_publicadas_primaria'] = $estado;
        }
        if ($request->nivel === 'secundaria' || $request->nivel === 'ambos') {
            $updates['notas_publicadas_secundaria'] = $estado;
        }

        $bimestre->update($updates);

        $accionStr = $request->accion === 'publicar' ? 'publicado' : 'ocultado';
        $nivelStr = ucfirst($request->nivel);
        return back()->with('success', "Se han {$accionStr} las notas del Bimestre {$bimestre->numero} para el nivel: {$nivelStr}.");
    }
}
