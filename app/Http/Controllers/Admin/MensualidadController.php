<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnoLectivo;
use App\Models\Grado;
use App\Models\GradoSeccion;
use App\Models\Mensualidad;
use App\Models\Matricula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MensualidadController extends Controller
{
    public function index(Request $request)
    {
        $anoActivo = AnoLectivo::where('activo', true)->first();

        $mes  = (int) $request->get('mes',  now()->month);
        $anio = (int) $request->get('anio', now()->year);

        $grados        = Grado::orderBy('nivel')->orderBy('orden')->get();
        $gradoSecciones = GradoSeccion::with(['grado', 'seccion'])
            ->when($anoActivo, fn ($q) => $q->where('ano_lectivo_id', $anoActivo->id))
            ->get();

        $query = Mensualidad::with([
            'matricula.estudiante',
            'matricula.gradoSeccion.grado',
            'matricula.gradoSeccion.seccion',
        ])
            ->where('mes', $mes)
            ->where('anio', $anio);

        if ($request->filled('nivel')) {
            $query->whereHas('matricula.gradoSeccion.grado', function ($q) use ($request) {
                $q->where('nivel', $request->nivel);
            });
        }

        if ($request->filled('grado_id')) {
            $query->whereHas('matricula.gradoSeccion', function ($q) use ($request) {
                $q->where('grado_id', $request->grado_id);
            });
        }

        if ($request->filled('grado_seccion_id')) {
            $query->whereHas('matricula', function ($q) use ($request) {
                $q->where('grado_seccion_id', $request->grado_seccion_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('matricula.estudiante', function ($q) use ($search) {
                $q->where('dni', 'ilike', "%{$search}%")
                    ->orWhere('nombres', 'ilike', "%{$search}%")
                    ->orWhere('apellido_paterno', 'ilike', "%{$search}%")
                    ->orWhere('apellido_materno', 'ilike', "%{$search}%");
            });
        }

        $mensualidades = (clone $query)
            ->join('matriculas', 'mensualidades.matricula_id', '=', 'matriculas.id')
            ->join('estudiantes', 'matriculas.estudiante_id', '=', 'estudiantes.id')
            ->orderBy('estudiantes.apellido_paterno')
            ->orderBy('estudiantes.apellido_materno')
            ->orderBy('estudiantes.nombres')
            ->select('mensualidades.*')
            ->paginate(50)
            ->withQueryString();

        $stats = (clone $query)
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $aniosDisponibles = Mensualidad::selectRaw('DISTINCT anio')
            ->orderBy('anio', 'desc')
            ->pluck('anio')
            ->push(now()->year)
            ->unique()
            ->sortDesc()
            ->values();

        return view('admin.mensualidades.index', compact(
            'mensualidades',
            'anoActivo',
            'grados',
            'gradoSecciones',
            'mes',
            'anio',
            'stats',
            'aniosDisponibles',
        ));
    }

    public function update(Request $request, Mensualidad $mensualidad)
    {
        $request->validate([
            'estado' => ['required', 'in:DEBE,PAGÓ,EXONERADO,BENEFICIADO'],
        ]);

        $mensualidad->update([
            'estado' => $request->estado,
        ]);

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    public function generar(Request $request)
    {
        $request->validate([
            'mes'  => ['required', 'integer', 'min:1', 'max:12'],
            'anio' => ['required', 'integer', 'min:2020', 'max:2100'],
        ]);

        $mes  = (int) $request->mes;
        $anio = (int) $request->anio;

        $anoActivo = AnoLectivo::where('activo', true)->first();

        if (! $anoActivo) {
            return back()->with('error', 'No hay un año lectivo activo.');
        }

        $matriculas = Matricula::where('ano_lectivo_id', $anoActivo->id)
            ->where('estado', 'matriculado')
            ->get();

        $nuevos = 0;
        DB::transaction(function () use ($matriculas, $mes, $anio, &$nuevos) {
            foreach ($matriculas as $matricula) {
                $existe = Mensualidad::where('matricula_id', $matricula->id)
                    ->where('mes', $mes)
                    ->where('anio', $anio)
                    ->exists();

                if (! $existe) {
                    Mensualidad::create([
                        'matricula_id' => $matricula->id,
                        'mes'          => $mes,
                        'anio'         => $anio,
                        'estado'       => 'DEBE',
                    ]);
                    $nuevos++;
                }
            }
        });

        $meses = Mensualidad::meses();

        if ($nuevos === 0) {
            return back()->with('error', 'La lista de mensualidades para ' . $meses[$mes] . ' ' . $anio . ' ya habia sido generada anteriormente. No se crearon registros nuevos.');
        }

        return back()->with('success', 'Se generaron ' . $nuevos . ' registros para ' . $meses[$mes] . ' ' . $anio . '.');
    }

    public function actualizarMasivo(Request $request)
    {
        $request->validate([
            'ids'    => ['required', 'array'],
            'ids.*'  => ['integer', 'exists:mensualidades,id'],
            'estado' => ['required', 'in:DEBE,PAGÓ,EXONERADO,BENEFICIADO'],
        ]);

        Mensualidad::whereIn('id', $request->ids)->update(['estado' => $request->estado]);

        return back()->with('success', count($request->ids) . ' registros actualizados a "' . $request->estado . '".');
    }
}
