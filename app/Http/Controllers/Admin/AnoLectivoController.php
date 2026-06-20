<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnoLectivo;
use App\Models\Bimestre;
use Illuminate\Http\Request;

class AnoLectivoController extends Controller
{
    public function index()
    {
        $anosLectivos = AnoLectivo::orderBy('anio', 'desc')->get();
        $ultimoAno = $anosLectivos->first();
        $nextAnio = $ultimoAno ? $ultimoAno->anio + 1 : date('Y');

        return view('admin.anos-lectivos.index', compact('anosLectivos', 'nextAnio'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'anio' => 'required|integer|min:2000|unique:anos_lectivos,anio',
        ]);

        AnoLectivo::create([
            'anio' => $request->anio,
            'activo' => false,
        ]);

        return redirect()->route('admin.anos-lectivos.index')->with('success', "Año lectivo {$request->anio} registrado correctamente.");
    }

    public function activar(Request $request, AnoLectivo $anoLectivo)
    {
        // 1. Obtener el año activo actual si existe
        $anoActivo = AnoLectivo::where('activo', true)->first();

        if ($anoActivo) {
            // Si intentamos activar el mismo año que ya está activo, no hacemos nada
            if ($anoActivo->id === $anoLectivo->id) {
                return redirect()->route('admin.anos-lectivos.index')->with('info', "El año lectivo {$anoLectivo->anio} ya está activo.");
            }

            // 2. Validar que el año activo actual tenga todos sus 4 bimestres cerrados
            $bimestresCerrados = Bimestre::where('ano_lectivo_id', $anoActivo->id)
                ->where('estado', 'cerrado')
                ->count();

            if ($bimestresCerrados < 4) {
                return redirect()->route('admin.anos-lectivos.index')->with('error', "No se puede activar otro año lectivo. Para habilitar el próximo año lectivo, deben estar cerrados los 4 bimestres del año actual ({$anoActivo->anio}).");
            }
        }

        // 3. Activar el nuevo año y desactivar los otros
        AnoLectivo::where('id', '!=', $anoLectivo->id)->update(['activo' => false]);
        $anoLectivo->update(['activo' => true]);

        return redirect()->route('admin.anos-lectivos.index')->with('success', "Año lectivo {$anoLectivo->anio} activado correctamente como periodo activo de trabajo.");
    }
}
