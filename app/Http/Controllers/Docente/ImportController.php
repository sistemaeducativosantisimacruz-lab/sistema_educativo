<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Admin\ImportController as AdminImportController;
use App\Models\AnoLectivo;
use App\Models\GradoSeccion;
use App\Models\Bimestre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ExcelPreviewService;

class ImportController extends AdminImportController
{
    /**
     * Verifica si el docente logueado tiene permisos de tutoría 
     * y obtiene las secciones a su cargo.
     */
    private function getSeccionesTutor()
    {
        $docente = Auth::user()->docente;
        
        if (!$docente) {
            abort(403, 'No tienes perfil de docente asignado.');
        }

        $secciones = $docente->tutoriaSecciones()->with(['grado', 'seccion'])->get();

        if ($secciones->isEmpty()) {
            abort(403, 'Solo los docentes con cargo de tutor pueden importar notas de su sección.');
        }

        return $secciones;
    }

    public function create()
    {
        $secciones = $this->getSeccionesTutor();
        $anoActivo = AnoLectivo::where('activo', true)->first();
        $bimestres = collect();

        if ($anoActivo) {
            $bimestres = Bimestre::where('ano_lectivo_id', $anoActivo->id)->get();
        }

        // Recuperar el historial de importaciones pero filtrado solo para este docente o sus secciones
        $historial = \App\Models\ImportacionSiagie::with('admin')
            ->whereIn('grado_seccion_id', $secciones->pluck('id'))
            ->where('tipo', 'notas')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('docente.importar', compact('secciones', 'anoActivo', 'bimestres', 'historial'));
    }

    public function preview(Request $request, ExcelPreviewService $excelService)
    {
        // Usa el preview del padre (AdminImportController) 
        // ya que la lógica de preview del archivo Excel es idéntica
        return parent::preview($request, $excelService);
    }

    public function confirmar(Request $request, ExcelPreviewService $excelService)
    {
        $secciones = $this->getSeccionesTutor();
        
        // Validar que el grado_seccion_id exista y pertenezca al tutor
        $request->validate([
            'grado_seccion_id' => 'required|exists:grado_secciones,id',
            'bimestre_id' => 'required|exists:bimestres,id',
        ]);

        if (!$secciones->contains('id', $request->grado_seccion_id)) {
            return response()->json(['success' => false, 'message' => 'No tienes permisos de tutor para esta sección.'], 403);
        }

        $gradoSeccion = $secciones->firstWhere('id', $request->grado_seccion_id);

        // Inyectar datos en el request para que el método del padre funcione sin modificaciones
        $request->merge([
            'tipo' => 'notas', // El docente solo importa notas
            'nivel_id' => $gradoSeccion->grado->nivel,
            'grado_id' => $gradoSeccion->grado_id,
            'seccion_id' => $gradoSeccion->seccion_id,
        ]);

        return parent::confirmar($request, $excelService);
    }

    public function revertir($id)
    {
        $secciones = $this->getSeccionesTutor();
        
        $importacion = \App\Models\ImportacionSiagie::findOrFail($id);

        if (!$secciones->contains('id', $importacion->grado_seccion_id)) {
            return response()->json(['success' => false, 'message' => 'No puedes revertir una importación de una sección que no tienes a cargo.'], 403);
        }

        return parent::revertir($id);
    }
}
