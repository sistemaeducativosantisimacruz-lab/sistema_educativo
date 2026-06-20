<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnoLectivo;
use App\Models\GradoSeccion;
use App\Services\ExcelPreviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Estudiante;
use App\Models\Apoderado;
use App\Models\ImportacionSiagie;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class ImportController extends Controller
{
    public function create()
    {
        $anoActivo = AnoLectivo::where('activo', true)->first();
        $gradoSecciones = collect();
        $bimestres = collect();
        if ($anoActivo) {
            $gradoSecciones = GradoSeccion::join('grados', 'grado_secciones.grado_id', '=', 'grados.id')
                ->join('secciones', 'grado_secciones.seccion_id', '=', 'secciones.id')
                ->where('grado_secciones.ano_lectivo_id', $anoActivo->id)
                ->where('grado_secciones.activo', true)
                ->orderBy('grados.nivel')
                ->orderBy('grados.orden')
                ->orderBy('secciones.nombre')
                ->select('grado_secciones.*')
                ->with(['grado', 'seccion'])
                ->get();
            $bimestres = \App\Models\Bimestre::where('ano_lectivo_id', $anoActivo->id)->get();
        }
        $niveles = \DB::table('grados')->distinct()->orderBy('nivel')->pluck('nivel');
        return view('admin.importaciones.create', compact('gradoSecciones', 'anoActivo', 'bimestres', 'niveles'));
    }

    public function preview(Request $request, ExcelPreviewService $excelService)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:20480',
        ]);
        try {
            $file = $request->file('file');
            $preview = $excelService->parse($file->getRealPath());
            return response()->json([
                'success' => true,
                'columns' => $preview['headers'],
                'rows' => $preview['rows'],
                'original_name' => $file->getClientOriginalName(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Import preview error: '.$e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // New page for import wizard (standalone view)
    public function createImportar()
    {
        $anoActivo = AnoLectivo::where('activo', true)->first();
        $gradoSecciones = collect();
        $bimestres = collect();
        $historial = collect();
        if ($anoActivo) {
            $gradoSecciones = GradoSeccion::join('grados', 'grado_secciones.grado_id', '=', 'grados.id')
                ->join('secciones', 'grado_secciones.seccion_id', '=', 'secciones.id')
                ->where('grado_secciones.ano_lectivo_id', $anoActivo->id)
                ->where('grado_secciones.activo', true)
                ->orderBy('grados.nivel')
                ->orderBy('grados.orden')
                ->orderBy('secciones.nombre')
                ->select('grado_secciones.*')
                ->with(['grado', 'seccion'])
                ->get();
            $bimestres = \App\Models\Bimestre::where('ano_lectivo_id', $anoActivo->id)->get();
            
            $historial = \App\Models\ImportacionSiagie::with(['admin', 'gradoSeccion.grado', 'gradoSeccion.seccion'])
                ->where('ano_lectivo_id', $anoActivo->id)
                ->latest()
                ->paginate(20);
        }
        $niveles = \DB::table('grados')->distinct()->orderBy('nivel')->pluck('nivel');
        return view('admin.importar', compact('gradoSecciones', 'anoActivo', 'bimestres', 'niveles', 'historial'));
    }

    public function confirmar(Request $request, ExcelPreviewService $excelService)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:20480',
            'mapping' => 'required|json',
            'tipo' => 'required|string',
            'nivel_id' => 'required|string',
            'grado_id' => 'required|string',
            'seccion_id' => 'required|string',
            'unselected_indices' => 'nullable|json',
        ]);

        try {
            $mapping = json_decode($request->mapping, true);
            $unselectedIndices = $request->unselected_indices ? json_decode($request->unselected_indices, true) : [];
            $unselectedSet = array_flip($unselectedIndices);

            $file = $request->file('file');
            $anoActivo = AnoLectivo::where('activo', true)->first();
            
            $gradoSeccion = null;
            if ($request->grado_id !== 'todos' && $request->seccion_id !== 'todos') {
                $gradoSeccion = GradoSeccion::where('grado_id', $request->grado_id)
                    ->where('seccion_id', $request->seccion_id)
                    ->where('ano_lectivo_id', $anoActivo->id)
                    ->first();
            }

            if ($request->tipo === 'notas') {
                $bimestreId = $request->input('bimestre_id');
                $bimestre = \App\Models\Bimestre::find($bimestreId);
                if (!$bimestre || $bimestre->estado === 'cerrado') {
                    return response()->json(['success' => false, 'message' => 'El bimestre seleccionado no existe o ya está cerrado.'], 422);
                }
                if (!$request->bimestre_id) {
                    throw new \Exception('Bimestre requerido para la importación de notas.');
                }
                
                $stats = new \stdClass();
                $stats->procesados = [];
                $stats->errores = [];

                \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\NotasBimestralesImport($request->bimestre_id, $gradoSeccion?->id, $file->getRealPath(), $stats, $anoActivo->id), $file);
                
                $estudiantesCount = count($stats->procesados);
                
                // Si no se procesó ningún estudiante y hay errores, el estado es "errores" (no "con_errores")
                // Si se procesó al menos uno y hay errores, es "con_errores"
                $estado = count($stats->errores) > 0 ? ($estudiantesCount === 0 ? 'errores' : 'con_errores') : 'exitoso';
                
                $importacion = new ImportacionSiagie([
                    'admin_id' => Auth::id() ?? 1,
                    'grado_seccion_id' => $gradoSeccion?->id,
                    'ano_lectivo_id' => $anoActivo?->id,
                    'nombre_archivo' => $file->getClientOriginalName(),
                    'tipo' => $request->tipo,
                    'estudiantes_importados' => $estudiantesCount,
                    'errores' => count($stats->errores) > 0 ? $stats->errores : null,
                    'estado' => $estado,
                ]);
                $importacion->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Proceso de importación finalizado. Se importaron/actualizaron notas de ' . $estudiantesCount . ' estudiantes.',
                    'errores' => $stats->errores
                ]);
            }

            $preview = $excelService->parseComplete($file->getRealPath());
            $rows = $preview['rows'];

            $importados = 0;
            $errores = [];
            $seCrearonEstudiantes = false;

            $roleEstudiante = Role::where('nombre', 'estudiante')->first();

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                if (isset($unselectedSet[$index])) {
                    continue;
                }

                // Ignorar filas totalmente vacías
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    if ($request->tipo === 'directorio') {
                        $this->importarDirectorio($row, $mapping, $request, $roleEstudiante, $gradoSeccion, $anoActivo);
                    } elseif ($request->tipo === 'estudiantes') {
                        $this->importarEstudiantes($row, $mapping, $request, $roleEstudiante, $gradoSeccion, $anoActivo);
                    } elseif ($request->tipo === 'padres') {
                        $creoEstudiante = $this->importarPadres($row, $mapping, $request, $roleEstudiante, $gradoSeccion, $anoActivo);
                        if ($creoEstudiante) {
                            $seCrearonEstudiantes = true;
                        }
                    } else {
                        $this->importarSiagie($row, $mapping, $request);
                    }
                    $importados++;
                } catch (\Exception $e) {
                    $errores[] = "Fila " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            $tipoAGuardar = $request->tipo;
            if ($tipoAGuardar === 'padres' && $seCrearonEstudiantes) {
                $tipoAGuardar = 'padres/estudiante';
            }

            $importacion = new ImportacionSiagie([
                'admin_id' => Auth::id() ?? 1,
                'grado_seccion_id' => $gradoSeccion?->id,
                'ano_lectivo_id' => $anoActivo?->id,
                'nombre_archivo' => $file->getClientOriginalName(),
                'tipo' => $tipoAGuardar,
                'estudiantes_importados' => $importados,
                'errores' => count($errores) > 0 ? $errores : null,
                'estado' => count($errores) > 0 ? 'con_errores' : 'exitoso',
            ]);
            $importacion->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Importación exitosa. Se procesaron ' . $importados . ' registros.',
                'errores' => count($errores) > 0 ? $errores : []
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Import confirmar error: '.$e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function revertir($id)
    {
        try {
            $importacion = \App\Models\ImportacionSiagie::findOrFail($id);
            $time = $importacion->created_at;

            DB::beginTransaction();

            $start = $time->copy()->subSeconds(10);
            $end = $time->copy()->addSeconds(10);

            if ($importacion->tipo === 'notas') {
                \DB::table('calificaciones')
                    ->whereBetween('created_at', [$start, $end])
                    ->delete();
                
                \DB::table('promedios_bimestrales')
                    ->whereBetween('created_at', [$start, $end])
                    ->delete();
            } else {
                // Borramos registros creados en esta importación
                \App\Models\Apoderado::whereBetween('created_at', [$start, $end])->delete();
                \DB::table('matriculas')->whereBetween('created_at', [$start, $end])->delete();
                \App\Models\Estudiante::whereBetween('created_at', [$start, $end])->delete();

                // Eliminar usuarios estudiantes creados
                \App\Models\User::whereHas('role', function($q) {
                    $q->where('nombre', 'estudiante');
                })->whereBetween('created_at', [$start, $end])->delete();
            }

            $importacion->estado = 'revertido';
            $importacion->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Importación revertida exitosamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error revertir importacion: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al revertir: ' . $e->getMessage()], 500);
        }
    }

    private function getMappedValue($row, $mapping, $key) {
        $col = $mapping[$key] ?? null;
        return $col !== null && $col !== '' ? trim($row[$col] ?? '') : null;
    }

    private function parseDate($dateStr)
    {
        if (empty($dateStr)) return '2000-01-01';
        
        $dateStr = trim($dateStr);
        
        // Formato Y-m-d
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            return $dateStr;
        }

        // Formato d/m/Y o d-m-Y
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $dateStr, $matches)) {
            return sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
        }

        try {
            return \Carbon\Carbon::parse($dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return '2000-01-01';
        }
    }

    private function formatTelefono($telefono)
    {
        if (empty($telefono)) return null;
        $numeros = preg_replace('/[^0-9]/', '', $telefono);
        return substr($numeros, 0, 9) ?: null;
    }

    private function separarNombresCompletos($nombreCompleto)
    {
        $nombreCompleto = trim(str_replace('  ', ' ', $nombreCompleto));
        $res = [
            'apellido_paterno' => '',
            'apellido_materno' => '',
            'nombres' => ''
        ];
        
        if (empty($nombreCompleto)) return $res;

        if (str_contains($nombreCompleto, ',')) {
            $parts = explode(',', $nombreCompleto);
            $apellidos = trim($parts[0]);
            $res['nombres'] = trim($parts[1] ?? '');
            
            $apParts = explode(' ', $apellidos);
            if (count($apParts) >= 2) {
                $res['apellido_paterno'] = array_shift($apParts);
                $res['apellido_materno'] = implode(' ', $apParts);
            } else {
                $res['apellido_paterno'] = $apellidos;
            }
        } else {
            $parts = explode(' ', $nombreCompleto);
            if (count($parts) >= 3) {
                $res['apellido_paterno'] = array_shift($parts);
                $res['apellido_materno'] = array_shift($parts);
                $res['nombres'] = implode(' ', $parts);
            } elseif (count($parts) == 2) {
                $res['apellido_paterno'] = $parts[0];
                $res['nombres'] = $parts[1];
            } else {
                $res['nombres'] = $nombreCompleto;
            }
        }
        return $res;
    }

    private function importarDirectorio($row, $mapping, $request, $roleEstudiante, $gradoSeccion = null, $anoActivo = null)
    {
        $dniEstudiante = $this->getMappedValue($row, $mapping, 'estudiante_dni');
        if (!$dniEstudiante) throw new \Exception('DNI de estudiante requerido.');

        $nombresCompletos = $this->getMappedValue($row, $mapping, 'estudiante_nombres_completos');
        $partesEst = $this->separarNombresCompletos($nombresCompletos);

        $nivel = strtolower($request->nivel_id);

        $user = User::firstOrCreate(
            ['dni' => $dniEstudiante],
            [
                'name' => trim($partesEst['nombres'] . ' ' . $partesEst['apellido_paterno'] . ' ' . $partesEst['apellido_materno']),
                'email' => $dniEstudiante,
                'password' => Hash::make($dniEstudiante),
                'role_id' => $roleEstudiante->id,
                'must_change_password' => true,
            ]
        );

        $datosEstudiante = [
            'user_id' => $user->id,
            'codigo_estudiante' => $this->getMappedValue($row, $mapping, 'estudiante_codigo') ?: null,
            'nombres' => $partesEst['nombres'],
            'apellido_paterno' => $partesEst['apellido_paterno'],
            'apellido_materno' => $partesEst['apellido_materno'],
            'fecha_nacimiento' => $this->parseDate($this->getMappedValue($row, $mapping, 'estudiante_fecha_nacimiento')),
            'sexo' => 'M', // Default required by DB
            'nivel' => $nivel,
            'estado' => 'ACTIVO'
        ];

        // Añadir datos de padres si es Primaria
        $datosPadre = null;
        $datosMadre = null;
        if ($nivel === 'primaria') {
            $datosPadre = [
                'nombres' => $this->getMappedValue($row, $mapping, 'padre_nombres'),
                'dni' => $this->getMappedValue($row, $mapping, 'padre_dni'),
                'telefono' => $this->formatTelefono($this->getMappedValue($row, $mapping, 'padre_telefono'))
            ];
            $datosMadre = [
                'nombres' => $this->getMappedValue($row, $mapping, 'madre_nombres'),
                'dni' => $this->getMappedValue($row, $mapping, 'madre_dni'),
                'telefono' => $this->formatTelefono($this->getMappedValue($row, $mapping, 'madre_telefono'))
            ];
        }

        $estudiante = Estudiante::updateOrCreate(
            ['dni' => $dniEstudiante],
            $datosEstudiante
        );

        if ($datosPadre && ($datosPadre['nombres'] || $datosPadre['dni'])) {
            $partesPadre = $this->separarNombresCompletos($datosPadre['nombres'] ?: '');
            Apoderado::updateOrCreate(
                ['estudiante_dni' => $dniEstudiante, 'parentesco' => 'PADRE'],
                [
                    'dni' => $datosPadre['dni'],
                    'nombres' => $partesPadre['nombres'],
                    'apellido_paterno' => $partesPadre['apellido_paterno'],
                    'apellido_materno' => $partesPadre['apellido_materno'],
                    'telefono' => $datosPadre['telefono']
                ]
            );
        }

        if ($datosMadre && ($datosMadre['nombres'] || $datosMadre['dni'])) {
            $partesMadre = $this->separarNombresCompletos($datosMadre['nombres'] ?: '');
            Apoderado::updateOrCreate(
                ['estudiante_dni' => $dniEstudiante, 'parentesco' => 'MADRE'],
                [
                    'dni' => $datosMadre['dni'],
                    'nombres' => $partesMadre['nombres'],
                    'apellido_paterno' => $partesMadre['apellido_paterno'],
                    'apellido_materno' => $partesMadre['apellido_materno'],
                    'telefono' => $datosMadre['telefono']
                ]
            );
        }

        // Matricular al estudiante si se eligió un grado/sección
        if ($gradoSeccion && $anoActivo) {
            \App\Models\Matricula::updateOrCreate(
                [
                    'estudiante_id' => $estudiante->id,
                    'ano_lectivo_id' => $anoActivo->id
                ],
                [
                    'grado_seccion_id' => $gradoSeccion->id,
                    'estado' => 'matriculado'
                ]
            );
        }

        // Guardar Apoderado
        $apoderado_tipo = $request->apoderado_tipo ?? 'otro';
        $dniApoderado = null;
        $nomCompApo = '';
        $parentesco = 'MADRE'; // Default
        
        if ($nivel === 'primaria') {
            if ($apoderado_tipo === 'padre') {
                $dniApoderado = $datosPadre['dni'] ?? null;
                $nomCompApo = $datosPadre['nombres'] ?? '';
                $telefonoApoderado = $datosPadre['telefono'] ?? null;
                $parentesco = 'PADRE';
            } elseif ($apoderado_tipo === 'madre') {
                $dniApoderado = $datosMadre['dni'] ?? null;
                $nomCompApo = $datosMadre['nombres'] ?? '';
                $telefonoApoderado = $datosMadre['telefono'] ?? null;
                $parentesco = 'MADRE';
            } else {
                $dniApoderado = $this->getMappedValue($row, $mapping, 'apoderado_dni');
                $nomCompApo = $this->getMappedValue($row, $mapping, 'apoderado_nombres') ?: '';
                $telefonoApoderado = $this->formatTelefono($this->getMappedValue($row, $mapping, 'telefono'));
                $parentesco = $this->getMappedValue($row, $mapping, 'apoderado_parentesco') ?: 'OTRO';
            }
        } else {
            $dniApoderado = $this->getMappedValue($row, $mapping, 'apoderado_dni');
            $nomCompApo = $this->getMappedValue($row, $mapping, 'apoderado_nombres_completos') ?: '';
            $telefonoApoderado = $this->formatTelefono($this->getMappedValue($row, $mapping, 'telefono'));
            $parentesco = $this->getMappedValue($row, $mapping, 'apoderado_parentesco') ?: 'MADRE';
        }

        if ($dniApoderado) {
            $partesApo = $this->separarNombresCompletos($nomCompApo);

            Apoderado::where('estudiante_dni', $dniEstudiante)->update(['es_apoderado' => false]);

            Apoderado::updateOrCreate(
                ['estudiante_dni' => $dniEstudiante, 'dni' => $dniApoderado],
                [
                    'nombres' => $partesApo['nombres'],
                    'apellido_paterno' => $partesApo['apellido_paterno'],
                    'apellido_materno' => $partesApo['apellido_materno'],
                    'telefono' => $telefonoApoderado,
                    'direccion' => $this->getMappedValue($row, $mapping, 'direccion'),
                    'parentesco' => $parentesco,
                    'es_apoderado' => true
                ]
            );
        }
    }

    private function importarEstudiantes($row, $mapping, $request, $roleEstudiante, $gradoSeccion = null, $anoActivo = null)
    {
        $dniEstudiante = $this->getMappedValue($row, $mapping, 'estudiante_dni');
        if (!$dniEstudiante) throw new \Exception('DNI de estudiante requerido.');

        $sexoMap = mb_strtoupper(trim($this->getMappedValue($row, $mapping, 'estudiante_sexo') ?: ''));
        $sexo = 'M';
        if ($sexoMap === 'MUJER' || $sexoMap === 'F' || $sexoMap === 'FEMENINO' || str_contains($sexoMap, 'MUJ') || str_contains($sexoMap, 'FEM')) {
            $sexo = 'F';
        }

        $nombres = $this->getMappedValue($row, $mapping, 'estudiante_nombres');
        $apPaterno = $this->getMappedValue($row, $mapping, 'estudiante_apellido_paterno');
        $apMaterno = $this->getMappedValue($row, $mapping, 'estudiante_apellido_materno');

        $user = User::firstOrCreate(
            ['dni' => $dniEstudiante],
            [
                'name' => trim($nombres . ' ' . $apPaterno . ' ' . $apMaterno),
                'email' => $dniEstudiante,
                'password' => Hash::make($dniEstudiante),
                'role_id' => $roleEstudiante->id,
                'must_change_password' => true,
            ]
        );

        $estudiante = Estudiante::updateOrCreate(
            ['dni' => $dniEstudiante],
            [
                'user_id' => $user->id,
                'codigo_estudiante' => $this->getMappedValue($row, $mapping, 'estudiante_codigo') ?: null,
                'nombres' => $nombres,
                'apellido_paterno' => $apPaterno,
                'apellido_materno' => $apMaterno,
                'fecha_nacimiento' => $this->parseDate($this->getMappedValue($row, $mapping, 'estudiante_fecha_nacimiento')),
                'sexo' => $sexo,
                'nivel' => strtolower($request->nivel_id),
                'estado' => 'ACTIVO'
            ]
        );

        if (!$gradoSeccion && $anoActivo) {
            $gradoNombre = mb_strtoupper(trim($this->getMappedValue($row, $mapping, 'estudiante_grado') ?: ''));
            $seccionNombre = mb_strtoupper(trim($this->getMappedValue($row, $mapping, 'estudiante_seccion') ?: ''));
            
            if ($gradoNombre && $seccionNombre) {
                $ordenMap = [
                    'PRIMERO' => 1, 'PRIMER' => 1, '1' => 1, '1RO' => 1, '1ERO' => 1,
                    'SEGUNDO' => 2, '2' => 2, '2DO' => 2,
                    'TERCERO' => 3, 'TERCER' => 3, '3' => 3, '3RO' => 3, '3ERO' => 3,
                    'CUARTO' => 4, '4' => 4, '4TO' => 4,
                    'QUINTO' => 5, '5' => 5, '5TO' => 5,
                    'SEXTO' => 6, '6' => 6, '6TO' => 6,
                ];
                $orden = $ordenMap[$gradoNombre] ?? null;
                
                if ($orden) {
                    $gradoObj = \App\Models\Grado::where('nivel', $request->nivel_id)->where('orden', $orden)->first();
                    $seccionObj = \App\Models\Seccion::where('nombre', $seccionNombre)->first();
                    
                    if ($gradoObj && $seccionObj) {
                        $gradoSeccion = \App\Models\GradoSeccion::where('grado_id', $gradoObj->id)
                            ->where('seccion_id', $seccionObj->id)
                            ->where('ano_lectivo_id', $anoActivo->id)
                            ->first();
                    }
                }
            }
        }

        if ($gradoSeccion && $anoActivo) {
            \App\Models\Matricula::updateOrCreate(
                [
                    'estudiante_id' => $estudiante->id,
                    'ano_lectivo_id' => $anoActivo->id
                ],
                [
                    'grado_seccion_id' => $gradoSeccion->id,
                    'estado' => 'matriculado'
                ]
            );
        }
    }

    private function importarSiagie($row, $mapping, $request)
    {
        $codigoSiagie = $this->getMappedValue($row, $mapping, 'codigo_siagie');
        $nombresApe = $this->getMappedValue($row, $mapping, 'nombres_apellidos');

        if (!$codigoSiagie || !$nombresApe) throw new \Exception('Faltan datos de código SIAGIE o Nombres.');

        $partes = $this->separarNombresCompletos($nombresApe);
        
        $estudiante = Estudiante::where('apellido_paterno', $partes['apellido_paterno'])
            ->where('nombres', $partes['nombres'])
            ->first();
            
        if ($estudiante) {
            $estudiante->update(['codigo_estudiante' => $codigoSiagie]);
        } else {
            throw new \Exception('Estudiante no encontrado en BD para asignarle el código.');
        }
    }
    private function importarPadres($row, $mapping, $request, $roleEstudiante = null, $gradoSeccion = null, $anoActivo = null)
    {
        $dniEstudiante = $this->getMappedValue($row, $mapping, 'estudiante_dni');
        if (!$dniEstudiante) throw new \Exception('DNI de estudiante requerido.');

        $fechaRaw = $this->getMappedValue($row, $mapping, 'estudiante_fecha_nacimiento');
        $fechaNacimiento = $fechaRaw ? $this->parseDate($fechaRaw) : '2000-01-01';

        $creadoNuevo = false;
        $estudiante = Estudiante::where('dni', $dniEstudiante)->first();
        if (!$estudiante) {
            $creadoNuevo = true;
            $nombresCompletos = $this->getMappedValue($row, $mapping, 'estudiante_nombres_apellidos');
            if (!$nombresCompletos) throw new \Exception('Estudiante con DNI ' . $dniEstudiante . ' no encontrado y no se proporcionaron sus Apellidos y Nombres para crearlo.');

            $partesEst = $this->separarNombresCompletos($nombresCompletos);
            
            $user = User::firstOrCreate(
                ['dni' => $dniEstudiante],
                [
                    'name' => trim($partesEst['nombres'] . ' ' . $partesEst['apellido_paterno'] . ' ' . $partesEst['apellido_materno']),
                    'email' => $dniEstudiante,
                    'password' => Hash::make($dniEstudiante),
                    'role_id' => $roleEstudiante->id ?? 3, // Assuming 3 is student role if not passed
                    'must_change_password' => true,
                ]
            );

            $estudiante = Estudiante::create([
                'dni' => $dniEstudiante,
                'user_id' => $user->id,
                'nombres' => $partesEst['nombres'],
                'apellido_paterno' => $partesEst['apellido_paterno'],
                'apellido_materno' => $partesEst['apellido_materno'],
                'fecha_nacimiento' => $fechaNacimiento,
                'sexo' => 'M', // default
                'nivel' => strtolower($request->nivel_id),
                'estado' => 'ACTIVO'
            ]);

            if ($gradoSeccion && $anoActivo) {
                \App\Models\Matricula::updateOrCreate(
                    [
                        'estudiante_id' => $estudiante->id,
                        'ano_lectivo_id' => $anoActivo->id
                    ],
                    [
                        'grado_seccion_id' => $gradoSeccion->id,
                        'estado' => 'matriculado'
                    ]
                );
            }
        }

        $datosActualizar = [];

        if ($fechaRaw !== null && $fechaRaw !== '') {
            $datosActualizar['fecha_nacimiento'] = $fechaNacimiento;
        }

        $colegioInicial = $this->getMappedValue($row, $mapping, 'colegio_inicial');
        if ($colegioInicial !== null && $colegioInicial !== '') $datosActualizar['colegio_inicial'] = mb_strtoupper($colegioInicial);

        if (!empty($datosActualizar)) {
            $estudiante->update($datosActualizar);
        }

        $padreDni = $this->getMappedValue($row, $mapping, 'padre_dni');
        $padreNombres = $this->getMappedValue($row, $mapping, 'padre_nombres');
        if ($padreDni || $padreNombres) {
            $padreData = ['parentesco' => 'PADRE'];
            if ($padreDni) $padreData['dni'] = $padreDni;
            
            $partesPadre = $this->separarNombresCompletos($padreNombres ?: '');
            Apoderado::updateOrCreate(
                ['estudiante_dni' => $dniEstudiante, 'parentesco' => 'PADRE'],
                array_merge($padreData, [
                    'nombres' => mb_strtoupper($partesPadre['nombres']),
                    'apellido_paterno' => mb_strtoupper($partesPadre['apellido_paterno']),
                    'apellido_materno' => mb_strtoupper($partesPadre['apellido_materno']),
                    'telefono' => $this->formatTelefono($this->getMappedValue($row, $mapping, 'padre_telefono')),
                ])
            );
        }

        $madreDni = $this->getMappedValue($row, $mapping, 'madre_dni');
        $madreNombres = $this->getMappedValue($row, $mapping, 'madre_nombres');
        if ($madreDni || $madreNombres) {
            $madreData = ['parentesco' => 'MADRE'];
            if ($madreDni) $madreData['dni'] = $madreDni;
            
            $partesMadre = $this->separarNombresCompletos($madreNombres ?: '');
            Apoderado::updateOrCreate(
                ['estudiante_dni' => $dniEstudiante, 'parentesco' => 'MADRE'],
                array_merge($madreData, [
                    'nombres' => mb_strtoupper($partesMadre['nombres']),
                    'apellido_paterno' => mb_strtoupper($partesMadre['apellido_paterno']),
                    'apellido_materno' => mb_strtoupper($partesMadre['apellido_materno']),
                    'telefono' => $this->formatTelefono($this->getMappedValue($row, $mapping, 'madre_telefono')),
                ])
            );
        }

        if (!empty($datosActualizar)) {
            $estudiante->update($datosActualizar);
        }

        $apoderadoNombres = $this->getMappedValue($row, $mapping, 'apoderado_nombres');
        $apoderadoDni = $this->getMappedValue($row, $mapping, 'apoderado_dni');
        $apoderadoTelefono = $this->formatTelefono($this->getMappedValue($row, $mapping, 'apoderado_telefono'));
        $apoderadoDireccion = $this->getMappedValue($row, $mapping, 'apoderado_direccion');
        $apoderadoParentesco = $this->getMappedValue($row, $mapping, 'apoderado_parentesco');

        if ($apoderadoDni) {
            if ($padreDni && $apoderadoDni === $padreDni) {
                $apoderadoParentesco = 'PADRE';
            } elseif ($madreDni && $apoderadoDni === $madreDni) {
                $apoderadoParentesco = 'MADRE';
            }
            
            $partesApo = $this->separarNombresCompletos($apoderadoNombres ?: '');
            
            \App\Models\Apoderado::where('estudiante_dni', $dniEstudiante)->update(['es_apoderado' => false]);
            
            \App\Models\Apoderado::updateOrCreate(
                ['estudiante_dni' => $dniEstudiante, 'dni' => $apoderadoDni],
                [
                    'nombres' => $partesApo['nombres'],
                    'apellido_paterno' => $partesApo['apellido_paterno'],
                    'apellido_materno' => $partesApo['apellido_materno'],
                    'telefono' => $apoderadoTelefono,
                    'direccion' => $apoderadoDireccion,
                    'parentesco' => mb_strtoupper($apoderadoParentesco ?: 'OTRO'),
                    'es_apoderado' => true
                ]
            );
        }

        return $creadoNuevo;
    }
}
