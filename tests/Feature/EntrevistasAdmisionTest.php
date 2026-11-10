<?php

namespace Tests\Feature;

use App\Models\AnoLectivo;
use App\Models\Apoderado;
use App\Models\Docente;
use App\Models\EntrevistaAdmision;
use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\GradoSeccion;
use App\Models\Matricula;
use App\Models\Role;
use App\Models\Seccion;
use App\Models\User;
use App\Services\EntrevistaImportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EntrevistasAdmisionTest extends TestCase
{
    private User $adminUser;
    private User $docenteUser1;
    private User $docenteUser2;
    private AnoLectivo $anoLectivo;
    private GradoSeccion $gradoSeccion;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Definir tablas mínimas en SQLite en memoria
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('dni')->nullable()->unique();
            $table->string('password');
            $table->foreignId('role_id')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('docentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('curso_id')->nullable();
            $table->string('nivel', 20)->nullable();
            $table->string('tipo', 30)->nullable();
            $table->string('dni', 15)->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();
            $table->string('nombres')->nullable();
            $table->boolean('es_entrevistador')->default(false);
            $table->timestamps();
        });

        Schema::create('anos_lectivos', function (Blueprint $table) {
            $table->id();
            $table->integer('anio');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('grados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('nivel');
            $table->integer('orden')->default(1);
            $table->timestamps();
        });

        Schema::create('secciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('grado_secciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grado_id');
            $table->foreignId('seccion_id');
            $table->foreignId('ano_lectivo_id');
            $table->boolean('activo')->default(true);
            $table->foreignId('tutor_id')->nullable();
            $table->foreignId('cotutor_id')->nullable();
            $table->timestamps();
        });

        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('dni', 15)->unique();
            $table->string('apellido_paterno');
            $table->string('apellido_materno')->nullable();
            $table->string('nombres');
            $table->date('fecha_nacimiento');
            $table->char('sexo', 1)->default('M');
            $table->string('nivel', 20)->default('primaria');
            $table->string('estado', 20)->default('matriculado');
            $table->string('codigo_estudiante')->nullable();
            $table->string('colegio_inicial')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('apoderados', function (Blueprint $table) {
            $table->id();
            $table->string('estudiante_dni', 15);
            $table->string('parentesco', 50)->default('APODERADO');
            $table->string('dni', 15)->nullable();
            $table->string('nombres');
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('direccion')->nullable();
            $table->boolean('es_apoderado')->default(false);
            $table->timestamps();
        });

        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id');
            $table->foreignId('grado_seccion_id');
            $table->foreignId('ano_lectivo_id');
            $table->string('estado', 30)->default('matriculado');
            $table->string('tipo_matricula', 30)->default('Regular');
            $table->date('fecha_baja')->nullable();
            $table->string('motivo_baja')->nullable();
            $table->text('observaciones_baja')->nullable();
            $table->timestamps();
        });

        Schema::create('entrevistas_admision', function (Blueprint $table) {
            $table->id();
            $table->integer('ano_lectivo_postula')->default(2027);
            $table->string('nivel', 20)->default('primaria');
            $table->string('grado_postula', 50);
            $table->string('estudiante_dni', 15)->index();
            $table->string('estudiante_nombres', 100);
            $table->string('estudiante_apellido_paterno', 100)->nullable();
            $table->string('estudiante_apellido_materno', 100)->nullable();
            $table->date('estudiante_fecha_nacimiento')->nullable();
            $table->string('colegio_procedencia', 150)->nullable();
            $table->string('padre_dni', 15)->nullable();
            $table->string('padre_nombres', 150)->nullable();
            $table->string('padre_telefono', 25)->nullable();
            $table->string('padre_ocupacion', 100)->nullable();
            $table->string('padre_grado_estudios', 100)->nullable();
            $table->text('padre_domicilio')->nullable();
            $table->string('madre_dni', 15)->nullable();
            $table->string('madre_nombres', 150)->nullable();
            $table->string('madre_telefono', 25)->nullable();
            $table->string('madre_ocupacion', 100)->nullable();
            $table->string('madre_grado_estudios', 100)->nullable();
            $table->text('madre_domicilio')->nullable();
            $table->string('apoderado_dni', 15)->nullable();
            $table->string('apoderado_nombres', 150)->nullable();
            $table->string('apoderado_telefono', 25)->nullable();
            $table->string('apoderado_ocupacion', 100)->nullable();
            $table->string('apoderado_grado_estudios', 100)->nullable();
            $table->text('apoderado_domicilio')->nullable();
            $table->string('apoderado_parentesco', 50)->nullable();
            $table->json('respuestas_cuestionario')->nullable();
            $table->foreignId('docente_entrevistador_id')->nullable();
            $table->date('fecha_entrevista')->nullable();
            $table->string('resultado_conformidad', 50)->default('CONFORME');
            $table->string('estado', 30)->default('nuevo_ingresante');
            $table->text('observaciones')->nullable();
            $table->foreignId('estudiante_id')->nullable();
            $table->foreignId('matricula_id')->nullable();
            $table->foreignId('grado_seccion_asignada_id')->nullable();
            $table->string('archivo_excel_origen', 255)->nullable();
            $table->timestamps();
        });

        // 2. Poblar datos básicos
        $adminRole = Role::create(['nombre' => 'admin', 'descripcion' => 'Administrador']);
        $docenteRole = Role::create(['nombre' => 'docente', 'descripcion' => 'Docente']);
        Role::create(['nombre' => 'estudiante', 'descripcion' => 'Estudiante']);

        $this->adminUser = User::create([
            'name' => 'Director Admin',
            'email' => 'admin@santacruz.edu.pe',
            'dni' => '10000001',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'must_change_password' => false,
        ]);

        $this->docenteUser1 = User::create([
            'name' => 'Prof. María Entrevistadora',
            'email' => 'maria@santacruz.edu.pe',
            'dni' => '20000001',
            'password' => bcrypt('password'),
            'role_id' => $docenteRole->id,
            'must_change_password' => false,
        ]);
        Docente::create([
            'user_id' => $this->docenteUser1->id,
            'nombres' => 'María',
            'apellido_paterno' => 'Entrevistadora',
            'dni' => '20000001',
            'es_entrevistador' => true,
        ]);

        $this->docenteUser2 = User::create([
            'name' => 'Prof. Carlos Entrevistador',
            'email' => 'carlos@santacruz.edu.pe',
            'dni' => '20000002',
            'password' => bcrypt('password'),
            'role_id' => $docenteRole->id,
            'must_change_password' => false,
        ]);
        Docente::create([
            'user_id' => $this->docenteUser2->id,
            'nombres' => 'Carlos',
            'apellido_paterno' => 'Entrevistador',
            'dni' => '20000002',
            'es_entrevistador' => true,
        ]);

        $this->anoLectivo = AnoLectivo::create([
            'anio' => 2027,
            'activo' => true,
        ]);

        $grado = Grado::create([
            'nombre' => '1° Primaria',
            'nivel' => 'primaria',
            'orden' => 1,
        ]);

        $seccion = Seccion::create(['nombre' => 'A']);

        $this->gradoSeccion = GradoSeccion::create([
            'grado_id' => $grado->id,
            'seccion_id' => $seccion->id,
            'ano_lectivo_id' => $this->anoLectivo->id,
            'activo' => true,
        ]);
    }

    public function test_admin_puede_ver_index_de_admision(): void
    {
        EntrevistaAdmision::create([
            'ano_lectivo_postula' => 2027,
            'nivel' => 'primaria',
            'grado_postula' => '1° Primaria',
            'estudiante_dni' => '78901234',
            'estudiante_nombres' => 'SEBASTIAN',
            'estudiante_apellido_paterno' => 'MENDOZA',
            'estudiante_apellido_materno' => 'ROJAS',
            'resultado_conformidad' => 'CONFORME',
            'estado' => 'nuevo_ingresante',
            'docente_entrevistador_id' => $this->docenteUser1->id,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.admision.index'));

        $response->assertStatus(200);
        $response->assertSee('Admisión e Ingresantes 2027');
        $response->assertSee('MENDOZA ROJAS, SEBASTIAN');
        $response->assertSee('78901234');
        $response->assertSee('CONFORME');
        $response->assertSee('Nuevo Ingresante');
    }

    public function test_docente_ve_solo_sus_propias_entrevistas(): void
    {
        // Entrevista de docente 1
        EntrevistaAdmision::create([
            'ano_lectivo_postula' => 2027,
            'nivel' => 'primaria',
            'grado_postula' => '1° Primaria',
            'estudiante_dni' => '78901234',
            'estudiante_nombres' => 'ALUMNO DE MARIA',
            'estudiante_apellido_paterno' => 'GARCIA',
            'docente_entrevistador_id' => $this->docenteUser1->id,
        ]);

        // Entrevista de docente 2
        EntrevistaAdmision::create([
            'ano_lectivo_postula' => 2027,
            'nivel' => 'primaria',
            'grado_postula' => '1° Primaria',
            'estudiante_dni' => '11223344',
            'estudiante_nombres' => 'ALUMNO DE CARLOS',
            'estudiante_apellido_paterno' => 'LOPEZ',
            'docente_entrevistador_id' => $this->docenteUser2->id,
        ]);

        // Docente 1 ingresa a su panel
        $responseDocente1 = $this->actingAs($this->docenteUser1)->get(route('docente.admision.index'));
        $responseDocente1->assertStatus(200);
        $responseDocente1->assertSee('ALUMNO DE MARIA');
        $responseDocente1->assertDontSee('ALUMNO DE CARLOS');

        // Docente 2 ingresa a su panel
        $responseDocente2 = $this->actingAs($this->docenteUser2)->get(route('docente.admision.index'));
        $responseDocente2->assertStatus(200);
        $responseDocente2->assertSee('ALUMNO DE CARLOS');
        $responseDocente2->assertDontSee('ALUMNO DE MARIA');
    }

    public function test_docente_no_puede_ver_detalle_de_entrevista_ajena(): void
    {
        $entrevistaDocente2 = EntrevistaAdmision::create([
            'ano_lectivo_postula' => 2027,
            'nivel' => 'primaria',
            'grado_postula' => '1° Primaria',
            'estudiante_dni' => '99887766',
            'estudiante_nombres' => 'POSTULANTE PRIVADO',
            'docente_entrevistador_id' => $this->docenteUser2->id,
        ]);

        $response = $this->actingAs($this->docenteUser1)->get(route('docente.admision.show', $entrevistaDocente2));
        $response->assertStatus(403);
    }

    public function test_descarga_plantilla_retorna_stream_valido(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.admision.plantilla', ['nivel' => 'primaria']));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'spreadsheet') || str_contains($response->headers->get('content-type'), 'octet-stream'));
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('Ficha_Entrevista_Admision_2027_Primaria.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_nuevo_postulante_esta_aislado_de_listas_matriculados(): void
    {
        $entrevista = EntrevistaAdmision::create([
            'ano_lectivo_postula' => 2027,
            'nivel' => 'primaria',
            'grado_postula' => '1° Primaria',
            'estudiante_dni' => '77665544',
            'estudiante_nombres' => 'DIEGO ARMANDO',
            'estudiante_apellido_paterno' => 'MARADONA',
            'resultado_conformidad' => 'CONFORME',
            'estado' => 'nuevo_ingresante',
        ]);

        // No debe existir registro en matriculas
        $this->assertDatabaseMissing('matriculas', [
            'ano_lectivo_id' => $this->anoLectivo->id,
        ]);

        // No debe existir en la tabla estudiantes oficial
        $this->assertDatabaseMissing('estudiantes', [
            'dni' => '77665544',
        ]);

        $this->assertEquals('nuevo_ingresante', $entrevista->estado);
    }

    public function test_admin_puede_matricular_postulante_creando_estudiante_apoderado_y_matricula(): void
    {
        $entrevista = EntrevistaAdmision::create([
            'ano_lectivo_postula' => 2027,
            'nivel' => 'primaria',
            'grado_postula' => '1° Primaria',
            'estudiante_dni' => '76543210',
            'estudiante_nombres' => 'MATEO ALESSANDRO',
            'estudiante_apellido_paterno' => 'TORRES',
            'estudiante_apellido_materno' => 'DEL SOLAR',
            'estudiante_fecha_nacimiento' => '2020-03-15',
            'colegio_procedencia' => 'Cuna Jardín Los Angelitos',
            'padre_dni' => '40506070',
            'padre_nombres' => 'ROBERTO TORRES',
            'padre_telefono' => '998877665',
            'padre_ocupacion' => 'INGENIERO',
            'madre_dni' => '40506071',
            'madre_nombres' => 'CARLA DEL SOLAR',
            'madre_telefono' => '998877666',
            'apoderado_dni' => '40506070',
            'apoderado_nombres' => 'ROBERTO TORRES',
            'apoderado_telefono' => '998877665',
            'apoderado_parentesco' => 'PADRE',
            'resultado_conformidad' => 'CONFORME',
            'estado' => 'nuevo_ingresante',
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.admision.matricular', $entrevista), [
            'grado_seccion_id' => $this->gradoSeccion->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verificar que el postulante cambió de estado
        $entrevista->refresh();
        $this->assertEquals('matriculado', $entrevista->estado);
        $this->assertNotNull($entrevista->estudiante_id);
        $this->assertNotNull($entrevista->matricula_id);
        $this->assertEquals($this->gradoSeccion->id, $entrevista->grado_seccion_asignada_id);

        // Verificar tabla estudiantes
        $this->assertDatabaseHas('estudiantes', [
            'id' => $entrevista->estudiante_id,
            'dni' => '76543210',
            'nombres' => 'MATEO ALESSANDRO',
            'apellido_paterno' => 'TORRES',
            'apellido_materno' => 'DEL SOLAR',
            'estado' => 'matriculado',
        ]);

        // Verificar tabla users creada para el estudiante
        $this->assertDatabaseHas('users', [
            'dni' => '76543210',
            'email' => '76543210@santacruz.edu.pe',
        ]);

        // Verificar tabla matriculas
        $this->assertDatabaseHas('matriculas', [
            'id' => $entrevista->matricula_id,
            'estudiante_id' => $entrevista->estudiante_id,
            'grado_seccion_id' => $this->gradoSeccion->id,
            'estado' => 'matriculado',
        ]);

        // Verificar apoderados
        $this->assertDatabaseHas('apoderados', [
            'estudiante_dni' => '76543210',
            'dni' => '40506070',
            'parentesco' => 'PADRE',
        ]);
        $this->assertDatabaseHas('apoderados', [
            'estudiante_dni' => '76543210',
            'dni' => '40506071',
            'parentesco' => 'MADRE',
        ]);
    }

    public function test_servicio_separador_de_nombres_apellidos(): void
    {
        $service = new EntrevistaImportService();

        // Caso 1: Con coma "Apellidos, Nombres"
        [$n1, $p1, $m1] = $service->separarNombresApellidos('Flores Quispe, Andrea Nicole');
        $this->assertEquals('Andrea Nicole', $n1);
        $this->assertEquals('Flores', $p1);
        $this->assertEquals('Quispe', $m1);

        // Caso 2: Formato natural de 4 palabras
        [$n2, $p2, $m2] = $service->separarNombresApellidos('Lucas Matias Fernandez Rios');
        $this->assertEquals('Lucas Matias', $n2);
        $this->assertEquals('Fernandez', $p2);
        $this->assertEquals('Rios', $m2);

        // Caso 3: Formato de 3 palabras
        [$n3, $p3, $m3] = $service->separarNombresApellidos('Carlos Ramos Diaz');
        $this->assertEquals('Carlos', $n3);
        $this->assertEquals('Ramos', $p3);
        $this->assertEquals('Diaz', $m3);
    }

    public function test_importacion_archivo_excel_real_de_entrevista(): void
    {
        $realExcelPath = 'C:/Users/gadie/.gemini/antigravity/brain/65b33ff3-cda3-4d15-ae76-907badb7d685/.user_uploaded/media_1794240766212_b1400a82.xlsx';
        if (!file_exists($realExcelPath)) {
            $this->markTestSkipped('Archivo real no encontrado en el entorno.');
        }

        // Cargar la plantilla real y llenarla con datos de un postulante de prueba
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($realExcelPath);
        $sheet = $spreadsheet->getSheetByName('Ficha');
        $sheet->setCellValue('C7', '1° Primaria');
        $sheet->setCellValue('C9', 'QUISPE MAMANI, THIAGO VALENTINO');
        $sheet->setCellValue('C10', '2020-05-12');
        $sheet->setCellValue('C11', '74829103');
        $sheet->setCellValue('C12', 'CARLOS QUISPE');
        $sheet->setCellValue('C13', '40506071');
        $sheet->setCellValue('C17', '987654321');
        $sheet->setCellValue('C18', 'MARIA MAMANI');
        $sheet->setCellValue('C19', '40506072');
        $sheet->setCellValue('C80', 'CONFORME');

        $tempFile = tempnam(sys_get_temp_dir(), 'test_entrevista_') . '.xlsx';
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempFile);

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempFile,
            'Ficha_Entrevista_Padres_Primaria_2027.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $service = new EntrevistaImportService();
        $resultado = $service->importarFicha($uploadedFile, $this->docenteUser1->id, 2027);

        $this->assertEquals('74829103', $resultado['dni']);
        $this->assertEquals('CONFORME', $resultado['conformidad']);

        $this->assertDatabaseHas('entrevistas_admision', [
            'id' => $resultado['id'],
            'estudiante_dni' => '74829103',
            'estudiante_apellido_paterno' => 'QUISPE',
            'estudiante_apellido_materno' => 'MAMANI',
            'estudiante_nombres' => 'THIAGO VALENTINO',
            'padre_nombres' => 'CARLOS QUISPE',
            'padre_dni' => '40506071',
            'madre_nombres' => 'MARIA MAMANI',
            'ano_lectivo_postula' => 2027,
            'estado' => 'nuevo_ingresante',
        ]);

        if (file_exists($tempFile)) {
            @unlink($tempFile);
        }
    }

    public function test_descarga_plantilla_secundaria_retorna_stream_valido(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.admision.plantilla', ['nivel' => 'secundaria']));

        $response->assertStatus(200);
        $this->assertStringContainsString('Ficha_Entrevista_Admision_2027_Secundaria.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_importacion_ficha_secundaria_guarda_cuestionario_completo(): void
    {
        $templatePath = resource_path('templates/plantilla_entrevista_secundaria_2027.xlsx');
        if (!file_exists($templatePath)) {
            $this->markTestSkipped('Plantilla de secundaria no encontrada.');
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
        $sheet = $spreadsheet->getSheetByName('Ficha');

        // Llenar datos de prueba para Secundaria (sin columna F, solo columna C)
        $sheet->setCellValue('C7', '1° Secundaria');
        $sheet->setCellValue('C9', 'NAVARRO CASTILLO, MATIAS ADRIAN');
        $sheet->setCellValue('C10', '2014-07-22');
        $sheet->setCellValue('C11', '72345678');
        $sheet->setCellValue('C12', 'PEDRO NAVARRO');
        $sheet->setCellValue('C13', '42345678');
        $sheet->setCellValue('C17', '976543210');
        $sheet->setCellValue('C18', 'LUCIA CASTILLO');
        $sheet->setCellValue('C19', '42345679');
        $sheet->setCellValue('C42', 'Sí'); // sacramento_confirmacion (exclusivo de secundaria)
        $sheet->setCellValue('C70', 'Sí'); // compromiso pago S/ 50.00
        $sheet->setCellValue('C81', 'CONFORME');
        $sheet->setCellValue('C82', '2026-11-20');

        $tempFile = tempnam(sys_get_temp_dir(), 'test_sec_') . '.xlsx';
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempFile);

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempFile,
            'Ficha_Entrevista_Padres_Secundaria_2027.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $service = new EntrevistaImportService();
        $resultado = $service->importarFicha($uploadedFile, $this->docenteUser1->id, 2027);

        $this->assertEquals('72345678', $resultado['dni']);
        $this->assertEquals('CONFORME', $resultado['conformidad']);

        $entrevista = EntrevistaAdmision::findOrFail($resultado['id']);
        $this->assertEquals('secundaria', $entrevista->nivel);
        $this->assertStringContainsString('Secundaria', $entrevista->grado_postula);
        $this->assertArrayHasKey('sacramento_confirmacion', $entrevista->respuestas_cuestionario);
        $this->assertEquals('Sí', $entrevista->respuestas_cuestionario['sacramento_confirmacion']);

        if (file_exists($tempFile)) {
            @unlink($tempFile);
        }
    }

    public function test_docente_sin_rol_entrevistador_recibe_403(): void
    {
        $roleDocente = Role::where('nombre', 'docente')->first();
        $docenteSinRol = User::create([
            'name' => 'Prof. Juan Regular',
            'email' => 'juan@santacruz.edu.pe',
            'dni' => '20000099',
            'password' => bcrypt('password'),
            'role_id' => $roleDocente->id,
            'must_change_password' => false,
        ]);
        Docente::create([
            'user_id' => $docenteSinRol->id,
            'nombres' => 'Juan',
            'apellido_paterno' => 'Regular',
            'dni' => '20000099',
            'es_entrevistador' => false,
        ]);

        $response = $this->actingAs($docenteSinRol)->get(route('docente.admision.index'));
        $response->assertStatus(403);
    }

    public function test_admin_puede_asignar_y_revocar_rol_entrevistador_a_docente(): void
    {
        $docente = Docente::where('dni', '20000001')->first();
        $this->assertTrue((bool)$docente->es_entrevistador);

        // Revocar
        $response = $this->actingAs($this->adminUser)->postJson(route('admin.admision.docentes.toggle', $docente));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'es_entrevistador' => false,
        ]);

        $docente->refresh();
        $this->assertFalse((bool)$docente->es_entrevistador);

        // Volver a asignar
        $response2 = $this->actingAs($this->adminUser)->postJson(route('admin.admision.docentes.toggle', $docente));
        $response2->assertStatus(200);
        $response2->assertJson([
            'success' => true,
            'es_entrevistador' => true,
        ]);

        $docente->refresh();
        $this->assertTrue((bool)$docente->es_entrevistador);
    }
}
