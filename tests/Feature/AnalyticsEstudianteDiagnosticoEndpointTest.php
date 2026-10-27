<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\AnoLectivo;
use App\Models\Bimestre;
use App\Models\Grado;
use App\Models\Seccion;
use App\Models\GradoSeccion;
use App\Models\Curso;
use App\Models\Competencia;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Services\AnaliticaEvaluacionService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class AnalyticsEstudianteDiagnosticoEndpointTest extends TestCase
{
    private User $adminUser;
    private User $docenteUser;
    private AnoLectivo $anoLectivo;
    private Bimestre $bimestre1;
    private Bimestre $bimestre2;
    private GradoSeccion $gradoSeccionA;
    private GradoSeccion $gradoSeccionB;
    private Curso $curso;
    private Competencia $comp1;
    private Competencia $comp2;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Esquema mínimo en memoria SQLite
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
            $table->string('password');
            $table->foreignId('role_id')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->timestamps();
        });

        Schema::create('anos_lectivos', function (Blueprint $table) {
            $table->id();
            $table->integer('anio')->default(2026);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('bimestres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ano_lectivo_id');
            $table->integer('numero');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('estado')->default('activo');
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
            $table->timestamps();
        });

        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo')->nullable();
            $table->string('nivel')->default('primaria');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('competencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id');
            $table->string('nombre');
            $table->integer('orden')->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->string('dni')->nullable();
            $table->string('nombres');
            $table->string('apellido_paterno');
            $table->string('apellido_materno');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id');
            $table->foreignId('grado_seccion_id');
            $table->foreignId('ano_lectivo_id');
            $table->string('estado')->default('activo');
            $table->timestamps();
        });

        Schema::create('notas_bimestrales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id');
            $table->foreignId('curso_id');
            $table->foreignId('competencia_id');
            $table->foreignId('bimestre_id');
            $table->string('nota', 5)->nullable();
            $table->timestamps();
        });

        // 2. Sembrado de roles y usuarios
        $adminRole = Role::create(['nombre' => 'admin', 'descripcion' => 'Administrador']);
        $docenteRole = Role::create(['nombre' => 'docente', 'descripcion' => 'Docente']);

        $this->adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'must_change_password' => false,
        ]);

        $this->docenteUser = User::create([
            'name' => 'Docente Test',
            'email' => 'docente@test.com',
            'password' => bcrypt('password'),
            'role_id' => $docenteRole->id,
            'must_change_password' => false,
        ]);

        // 3. Sembrado de estructura académica
        $this->anoLectivo = AnoLectivo::create([
            'anio' => 2026,
            'activo' => true,
        ]);

        $this->bimestre1 = Bimestre::create([
            'ano_lectivo_id' => $this->anoLectivo->id,
            'numero' => 1,
            'estado' => 'cerrado',
        ]);

        $this->bimestre2 = Bimestre::create([
            'ano_lectivo_id' => $this->anoLectivo->id,
            'numero' => 2,
            'estado' => 'activo',
        ]);

        $grado = Grado::create(['nombre' => '3.°', 'nivel' => 'primaria', 'orden' => 3]);
        $seccionA = Seccion::create(['nombre' => 'A']);
        $seccionB = Seccion::create(['nombre' => 'B']);

        $this->gradoSeccionA = GradoSeccion::create([
            'grado_id' => $grado->id,
            'seccion_id' => $seccionA->id,
            'ano_lectivo_id' => $this->anoLectivo->id,
            'activo' => true,
        ]);

        $this->gradoSeccionB = GradoSeccion::create([
            'grado_id' => $grado->id,
            'seccion_id' => $seccionB->id,
            'ano_lectivo_id' => $this->anoLectivo->id,
            'activo' => true,
        ]);

        $this->curso = Curso::create([
            'nombre' => 'MATEMÁTICA',
            'codigo' => 'MAT',
            'nivel' => 'primaria',
            'activo' => true,
        ]);

        $this->comp1 = Competencia::create([
            'curso_id' => $this->curso->id,
            'nombre' => 'Resuelve problemas de cantidad',
            'orden' => 1,
            'activo' => true,
        ]);

        $this->comp2 = Competencia::create([
            'curso_id' => $this->curso->id,
            'nombre' => 'Resuelve problemas de regularidad',
            'orden' => 2,
            'activo' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('notas_bimestrales');
        Schema::dropIfExists('matriculas');
        Schema::dropIfExists('estudiantes');
        Schema::dropIfExists('competencias');
        Schema::dropIfExists('cursos');
        Schema::dropIfExists('grado_secciones');
        Schema::dropIfExists('secciones');
        Schema::dropIfExists('grados');
        Schema::dropIfExists('bimestres');
        Schema::dropIfExists('anos_lectivos');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');

        parent::tearDown();
    }

    public function test_admin_puede_obtener_diagnostico_200_con_estructura_valida(): void
    {
        $estudiante = Estudiante::create([
            'nombres' => 'Juan Carlos',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'García',
        ]);

        Matricula::create([
            'estudiante_id' => $estudiante->id,
            'grado_seccion_id' => $this->gradoSeccionA->id,
            'ano_lectivo_id' => $this->anoLectivo->id,
            'estado' => 'activo',
        ]);

        // Bimestre 1: notas B
        DB::table('notas_bimestrales')->insert([
            ['estudiante_id' => $estudiante->id, 'curso_id' => $this->curso->id, 'competencia_id' => $this->comp1->id, 'bimestre_id' => $this->bimestre1->id, 'nota' => 'B'],
            ['estudiante_id' => $estudiante->id, 'curso_id' => $this->curso->id, 'competencia_id' => $this->comp2->id, 'bimestre_id' => $this->bimestre1->id, 'nota' => 'B'],
        ]);

        // Bimestre 2: notas A
        DB::table('notas_bimestrales')->insert([
            ['estudiante_id' => $estudiante->id, 'curso_id' => $this->curso->id, 'competencia_id' => $this->comp1->id, 'bimestre_id' => $this->bimestre2->id, 'nota' => 'A'],
            ['estudiante_id' => $estudiante->id, 'curso_id' => $this->curso->id, 'competencia_id' => $this->comp2->id, 'bimestre_id' => $this->bimestre2->id, 'nota' => 'A'],
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson("/admin/analytics/estudiantes/{$estudiante->id}/diagnostico?bimestre_id={$this->bimestre2->id}&grado_seccion_id={$this->gradoSeccionA->id}&curso_id={$this->curso->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'estudiante' => ['id', 'nombre', 'nivel_global'],
            'columnas' => ['anterior', 'actual'],
            'competencias' => [
                '*' => ['nombre', 'anterior', 'actual']
            ],
            'diagnostico',
        ]);

        $data = $response->json();
        $this->assertEquals('PÉREZ GARCÍA, JUAN CARLOS', $data['estudiante']['nombre']);
        $this->assertEquals('A', $data['estudiante']['nivel_global']);
        $this->assertEquals('Bimestre 1', $data['columnas']['anterior']);
        $this->assertEquals('Bimestre 2', $data['columnas']['actual']);
        $this->assertCount(2, $data['competencias']);
        $this->assertStringContainsString('evolución favorable', $data['diagnostico']);
    }

    public function test_estudiante_sin_notas_retorna_nivel_global_null_y_no_es_clasificado_como_c(): void
    {
        $estudiante = Estudiante::create([
            'nombres' => 'María Elena',
            'apellido_paterno' => 'López',
            'apellido_materno' => 'Ramos',
        ]);

        Matricula::create([
            'estudiante_id' => $estudiante->id,
            'grado_seccion_id' => $this->gradoSeccionA->id,
            'ano_lectivo_id' => $this->anoLectivo->id,
            'estado' => 'activo',
        ]);

        // No insertamos notas para este estudiante
        $response = $this->actingAs($this->adminUser)
            ->getJson("/admin/analytics/estudiantes/{$estudiante->id}/diagnostico?bimestre_id={$this->bimestre2->id}&grado_seccion_id={$this->gradoSeccionA->id}&curso_id={$this->curso->id}");

        $response->assertStatus(200);
        $data = $response->json();

        // DEBE ser null, NO C
        $this->assertNull($data['estudiante']['nivel_global']);
        $this->assertEquals('Sin calificaciones registradas para el periodo seleccionado.', $data['diagnostico']);

        // Verificamos también que en la lista getEstudiantesSeccionConNivel venga como null
        $service = app(AnaliticaEvaluacionService::class);
        $lista = $service->getEstudiantesSeccionConNivel($this->anoLectivo->id, $this->gradoSeccionA->id, $this->bimestre2->id, $this->curso->id);

        $this->assertCount(1, $lista);
        $this->assertEquals('LÓPEZ RAMOS, MARÍA ELENA', $lista[0]['nombre']);
        $this->assertNull($lista[0]['nivel_global']);
    }

    public function test_estudiante_de_otra_seccion_responde_404(): void
    {
        $estudiante = Estudiante::create([
            'nombres' => 'Pedro',
            'apellido_paterno' => 'Gómez',
            'apellido_materno' => 'Díaz',
        ]);

        // Matriculado en sección B
        Matricula::create([
            'estudiante_id' => $estudiante->id,
            'grado_seccion_id' => $this->gradoSeccionB->id,
            'ano_lectivo_id' => $this->anoLectivo->id,
            'estado' => 'activo',
        ]);

        // Se solicita consultarlo con grado_seccion_id de la sección A
        $response = $this->actingAs($this->adminUser)
            ->getJson("/admin/analytics/estudiantes/{$estudiante->id}/diagnostico?bimestre_id={$this->bimestre2->id}&grado_seccion_id={$this->gradoSeccionA->id}");

        $response->assertStatus(404);
    }

    public function test_estudiante_retirado_responde_404(): void
    {
        $estudiante = Estudiante::create([
            'nombres' => 'Rosa',
            'apellido_paterno' => 'Mendoza',
            'apellido_materno' => 'Torres',
        ]);

        Matricula::create([
            'estudiante_id' => $estudiante->id,
            'grado_seccion_id' => $this->gradoSeccionA->id,
            'ano_lectivo_id' => $this->anoLectivo->id,
            'estado' => 'retirado',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson("/admin/analytics/estudiantes/{$estudiante->id}/diagnostico?bimestre_id={$this->bimestre2->id}&grado_seccion_id={$this->gradoSeccionA->id}");

        $response->assertStatus(404);
    }

    public function test_usuario_sin_rol_admin_responde_403(): void
    {
        $estudiante = Estudiante::create([
            'nombres' => 'Carlos',
            'apellido_paterno' => 'Vargas',
            'apellido_materno' => 'Silva',
        ]);

        Matricula::create([
            'estudiante_id' => $estudiante->id,
            'grado_seccion_id' => $this->gradoSeccionA->id,
            'ano_lectivo_id' => $this->anoLectivo->id,
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($this->docenteUser)
            ->getJson("/admin/analytics/estudiantes/{$estudiante->id}/diagnostico?bimestre_id={$this->bimestre2->id}&grado_seccion_id={$this->gradoSeccionA->id}");

        $response->assertStatus(403);
    }

    public function test_seccion_sin_estudiantes_retorna_lista_vacia(): void
    {
        $service = app(AnaliticaEvaluacionService::class);
        $lista = $service->getEstudiantesSeccionConNivel(
            $this->anoLectivo->id,
            $this->gradoSeccionB->id,
            $this->bimestre1->id
        );

        $this->assertIsArray($lista);
        $this->assertEmpty($lista);
    }

    public function test_bimestre_1_retorna_columna_anterior_null_y_omite_evolucion(): void
    {
        $estudiante = Estudiante::create([
            'nombres' => 'Lucía',
            'apellido_paterno' => 'Castro',
            'apellido_materno' => 'Flores',
        ]);

        Matricula::create([
            'estudiante_id' => $estudiante->id,
            'grado_seccion_id' => $this->gradoSeccionA->id,
            'ano_lectivo_id' => $this->anoLectivo->id,
            'estado' => 'activo',
        ]);

        DB::table('notas_bimestrales')->insert([
            ['estudiante_id' => $estudiante->id, 'curso_id' => $this->curso->id, 'competencia_id' => $this->comp1->id, 'bimestre_id' => $this->bimestre1->id, 'nota' => 'AD'],
            ['estudiante_id' => $estudiante->id, 'curso_id' => $this->curso->id, 'competencia_id' => $this->comp2->id, 'bimestre_id' => $this->bimestre1->id, 'nota' => 'AD'],
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson("/admin/analytics/estudiantes/{$estudiante->id}/diagnostico?bimestre_id={$this->bimestre1->id}&grado_seccion_id={$this->gradoSeccionA->id}&curso_id={$this->curso->id}");

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertNull($data['columnas']['anterior']);
        $this->assertEquals('Bimestre 1', $data['columnas']['actual']);
        $this->assertNull($data['competencias'][0]['anterior']);
        $this->assertEquals('AD', $data['competencias'][0]['actual']);
        $this->assertStringNotContainsString('Respecto al periodo anterior', $data['diagnostico']);
    }
}
