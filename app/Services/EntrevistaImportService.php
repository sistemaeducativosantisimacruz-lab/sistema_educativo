<?php

namespace App\Services;

use App\Models\Apoderado;
use App\Models\EntrevistaAdmision;
use App\Models\Estudiante;
use App\Models\GradoSeccion;
use App\Models\Matricula;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EntrevistaImportService
{
    /**
     * Procesa y extrae la información de una ficha de entrevista en Excel.
     *
     * @param UploadedFile $file
     * @param int $docenteId
     * @param int $anoPostula
     * @return array
     * @throws \Exception
     */
    public function importarFicha(UploadedFile $file, int $docenteId, ?int $anoPostula = null): array
    {
        if (!$anoPostula) {
            $anoPostula = \App\Models\AnoLectivo::where('activo', true)->value('anio') ?? (int) date('Y');
        }

        $spreadsheet = IOFactory::load($file->getRealPath());
        $datos = $this->extraerDiccionarioDeFicha($spreadsheet);

        // Validar campo mínimo requerido: DNI del estudiante
        $dniEstudiante = preg_replace('/\D/', '', $datos['est_dni'] ?? '');
        if (strlen($dniEstudiante) < 8) {
            throw new \Exception("La ficha no contiene un D.N.I. de estudiante válido (se encontraron menos de 8 dígitos).");
        }

        $nombreCompleto = trim($datos['est_nombres'] ?? '');
        if (empty($nombreCompleto)) {
            throw new \Exception("El nombre y apellidos del estudiante es obligatorio en la ficha.");
        }

        // Separar nombres y apellidos
        [$nombres, $paterno, $materno] = $this->separarNombresApellidos($nombreCompleto);

        // Normalizar fecha de nacimiento
        $fechaNacimiento = $this->parsearFecha($datos['est_fecha_nacimiento'] ?? null);

        // Normalizar fecha de entrevista
        $fechaEntrevista = $this->parsearFecha($datos['fecha_entrevista'] ?? null) ?? now()->toDateString();

        // Determinar grado y nivel
        $gradoPostula = trim($datos['grado'] ?? '');
        $esSecundaria = (stripos($gradoPostula, 'secundaria') !== false) || isset($datos['sacramento_confirmacion']);

        if ($esSecundaria && !str_contains(strtolower($gradoPostula), 'secundaria')) {
            $gradoPostula = !empty($gradoPostula) ? "{$gradoPostula} Secundaria" : "1° Secundaria";
        } elseif (!$esSecundaria && !str_contains(strtolower($gradoPostula), 'primaria')) {
            $gradoPostula = !empty($gradoPostula) ? "{$gradoPostula} Primaria" : "1° Primaria";
        }

        $nivel = $esSecundaria ? 'secundaria' : 'primaria';

        // Conformidad
        $conformidadRaw = strtoupper(trim($datos['conformidad'] ?? 'CONFORME'));
        $resultadoConformidad = (str_contains($conformidadRaw, 'NO')) ? 'NO CONFORME' : 'CONFORME';

        // Recopilar respuestas completas del cuestionario (excluyendo datos básicos de personas)
        $excluidos = [
            'grado', 'est_nombres', 'est_fecha_nacimiento', 'est_dni',
            'padre_nombres', 'padre_dni', 'padre_grado_estudios', 'padre_ocupacion', 'padre_domicilio', 'padre_telefono',
            'madre_nombres', 'madre_dni', 'madre_grado_estudios', 'madre_ocupacion', 'madre_domicilio', 'madre_telefono',
            'apoderado_nombres', 'apoderado_dni', 'apoderado_grado_estudios', 'apoderado_ocupacion', 'apoderado_domicilio', 'apoderado_telefono',
            'conformidad', 'fecha_entrevista'
        ];
        $cuestionario = array_diff_key($datos, array_flip($excluidos));

        // Guardar o actualizar registro de entrevista
        $entrevista = EntrevistaAdmision::updateOrCreate(
            [
                'estudiante_dni' => $dniEstudiante,
                'ano_lectivo_postula' => $anoPostula,
            ],
            [
                'nivel' => $nivel,
                'grado_postula' => $gradoPostula,
                'estudiante_nombres' => mb_strtoupper($nombres),
                'estudiante_apellido_paterno' => mb_strtoupper($paterno),
                'estudiante_apellido_materno' => mb_strtoupper($materno),
                'estudiante_fecha_nacimiento' => $fechaNacimiento,
                'colegio_procedencia' => $datos['colegio_inicial'] ?? ($datos['vive_con'] ?? null),
                
                // Padre
                'padre_dni' => preg_replace('/\D/', '', $datos['padre_dni'] ?? '') ?: null,
                'padre_nombres' => mb_strtoupper(trim($datos['padre_nombres'] ?? '')),
                'padre_telefono' => trim($datos['padre_telefono'] ?? ''),
                'padre_ocupacion' => mb_strtoupper(trim($datos['padre_ocupacion'] ?? '')),
                'padre_grado_estudios' => trim($datos['padre_grado_estudios'] ?? ''),
                'padre_domicilio' => trim($datos['padre_domicilio'] ?? ''),

                // Madre
                'madre_dni' => preg_replace('/\D/', '', $datos['madre_dni'] ?? '') ?: null,
                'madre_nombres' => mb_strtoupper(trim($datos['madre_nombres'] ?? '')),
                'madre_telefono' => trim($datos['madre_telefono'] ?? ''),
                'madre_ocupacion' => mb_strtoupper(trim($datos['madre_ocupacion'] ?? '')),
                'madre_grado_estudios' => trim($datos['madre_grado_estudios'] ?? ''),
                'madre_domicilio' => trim($datos['madre_domicilio'] ?? ''),

                // Apoderado
                'apoderado_dni' => preg_replace('/\D/', '', $datos['apoderado_dni'] ?? '') ?: null,
                'apoderado_nombres' => mb_strtoupper(trim($datos['apoderado_nombres'] ?? '')),
                'apoderado_telefono' => trim($datos['apoderado_telefono'] ?? ''),
                'apoderado_ocupacion' => mb_strtoupper(trim($datos['apoderado_ocupacion'] ?? '')),
                'apoderado_grado_estudios' => trim($datos['apoderado_grado_estudios'] ?? ''),
                'apoderado_domicilio' => trim($datos['apoderado_domicilio'] ?? ''),
                'apoderado_parentesco' => mb_strtoupper(trim($datos['patria_potestad'] ?? 'APODERADO')),

                'respuestas_cuestionario' => $cuestionario,
                'docente_entrevistador_id' => $docenteId,
                'fecha_entrevista' => $fechaEntrevista,
                'resultado_conformidad' => $resultadoConformidad,
                'estado' => 'nuevo_ingresante',
                'estado_entrevista' => 'completada',
                'archivo_excel_origen' => $file->getClientOriginalName(),
            ]
        );

        return [
            'id' => $entrevista->id,
            'dni' => $dniEstudiante,
            'estudiante' => $entrevista->nombre_completo_estudiante,
            'grado_postula' => $gradoPostula,
            'conformidad' => $resultadoConformidad,
        ];
    }

    /**
     * Mapeo oficial de fila de la hoja Ficha a código de campo para PRIMARIA.
     */
    public static array $mapaFilasFichaPrimaria = [
        7 => 'grado',
        9 => 'est_nombres',
        10 => 'est_fecha_nacimiento',
        11 => 'est_dni',
        12 => 'padre_nombres',
        13 => 'padre_dni',
        14 => 'padre_grado_estudios',
        15 => 'padre_ocupacion',
        16 => 'padre_domicilio',
        17 => 'padre_telefono',
        18 => 'madre_nombres',
        19 => 'madre_dni',
        20 => 'madre_grado_estudios',
        21 => 'madre_ocupacion',
        22 => 'madre_domicilio',
        23 => 'madre_telefono',
        24 => 'apoderado_nombres',
        25 => 'apoderado_dni',
        26 => 'apoderado_grado_estudios',
        27 => 'apoderado_ocupacion',
        28 => 'apoderado_domicilio',
        29 => 'apoderado_telefono',
        31 => 'num_hermanos',
        32 => 'vive_con',
        33 => 'hermanos_en_ie',
        34 => 'hermanos_en_ie_cantidad',
        35 => 'hermanos_en_ie_nombres',
        36 => 'acuerdo_comunion',
        38 => 'parroquia',
        39 => 'comunidad_zonal',
        40 => 'sacramento_bautizo',
        41 => 'sacramento_eucaristia',
        42 => 'eucaristia_importante',
        43 => 'eucaristia_por_que',
        44 => 'eucaristia_dia',
        46 => 'padres_casados',
        47 => 'estado_conyugal',
        48 => 'enfermedad',
        49 => 'conoce_diagnostico',
        50 => 'diagnostico_cual',
        52 => 'patria_potestad',
        53 => 'persona_dispuesta',
        56 => 'oblig_reuniones_aula',
        57 => 'oblig_atencion_padres',
        58 => 'oblig_escuela_padres',
        59 => 'oblig_eucaristia',
        60 => 'oblig_conservacion',
        61 => 'oblig_multa_apafa',
        63 => 'norma_horario',
        64 => 'norma_corte_cabello',
        65 => 'norma_cabello_trenzado',
        66 => 'norma_uniforme',
        67 => 'norma_asistencia',
        69 => 'compromiso_pago',
        71 => 'autoriza_comunicacion',
        72 => 'autoriza_fotos_videos',
        73 => 'autoriza_actividades',
        74 => 'autoriza_atencion_medica',
        75 => 'autoriza_regresa_solo',
        76 => 'recoge_nombre',
        77 => 'recoge_dni',
        78 => 'acuerdo_integracion_aulas',
        80 => 'conformidad',
        81 => 'fecha_entrevista',
    ];

    /**
     * Mapeo oficial de fila de la hoja Ficha a código de campo para SECUNDARIA.
     */
    public static array $mapaFilasFichaSecundaria = [
        7 => 'grado',
        9 => 'est_nombres',
        10 => 'est_fecha_nacimiento',
        11 => 'est_dni',
        12 => 'padre_nombres',
        13 => 'padre_dni',
        14 => 'padre_grado_estudios',
        15 => 'padre_ocupacion',
        16 => 'padre_domicilio',
        17 => 'padre_telefono',
        18 => 'madre_nombres',
        19 => 'madre_dni',
        20 => 'madre_grado_estudios',
        21 => 'madre_ocupacion',
        22 => 'madre_domicilio',
        23 => 'madre_telefono',
        24 => 'apoderado_nombres',
        25 => 'apoderado_dni',
        26 => 'apoderado_grado_estudios',
        27 => 'apoderado_ocupacion',
        28 => 'apoderado_domicilio',
        29 => 'apoderado_telefono',
        31 => 'num_hermanos',
        32 => 'vive_con',
        33 => 'hermanos_en_ie',
        34 => 'hermanos_en_ie_cantidad',
        35 => 'hermanos_en_ie_nombres',
        36 => 'acuerdo_comunion',
        38 => 'parroquia',
        39 => 'comunidad_zonal',
        40 => 'sacramento_bautizo',
        41 => 'sacramento_eucaristia',
        42 => 'sacramento_confirmacion',
        43 => 'eucaristia_importante',
        44 => 'eucaristia_por_que',
        45 => 'eucaristia_dia',
        47 => 'padres_casados',
        48 => 'estado_conyugal',
        49 => 'enfermedad',
        50 => 'conoce_diagnostico',
        51 => 'diagnostico_cual',
        53 => 'patria_potestad',
        54 => 'persona_dispuesta',
        57 => 'oblig_reuniones_aula',
        58 => 'oblig_atencion_padres',
        59 => 'oblig_escuela_padres',
        60 => 'oblig_conservacion',
        61 => 'oblig_actividades_ie',
        62 => 'oblig_multa_apafa',
        64 => 'norma_horario',
        65 => 'norma_corte_cabello',
        66 => 'norma_cabello_trenzado',
        67 => 'norma_uniforme',
        68 => 'norma_asistencia',
        70 => 'compromiso_pago',
        72 => 'autoriza_comunicacion',
        73 => 'autoriza_fotos_videos',
        74 => 'autoriza_actividades',
        75 => 'autoriza_atencion_medica',
        76 => 'autoriza_regresa_solo',
        77 => 'recoge_nombre',
        78 => 'recoge_dni',
        79 => 'acuerdo_integracion_aulas',
        81 => 'conformidad',
        82 => 'fecha_entrevista',
    ];

    /**
     * Extrae el diccionario [codigo => respuesta] del libro de Excel.
     * Soporta lectura directa por número de fila de la hoja «Ficha» (tanto Primaria como Secundaria),
     * hoja de exportación horizontal, y columna F de archivos de trabajo.
     */
    protected function extraerDiccionarioDeFicha(Spreadsheet $spreadsheet): array
    {
        $diccionario = [];

        // Estrategia 1: Lectura directa de la hoja «Ficha» por número de fila según nivel
        $sheetFicha = $spreadsheet->getSheetByName('Ficha') ?? $spreadsheet->getActiveSheet();
        if ($sheetFicha) {
            $posibleDni = preg_replace('/\D/', '', (string)$sheetFicha->getCell('C11')->getValue());
            $posibleNombre = trim((string)$sheetFicha->getCell('C9')->getValue());

            if (strlen($posibleDni) >= 8 || !empty($posibleNombre)) {
                // Detectar si la ficha corresponde a secundaria
                $textoEncabezado = (string)$sheetFicha->getCell('A1')->getValue() . ' ' . (string)$sheetFicha->getCell('B7')->getValue();
                $esSecundaria = (stripos($textoEncabezado, 'secundaria') !== false);

                $mapaFilas = $esSecundaria ? self::$mapaFilasFichaSecundaria : self::$mapaFilasFichaPrimaria;

                foreach ($mapaFilas as $fila => $codigo) {
                    $val = $sheetFicha->getCell("C{$fila}")->getValue();
                    if (is_string($val) && str_starts_with($val, '=')) {
                        $val = $sheetFicha->getCell("C{$fila}")->getOldCalculatedValue() ?? '';
                    }
                    $diccionario[$codigo] = is_string($val) ? trim($val) : $val;
                }

                if (!empty($diccionario['est_dni']) || !empty($diccionario['est_nombres'])) {
                    return $diccionario;
                }
            }
        }

        // Estrategia 2: Hoja "Exportar_Ficha", "Exportación" o similar
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheetName = strtolower(trim($sheet->getTitle()));
            if (str_contains($sheetName, 'export') || str_contains($sheetName, 'respuest')) {
                $maxCol = $sheet->getHighestColumn();
                $row1 = $sheet->rangeToArray("A1:{$maxCol}1")[0] ?? [];
                $row2 = $sheet->rangeToArray("A2:{$maxCol}2")[0] ?? [];
                if (!empty($row1) && !empty($row2)) {
                    foreach ($row1 as $idx => $code) {
                        $code = trim((string) $code);
                        if (!empty($code)) {
                            $diccionario[$code] = $row2[$idx] ?? '';
                        }
                    }
                    if (isset($diccionario['est_dni']) || isset($diccionario['est_nombres'])) {
                        return $diccionario;
                    }
                }
            }
        }

        // Estrategia 3: Columna F si el archivo proviene de una versión anterior con códigos visibles
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        for ($r = 1; $r <= $highestRow; $r++) {
            $codigo = trim((string) $sheet->getCell("F{$r}")->getValue());
            $respuesta = $sheet->getCell("C{$r}")->getValue();

            if (is_string($respuesta) && str_starts_with($respuesta, '=')) {
                $respuesta = $sheet->getCell("C{$r}")->getOldCalculatedValue() ?? '';
            }

            if (!empty($codigo) && $codigo !== 'Código' && !str_starts_with($codigo, 'ENCABEZADO')) {
                $diccionario[$codigo] = is_string($respuesta) ? trim($respuesta) : $respuesta;
            }
        }

        return $diccionario;
    }

    /**
     * Separa un string de nombre completo en [nombres, apellido_paterno, apellido_materno].
     */
    public function separarNombresApellidos(string $completo): array
    {
        $completo = trim($completo);

        // Si viene con formato "Apellidos, Nombres"
        if (str_contains($completo, ',')) {
            $partes = explode(',', $completo, 2);
            $apellidos = trim($partes[0]);
            $nombres = trim($partes[1]);
            $apWords = explode(' ', preg_replace('/\s+/', ' ', $apellidos));
            $paterno = $apWords[0] ?? '';
            $materno = isset($apWords[1]) ? implode(' ', array_slice($apWords, 1)) : '';
            return [$nombres, $paterno, $materno];
        }

        // Formato natural "Nombres ApellidoPaterno ApellidoMaterno"
        $words = explode(' ', preg_replace('/\s+/', ' ', $completo));
        $count = count($words);

        if ($count === 1) {
            return [$words[0], '', ''];
        }
        if ($count === 2) {
            return [$words[0], $words[1], ''];
        }
        if ($count === 3) {
            return [$words[0], $words[1], $words[2]];
        }

        // 4 o más palabras: los dos últimos son paterno y materno
        $materno = array_pop($words);
        $paterno = array_pop($words);
        $nombres = implode(' ', $words);

        return [$nombres, $paterno, $materno];
    }

    /**
     * Parsea fechas en distintos formatos comunes de Excel.
     */
    protected function parsearFecha($valor): ?string
    {
        if (empty($valor)) {
            return null;
        }

        // Si viene como número serial de fecha de Excel
        if (is_numeric($valor) && $valor > 1000) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($valor))->toDateString();
            } catch (\Exception) {}
        }

        $str = trim((string) $valor);
        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'Y/m/d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $str)->toDateString();
            } catch (\Exception) {}
        }

        return null;
    }

    /**
     * Asigna sección y formaliza la matrícula de un postulante en una sola transacción segura.
     *
     * @param EntrevistaAdmision $entrevista
     * @param int $gradoSeccionId
     * @param int $anoLectivoId
     * @return array
     * @throws \Exception
     */
    public function matricularPostulante(EntrevistaAdmision $entrevista, int $gradoSeccionId, int $anoLectivoId): array
    {
        if ($entrevista->estado === 'matriculado') {
            throw new \Exception("Este estudiante ya ha sido matriculado previamente.");
        }

        $gradoSeccion = GradoSeccion::with('grado', 'seccion')->findOrFail($gradoSeccionId);

        return DB::transaction(function () use ($entrevista, $gradoSeccion, $anoLectivoId) {
            $dni = $entrevista->estudiante_dni;

            // 1. Crear o recuperar usuario del estudiante
            $roleEstudianteId = Role::where('nombre', 'estudiante')->value('id');
            $user = User::firstOrCreate(
                ['dni' => $dni],
                [
                    'name' => "{$entrevista->estudiante_nombres} {$entrevista->estudiante_apellido_paterno}",
                    'email' => $dni . '@santacruz.edu.pe',
                    'password' => Hash::make($dni),
                    'role_id' => $roleEstudianteId,
                    'must_change_password' => true,
                ]
            );

            // 2. Crear o actualizar el registro en Estudiante
            $estudiante = Estudiante::updateOrCreate(
                ['dni' => $dni],
                [
                    'user_id' => $user->id,
                    'apellido_paterno' => $entrevista->estudiante_apellido_paterno ?: 'APELLIDO',
                    'apellido_materno' => $entrevista->estudiante_apellido_materno ?: '',
                    'nombres' => $entrevista->estudiante_nombres,
                    'fecha_nacimiento' => $entrevista->estudiante_fecha_nacimiento ?: now()->subYears(6)->toDateString(),
                    'sexo' => 'M', // Por defecto si no viene especificado
                    'nivel' => $gradoSeccion->grado->nivel,
                    'estado' => 'matriculado',
                    'colegio_inicial' => $entrevista->colegio_procedencia,
                ]
            );

            // 3. Crear Apoderados (Padre, Madre y Apoderado si aplica)
            if (!empty($entrevista->padre_dni) || !empty($entrevista->padre_nombres)) {
                Apoderado::updateOrCreate(
                    [
                        'estudiante_dni' => $dni,
                        'parentesco' => 'PADRE'
                    ],
                    [
                        'dni' => $entrevista->padre_dni,
                        'nombres' => $entrevista->padre_nombres,
                        'telefono' => $entrevista->padre_telefono,
                        'direccion' => $entrevista->padre_domicilio,
                        'es_apoderado' => false,
                    ]
                );
            }

            if (!empty($entrevista->madre_dni) || !empty($entrevista->madre_nombres)) {
                Apoderado::updateOrCreate(
                    [
                        'estudiante_dni' => $dni,
                        'parentesco' => 'MADRE'
                    ],
                    [
                        'dni' => $entrevista->madre_dni,
                        'nombres' => $entrevista->madre_nombres,
                        'telefono' => $entrevista->madre_telefono,
                        'direccion' => $entrevista->madre_domicilio,
                        'es_apoderado' => false,
                    ]
                );
            }

            // Registrar Apoderado titular
            $apoDni = $entrevista->apoderado_dni ?: ($entrevista->padre_dni ?: $entrevista->madre_dni);
            $apoNombres = $entrevista->apoderado_nombres ?: ($entrevista->padre_nombres ?: $entrevista->madre_nombres);

            if (!empty($apoDni) && !empty($apoNombres)) {
                Apoderado::updateOrCreate(
                    [
                        'estudiante_dni' => $dni,
                        'dni' => $apoDni,
                    ],
                    [
                        'nombres' => $apoNombres,
                        'telefono' => $entrevista->apoderado_telefono ?: ($entrevista->padre_telefono ?: $entrevista->madre_telefono),
                        'direccion' => $entrevista->apoderado_domicilio ?: ($entrevista->padre_domicilio ?: $entrevista->madre_domicilio),
                        'parentesco' => $entrevista->apoderado_parentesco ?: 'APODERADO',
                        'es_apoderado' => true,
                    ]
                );
            }

            // 4. Crear la Matrícula oficial en la sección
            $matricula = Matricula::updateOrCreate(
                [
                    'estudiante_id' => $estudiante->id,
                    'ano_lectivo_id' => $anoLectivoId,
                ],
                [
                    'grado_seccion_id' => $gradoSeccion->id,
                    'estado' => 'matriculado',
                    'tipo_matricula' => 'Regular',
                ]
            );

            // 5. Actualizar el estado de la ficha de entrevista
            $entrevista->update([
                'estado' => 'matriculado',
                'estudiante_id' => $estudiante->id,
                'matricula_id' => $matricula->id,
                'grado_seccion_asignada_id' => $gradoSeccion->id,
            ]);

            return [
                'estudiante_id' => $estudiante->id,
                'matricula_id' => $matricula->id,
                'seccion' => "{$gradoSeccion->grado->nombre} {$gradoSeccion->seccion->nombre}",
            ];
        });
    }

    /**
     * Búsqueda en vivo de estudiantes existentes (para reinscripción o hermanos en la IE).
     */
    public function buscarEstudiantes(string $query): array
    {
        $query = trim($query);
        if (strlen($query) < 2) {
            return [];
        }

        $likeOp = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $estudiantes = Estudiante::with([
            'familiares',
            'matriculas' => fn ($q) => $q->orderByDesc('id')->with('gradoSeccion.grado', 'gradoSeccion.seccion'),
        ])
        ->where(function ($q) use ($query, $likeOp) {
            $q->where('dni', 'like', "%{$query}%")
              ->orWhere('nombres', $likeOp, "%{$query}%")
              ->orWhere('apellido_paterno', $likeOp, "%{$query}%")
              ->orWhere('apellido_materno', $likeOp, "%{$query}%");
        })
        ->limit(10)
        ->get();

        return $estudiantes->map(function (Estudiante $estudiante) {
            $ultimaMatricula = $estudiante->matriculas->first();
            $ultimoGrado = $ultimaMatricula?->gradoSeccion?->grado;
            $siguienteGrado = null;
            if ($ultimoGrado) {
                $siguienteGrado = \App\Models\Grado::where('orden', '>', $ultimoGrado->orden)
                    ->orderBy('orden')
                    ->first();
            }

            // Familiares
            $padre = $estudiante->familiares->first(fn ($f) => in_array(strtoupper($f->parentesco ?? ''), ['PADRE', 'PAPA', 'PAPÁ']));
            $madre = $estudiante->familiares->first(fn ($f) => in_array(strtoupper($f->parentesco ?? ''), ['MADRE', 'MAMA', 'MAMÁ']));
            $apoderado = $estudiante->familiares->first(fn ($f) => $f->es_apoderado) ?? $estudiante->familiares->first();

            return [
                'id' => $estudiante->id,
                'dni' => $estudiante->dni,
                'nombres' => $estudiante->nombres,
                'apellido_paterno' => $estudiante->apellido_paterno,
                'apellido_materno' => $estudiante->apellido_materno,
                'nombre_completo' => trim("{$estudiante->apellido_paterno} {$estudiante->apellido_materno}, {$estudiante->nombres}"),
                'sexo' => $estudiante->sexo,
                'fecha_nacimiento' => $estudiante->fecha_nacimiento?->format('Y-m-d'),
                'nivel_actual' => $estudiante->nivel,
                'grado_actual' => $ultimoGrado ? "{$ultimoGrado->nombre} {$ultimaMatricula->gradoSeccion->seccion?->nombre}" : null,
                'grado_siguiente' => $siguienteGrado?->nombre ?? ($ultimoGrado?->nombre ?? '1° Primaria'),
                'nivel_siguiente' => $siguienteGrado?->nivel ?? ($estudiante->nivel ?? 'primaria'),
                'colegio_procedencia' => 'I.E.P. Santísima Cruz',
                'padre' => $padre ? [
                    'dni' => $padre->dni,
                    'nombres' => $padre->nombre_completo,
                    'telefono' => $padre->telefono,
                    'domicilio' => $padre->direccion,
                    'ocupacion' => '',
                    'grado_estudios' => '',
                ] : null,
                'madre' => $madre ? [
                    'dni' => $madre->dni,
                    'nombres' => $madre->nombre_completo,
                    'telefono' => $madre->telefono,
                    'domicilio' => $madre->direccion,
                    'ocupacion' => '',
                    'grado_estudios' => '',
                ] : null,
                'apoderado' => $apoderado ? [
                    'dni' => $apoderado->dni,
                    'nombres' => $apoderado->nombre_completo,
                    'telefono' => $apoderado->telefono,
                    'domicilio' => $apoderado->direccion,
                    'parentesco' => $apoderado->parentesco,
                    'ocupacion' => '',
                    'grado_estudios' => '',
                ] : null,
            ];
        })->toArray();
    }

    /**
     * Guarda el progreso o actualiza una ficha de entrevista completada en línea.
     */
    public function guardarEntrevistaEnLinea(array $datos, int $docenteId, ?int $entrevistaId = null, string $estadoEntrevista = 'en_progreso'): EntrevistaAdmision
    {
        $dniEstudiante = preg_replace('/\D/', '', $datos['estudiante_dni'] ?? ($datos['est_dni'] ?? ''));
        $anoPostula = (int) ($datos['ano_lectivo_postula'] ?? ($datos['ano_postula'] ?? 2026));
        $nivel = strtolower(trim($datos['nivel'] ?? 'primaria')) === 'secundaria' ? 'secundaria' : 'primaria';
        $gradoPostula = trim($datos['grado_postula'] ?? ($datos['grado'] ?? ($nivel === 'secundaria' ? '1° Secundaria' : '1° Primaria')));

        // Género y regla condicional
        $sexo = in_array(strtoupper(trim($datos['estudiante_sexo'] ?? '')), ['M', 'F']) ? strtoupper(trim($datos['estudiante_sexo'])) : null;

        // Separar nombres si no vienen separados
        $nombres = trim($datos['estudiante_nombres'] ?? '');
        $paterno = trim($datos['estudiante_apellido_paterno'] ?? '');
        $materno = trim($datos['estudiante_apellido_materno'] ?? '');
        if (empty($paterno) && empty($materno) && !empty($nombres)) {
            [$n, $p, $m] = $this->separarNombresApellidos($nombres);
            $nombres = $n;
            $paterno = $p;
            $materno = $m;
        }

        // Hermanos en la IE
        $hermanosDatos = is_array($datos['hermanos_datos'] ?? null) ? $datos['hermanos_datos'] : [];
        $tieneHermanosIE = ($datos['hermanos_en_ie'] ?? 'No') === 'Sí' || !empty($hermanosDatos);
        $cantHermanosIE = count($hermanosDatos);
        $nombresHermanosIE = !empty($hermanosDatos)
            ? implode(' ; ', array_map(fn ($h) => ($h['nombre'] ?? '') . (!empty($h['grado']) ? " ({$h['grado']})" : ''), $hermanosDatos))
            : ($datos['hermanos_en_ie_nombres'] ?? '');

        // Convivencia familiar (Pregunta 19): puede ser array de checks
        $viveCon = $datos['vive_con'] ?? '';
        if (is_array($viveCon)) {
            $viveCon = implode(', ', array_filter($viveCon));
        }

        // Días de eucaristía (Pregunta 27.3): puede ser array de checks
        $eucaristiaDia = $datos['eucaristia_dia'] ?? '';
        if (is_array($eucaristiaDia)) {
            $eucaristiaDia = implode(', ', array_filter($eucaristiaDia));
        }

        // Regla condicional de corte de cabello vs trenzas por género
        $corteCabello = $datos['norma_corte_cabello'] ?? '';
        $cabelloTrenzado = $datos['norma_cabello_trenzado'] ?? '';
        if ($sexo === 'M') {
            $cabelloTrenzado = 'No aplica';
            if (empty($corteCabello)) $corteCabello = 'Sí';
        } elseif ($sexo === 'F') {
            $corteCabello = 'No aplica';
            if (empty($cabelloTrenzado)) $cabelloTrenzado = 'Sí';
        }

        // Armar array completo de respuestas del cuestionario
        $cuestionario = [
            'num_hermanos' => $datos['num_hermanos'] ?? 0,
            'vive_con' => $viveCon,
            'hermanos_en_ie' => $tieneHermanosIE ? 'Sí' : 'No',
            'hermanos_en_ie_cantidad' => $cantHermanosIE,
            'hermanos_en_ie_nombres' => $nombresHermanosIE,
            'acuerdo_comunion' => $datos['acuerdo_comunion'] ?? 'Sí',
            'parroquia' => $datos['parroquia'] ?? '',
            'comunidad_zonal' => $datos['comunidad_zonal'] ?? '',
            'sacramento_bautizo' => $datos['sacramento_bautizo'] ?? 'Sí',
            'sacramento_eucaristia' => $datos['sacramento_eucaristia'] ?? 'Sí',
            'sacramento_confirmacion' => $datos['sacramento_confirmacion'] ?? ($nivel === 'secundaria' ? 'No' : 'No aplica'),
            'eucaristia_importante' => $datos['eucaristia_importante'] ?? 'Sí',
            'eucaristia_por_que' => $datos['eucaristia_por_que'] ?? '',
            'eucaristia_dia' => $eucaristiaDia,
            'padres_casados' => $datos['padres_casados'] ?? 'Sí',
            'estado_conyugal' => $datos['estado_conyugal'] ?? '',
            'enfermedad' => $datos['enfermedad'] ?? 'No',
            'conoce_diagnostico' => $datos['conoce_diagnostico'] ?? 'No aplica',
            'diagnostico_cual' => $datos['diagnostico_cual'] ?? '',
            'patria_potestad' => $datos['patria_potestad'] ?? 'Ambos padres',
            'persona_dispuesta' => $datos['persona_dispuesta'] ?? '',
            'oblig_reuniones_aula' => $datos['oblig_reuniones_aula'] ?? 'Sí',
            'oblig_atencion_padres' => $datos['oblig_atencion_padres'] ?? 'Sí',
            'oblig_escuela_padres' => $datos['oblig_escuela_padres'] ?? 'Sí',
            'oblig_conservacion' => $datos['oblig_conservacion'] ?? 'Sí',
            'oblig_actividades_ie' => $datos['oblig_actividades_ie'] ?? 'Sí',
            'oblig_multa_apafa' => $datos['oblig_multa_apafa'] ?? 'Sí',
            'norma_horario' => $datos['norma_horario'] ?? 'Sí',
            'norma_corte_cabello' => $corteCabello,
            'norma_cabello_trenzado' => $cabelloTrenzado,
            'norma_uniforme' => $datos['norma_uniforme'] ?? 'Sí',
            'norma_asistencia' => $datos['norma_asistencia'] ?? 'Sí',
            'compromiso_pago' => $datos['compromiso_pago'] ?? 'Sí',
            'autoriza_comunicacion' => $datos['autoriza_comunicacion'] ?? 'Sí',
            'autoriza_fotos_videos' => $datos['autoriza_fotos_videos'] ?? 'Sí',
            'autoriza_actividades' => $datos['autoriza_actividades'] ?? 'Sí',
            'autoriza_atencion_medica' => $datos['autoriza_atencion_medica'] ?? 'Sí',
            'autoriza_regresa_solo' => $datos['autoriza_regresa_solo'] ?? 'No',
            'recoge_nombre' => $datos['recoge_nombre'] ?? '',
            'recoge_dni' => $datos['recoge_dni'] ?? '',
            'acuerdo_integracion_aulas' => $datos['acuerdo_integracion_aulas'] ?? 'Sí',
        ];

        $payload = [
            'ano_lectivo_postula' => $anoPostula,
            'nivel' => $nivel,
            'grado_postula' => $gradoPostula,
            'estudiante_dni' => $dniEstudiante ?: ($entrevistaId ? null : 'BORRADOR-' . time()),
            'estudiante_nombres' => mb_strtoupper($nombres),
            'estudiante_apellido_paterno' => mb_strtoupper($paterno),
            'estudiante_apellido_materno' => mb_strtoupper($materno),
            'estudiante_sexo' => $sexo,
            'estudiante_fecha_nacimiento' => $this->parsearFecha($datos['estudiante_fecha_nacimiento'] ?? null),
            'colegio_procedencia' => $datos['colegio_procedencia'] ?? null,

            // Padre
            'padre_dni' => preg_replace('/\D/', '', $datos['padre_dni'] ?? '') ?: null,
            'padre_nombres' => mb_strtoupper(trim($datos['padre_nombres'] ?? '')),
            'padre_telefono' => trim($datos['padre_telefono'] ?? ''),
            'padre_ocupacion' => mb_strtoupper(trim($datos['padre_ocupacion'] ?? '')),
            'padre_grado_estudios' => trim($datos['padre_grado_estudios'] ?? ''),
            'padre_domicilio' => trim($datos['padre_domicilio'] ?? ''),

            // Madre
            'madre_dni' => preg_replace('/\D/', '', $datos['madre_dni'] ?? '') ?: null,
            'madre_nombres' => mb_strtoupper(trim($datos['madre_nombres'] ?? '')),
            'madre_telefono' => trim($datos['madre_telefono'] ?? ''),
            'madre_ocupacion' => mb_strtoupper(trim($datos['madre_ocupacion'] ?? '')),
            'madre_grado_estudios' => trim($datos['madre_grado_estudios'] ?? ''),
            'madre_domicilio' => trim($datos['madre_domicilio'] ?? ''),

            // Apoderado
            'apoderado_dni' => preg_replace('/\D/', '', $datos['apoderado_dni'] ?? '') ?: null,
            'apoderado_nombres' => mb_strtoupper(trim($datos['apoderado_nombres'] ?? '')),
            'apoderado_telefono' => trim($datos['apoderado_telefono'] ?? ''),
            'apoderado_ocupacion' => mb_strtoupper(trim($datos['apoderado_ocupacion'] ?? '')),
            'apoderado_grado_estudios' => trim($datos['apoderado_grado_estudios'] ?? ''),
            'apoderado_domicilio' => trim($datos['apoderado_domicilio'] ?? ''),
            'apoderado_parentesco' => mb_strtoupper(trim($datos['apoderado_parentesco'] ?? 'APODERADO')),

            'respuestas_cuestionario' => $cuestionario,
            'hermanos_datos' => $hermanosDatos,
            'docente_entrevistador_id' => $docenteId,
            'fecha_entrevista' => $this->parsearFecha($datos['fecha_entrevista'] ?? null) ?? now()->toDateString(),
            'resultado_conformidad' => strtoupper(trim($datos['resultado_conformidad'] ?? 'CONFORME')),
            'estado_entrevista' => $estadoEntrevista,
            'observaciones' => $datos['observaciones'] ?? null,
        ];

        if ($entrevistaId) {
            $entrevista = EntrevistaAdmision::findOrFail($entrevistaId);
            if (empty($dniEstudiante)) {
                unset($payload['estudiante_dni']);
            }
            $entrevista->update($payload);
            return $entrevista;
        }

        if (strlen($dniEstudiante) >= 8) {
            $entrevista = EntrevistaAdmision::firstOrNew([
                'estudiante_dni' => $dniEstudiante,
                'ano_lectivo_postula' => $anoPostula,
            ]);
            $entrevista->fill($payload);
            $entrevista->save();
            return $entrevista;
        }

        return EntrevistaAdmision::create($payload);
    }

    /**
     * Genera o entrega el archivo Excel oficial (Formato 1) listo para ser descargado y llenado.
     * Entrega directamente la plantilla fiel con validaciones de listas desplegables,
     * fórmulas de estado, hojas de exportación y estilos optimizados.
     *
     * @param string $nivel 'primaria' o 'secundaria'
     * @return StreamedResponse
     */
    public function descargarPlantilla(string $nivel = 'primaria'): StreamedResponse
    {
        $nivelNorm = strtolower(trim($nivel)) === 'secundaria' ? 'secundaria' : 'primaria';
        $nombreNivel = ucfirst($nivelNorm);
        $fileName = "Ficha_Entrevista_Admision_2027_{$nombreNivel}.xlsx";

        // Buscar plantilla maestra en resources/templates o storage/app/templates
        $templatePath = resource_path("templates/plantilla_entrevista_{$nivelNorm}_2027.xlsx");
        if (!file_exists($templatePath)) {
            $templatePath = storage_path("app/templates/plantilla_entrevista_{$nivelNorm}_2027.xlsx");
        }

        if (file_exists($templatePath)) {
            return response()->streamDownload(function () use ($templatePath) {
                $stream = fopen($templatePath, 'rb');
                fpassthru($stream);
                fclose($stream);
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0, no-cache, must-revalidate, proxy-revalidate',
            ]);
        }

        $spreadsheet = new Spreadsheet();

        // HOJA 1: FICHA DE ENTREVISTA
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ficha');

        // Configurar ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(40);
        $sheet->getColumnDimension('F')->setWidth(25);

        // Título Institucional
        $sheet->setCellValue('A1', 'ENTREVISTA A PADRES DE FAMILIA – PROCESO DE MATRÍCULA 2027 – ' . strtoupper($nivel));
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('1E40AF');

        $sheet->setCellValue('A2', 'Llene la columna «Respuesta» (celeste). Una ficha por entrevista.');
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('4B5563');

        // Encabezados de tabla
        $headers = ['N°', 'Pregunta / campo', 'Respuesta', 'Estado', 'Indicación / ejemplo', 'Código'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i] . '4', $h);
        }
        $sheet->getStyle('A4:F4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Campos del Formato 1
        $campos = [
            ['—', 'Proceso de matrícula 2027 – Grado al que postula', $nivel === 'secundaria' ? '1° Secundaria' : '1° Primaria', 'Completo', 'Elegir grado', 'grado'],
            ['1', 'Nombre y apellidos del estudiante', '', 'Falta', 'Ej.: Lucía Fernanda Pérez Rojas', 'est_nombres'],
            ['2', 'Fecha de nacimiento', '', 'Falta', 'dd/mm/aaaa. Ej.: 14/03/2018', 'est_fecha_nacimiento'],
            ['2.1', 'D.N.I. del estudiante', '', 'Falta', '8 dígitos. Ej.: 71234567', 'est_dni'],
            ['3', 'Nombres y apellidos del Padre', '', 'Falta', 'Ej.: Juan Carlos Pérez Díaz', 'padre_nombres'],
            ['4', 'DNI del Padre', '', 'Falta', '8 dígitos. Ej.: 71234567', 'padre_dni'],
            ['4.1', 'Grado de estudios del Padre', '', 'Falta', 'Elegir de la lista', 'padre_grado_estudios'],
            ['5', 'Ocupación del Padre', '', 'Falta', 'Ej.: Agricultor', 'padre_ocupacion'],
            ['6', 'Domicilio del Padre', '', 'Falta', 'Ej.: Av. Grau 123, Chulucanas', 'padre_domicilio'],
            ['7', 'Teléfono del Padre', '', 'Falta', 'Solo números, 6 a 9 dígitos. Ej.: 987654321', 'padre_telefono'],
            ['8', 'Nombres y apellidos de la Madre', '', 'Falta', 'Ej.: María Elena Rojas Vega', 'madre_nombres'],
            ['9', 'DNI de la Madre', '', 'Falta', '8 dígitos. Ej.: 71234567', 'madre_dni'],
            ['9.1', 'Grado de estudio de la Madre', '', 'Falta', 'Elegir de la lista', 'madre_grado_estudios'],
            ['10', 'Ocupación de la Madre', '', 'Falta', 'Ej.: Comerciante', 'madre_ocupacion'],
            ['11', 'Domicilio de la Madre', '', 'Falta', 'Ej.: Av. Grau 123, Chulucanas', 'madre_domicilio'],
            ['12', 'Teléfono de la Madre', '', 'Falta', 'Solo números, 6 a 9 dígitos. Ej.: 987654321', 'madre_telefono'],
            ['13', 'Nombre y apellidos del Apoderado (si corresponde)', '', '', 'Ej.: Rosa Díaz Vega', 'apoderado_nombres'],
            ['14', 'DNI del Apoderado', '', '', '8 dígitos. Ej.: 71234567', 'apoderado_dni'],
            ['14.1', 'Grado de estudio del Apoderado', '', '', 'Elegir de la lista', 'apoderado_grado_estudios'],
            ['15', 'Ocupación del Apoderado', '', '', 'Ej.: Docente', 'apoderado_ocupacion'],
            ['16', 'Domicilio del Apoderado', '', '', 'Ej.: Jr. Lima 456, Chulucanas', 'apoderado_domicilio'],
            ['17', 'Teléfono del Apoderado', '', '', 'Solo números, 6 a 9 dígitos. Ej.: 987654321', 'apoderado_telefono'],
            ['18', 'Número de hermanos del estudiante', '', 'Falta', 'Número entero (0 si no tiene)', 'num_hermanos'],
            ['19', 'Personas con quienes vive el estudiante', '', 'Falta', 'Papá y mamá, Solo mamá, etc.', 'vive_con'],
            ['20', '¿Tiene hermanos que estudian actualmente en la IE?', '', 'Falta', 'Sí / No', 'hermanos_en_ie'],
            ['21', '¿Cuántos en la IE?', '', '', 'Solo si N° 20 = Sí', 'hermanos_en_ie_cantidad'],
            ['22', 'Nombre de los hermanos en la IE', '', '', 'Separar con punto y coma (;)', 'hermanos_en_ie_nombres'],
            ['23', 'Axiología de COMUNIÓN y FRATERNIDAD. ¿Acepta?', '', 'Falta', 'Sí / No', 'acuerdo_comunion'],
            ['24', 'Parroquia', '', 'Falta', 'Ej.: Parroquia San Juan Bautista', 'parroquia'],
            ['25', 'Comunidad zonal a la que pertenece', '', 'Falta', 'Ej.: Comunidad Santa Rosa', 'comunidad_zonal'],
            ['26.1', 'Sacramento – Bautizo', '', 'Falta', 'Sí / No', 'sacramento_bautizo'],
            ['26.2', 'Sacramento – Eucaristía', '', 'Falta', 'Sí / No', 'sacramento_eucaristia'],
            ['27.1', '¿Considera importante la Eucaristía?', '', 'Falta', 'Sí / No', 'eucaristia_importante'],
            ['27.2', '¿Por qué?', '', 'Falta', 'Ej.: Fortalece la fe familiar', 'eucaristia_por_que'],
            ['27.3', '¿Qué día participa?', '', 'Falta', 'Domingo, Sábado, etc.', 'eucaristia_dia'],
            ['28', 'Papá y Mamá ¿Casados?', '', 'Falta', 'Sí / No', 'padres_casados'],
            ['28.1', 'Estado conyugal', '', '', 'Civil, Eclesiástico, Conviviente', 'estado_conyugal'],
            ['29', '¿Padece alguna enfermedad?', '', 'Falta', 'Sí / No', 'enfermedad'],
            ['30', '¿Conoce el diagnóstico?', '', '', 'Sí / No', 'conoce_diagnostico'],
            ['31', '¿Cuál es el diagnóstico?', '', '', 'Solo si padece enfermedad', 'diagnostico_cual'],
            ['32', '¿Quién tiene la patria potestad? (Separados)', '', '', 'Ambos, Madre, Padre, etc.', 'patria_potestad'],
            ['33', 'Progenitor sin potestad dispuesto a participar', '', '', 'Sí / No', 'persona_dispuesta'],
            ['34.1', 'Obligación: Reuniones de aula', '', 'Falta', 'Sí / No', 'oblig_reuniones_aula'],
            ['34.2', 'Obligación: Atención a Padres', '', 'Falta', 'Sí / No', 'oblig_atencion_padres'],
            ['34.3', 'Obligación: Escuela para Padres y Eucaristías', '', 'Falta', 'Sí / No', 'oblig_escuela_padres'],
            ['34.4', 'Obligación: Conservación de mobiliario', '', 'Falta', 'Sí / No', 'oblig_conservacion'],
            ['34.5', 'Obligación: Multa APAFA por incumplimiento', '', 'Falta', 'Sí / No', 'oblig_multa_apafa'],
            ['35.1', 'Norma: Puntualidad (7:05 am / 2:15 pm)', '', 'Falta', 'Sí / No', 'norma_horario'],
            ['35.2', 'Norma: Corte de cabello escolar', '', 'Falta', 'Sí / No', 'norma_corte_cabello'],
            ['35.3', 'Norma: Cabello trenzado sin maquillaje', '', 'Falta', 'Sí / No', 'norma_cabello_trenzado'],
            ['35.4', 'Norma: Uso de uniforme completo', '', 'Falta', 'Sí / No', 'norma_uniforme'],
            ['36', 'Compromiso económico (Pensión S/. 40.00 mensual)', '', 'Falta', 'Sí / No', 'compromiso_pago'],
            ['37.1', 'Autorizo comunicación por WhatsApp y llamadas', '', 'Falta', 'Sí / No', 'autoriza_comunicacion'],
            ['37.2', 'Autorizo uso de fotos y videos pedagógicos', '', 'Falta', 'Sí / No', 'autoriza_fotos_videos'],
            ['37.3', 'Autorizo participación en paseos y desfiles', '', 'Falta', 'Sí / No', 'autoriza_actividades'],
            ['37.4', 'Autorizo atención médica en emergencia', '', 'Falta', 'Sí / No', 'autoriza_atencion_medica'],
            ['37.5', 'Autorizo que el estudiante regrese solo a casa', '', 'Falta', 'Sí / No', 'autoriza_regresa_solo'],
            ['37.6', 'Persona autorizada a recoger (si no regresa solo)', '', '', 'Nombre completo', 'recoge_nombre'],
            ['37.7', 'DNI de la persona autorizada a recoger', '', '', '8 dígitos', 'recoge_dni'],
            ['38', 'Estrategia de INTEGRACIÓN DE AULAS. ¿Acepta?', '', 'Falta', 'Sí / No', 'acuerdo_integracion_aulas'],
            ['39', 'Conformidad con el proceso de admisión', 'CONFORME', 'Completo', 'CONFORME / NO CONFORME', 'conformidad'],
            ['—', 'Fecha de entrevista', now()->format('d/m/Y'), 'Completo', 'dd/mm/aaaa', 'fecha_entrevista'],
        ];

        $row = 5;
        foreach ($campos as $item) {
            $sheet->setCellValue('A' . $row, $item[0]);
            $sheet->setCellValue('B' . $row, $item[1]);
            $sheet->setCellValue('C' . $row, $item[2]);
            $sheet->setCellValue('D' . $row, $item[3]);
            $sheet->setCellValue('E' . $row, $item[4]);
            $sheet->setCellValue('F' . $row, $item[5]);

            // Fondo celeste suave para columna Respuesta
            $sheet->getStyle('C' . $row)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0F2FE']],
            ]);

            $row++;
        }

        // Bordes finos para la tabla
        $sheet->getStyle('A4:F' . ($row - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
        ]);

        // HOJA 2: EXPORTAR (Consolidado horizontal automático)
        $sheetExport = $spreadsheet->createSheet();
        $sheetExport->setTitle('Exportar_Ficha');

        $colLetter = 'A';
        foreach ($campos as $idx => $item) {
            $filaFicha = 5 + $idx;
            $sheetExport->setCellValue($colLetter . '1', $item[5]); // Código de campo
            $sheetExport->setCellValue($colLetter . '2', "=Ficha!C{$filaFicha}"); // Fórmula vinculada
            $colLetter++;
        }

        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'Ficha_Entrevista_Admision_2027_' . ucfirst($nivel) . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}

