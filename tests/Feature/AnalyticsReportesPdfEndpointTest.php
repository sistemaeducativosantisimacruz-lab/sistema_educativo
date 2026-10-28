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
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class AnalyticsReportesPdfEndpointTest extends TestCase
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
            $table->string('nombre')->default('2026');
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
            $table->boolean('activo')->default(true);
            $table->string('nivel')->default('secundaria');
            $table->timestamps();
        });

        Schema::create('competencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id');
            $table->string('nombre');
            $table->integer('orden')->default(1);
            $table->timestamps();
        });

        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->string('nombres');
            $table->string('apellido_paterno');
            $table->string('apellido_materno');
            $table->string('dni')->nullable();
            $table->string('codigo_estudiante')->nullable();
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
            $table->string('nota')->nullable();
            $table->text('conclusion_descriptiva')->nullable();
            $table->timestamps();
        });

        $adminRole = Role::create(['nombre' => 'admin', 'descripcion' => 'Administrador']);
        $docenteRole = Role::create(['nombre' => 'docente', 'descripcion' => 'Docente']);

        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'must_change_password' => false,
        ]);

        $this->docenteUser = User::create([
            'name' => 'Docente User',
            'email' => 'docente@test.com',
            'password' => bcrypt('password'),
            'role_id' => $docenteRole->id,
            'must_change_password' => false,
        ]);

        $this->anoLectivo = AnoLectivo::create(['nombre' => '2026', 'anio' => 2026, 'activo' => true]);

        $this->bimestre1 = Bimestre::create([
            'ano_lectivo_id' => $this->anoLectivo->id,
            'numero' => 1,
            'estado' => 'cerrado'
        ]);

        $this->bimestre2 = Bimestre::create([
            'ano_lectivo_id' => $this->anoLectivo->id,
            'numero' => 2,
            'estado' => 'activo'
        ]);

        $gradoSec = Grado::create(['nombre' => '3.°', 'nivel' => 'secundaria', 'orden' => 3]);
        $seccA = Seccion::create(['nombre' => 'A']);
        $seccB = Seccion::create(['nombre' => 'B']);

        $this->gradoSeccionA = GradoSeccion::create([
            'grado_id' => $gradoSec->id,
            'seccion_id' => $seccA->id,
            'ano_lectivo_id' => $this->anoLectivo->id,
            'activo' => true
        ]);

        $this->gradoSeccionB = GradoSeccion::create([
            'grado_id' => $gradoSec->id,
            'seccion_id' => $seccB->id,
            'ano_lectivo_id' => $this->anoLectivo->id,
            'activo' => true
        ]);

        $this->curso = Curso::create(['nombre' => 'Matemática', 'codigo' => 'MAT', 'activo' => true, 'nivel' => 'secundaria']);
        $this->comp1 = Competencia::create(['curso_id' => $this->curso->id, 'nombre' => 'Resuelve problemas de cantidad', 'orden' => 1]);
        $this->comp2 = Competencia::create(['curso_id' => $this->curso->id, 'nombre' => 'Resuelve problemas de regularidad', 'orden' => 2]);
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

    public function test_admin_puede_generar_reporte_estudiante_pdf_a4(): void
    {
        $estudiante = Estudiante::create([
            'nombres' => 'Juan Carlos',
            'apellido_paterno' => 'Perez',
            'apellido_materno' => 'Gomez',
            'dni' => '72345678'
        ]);

        Matricula::create([
            'estudiante_id' => $estudiante->id,
            'grado_seccion_id' => $this->gradoSeccionA->id,
            'ano_lectivo_id' => $this->anoLectivo->id,
            'estado' => 'activo'
        ]);

        // Registrar notas
        DB::table('notas_bimestrales')->insert([
            ['estudiante_id' => $estudiante->id, 'curso_id' => $this->curso->id, 'competencia_id' => $this->comp1->id, 'bimestre_id' => $this->bimestre1->id, 'nota' => 'B'],
            ['estudiante_id' => $estudiante->id, 'curso_id' => $this->curso->id, 'competencia_id' => $this->comp2->id, 'bimestre_id' => $this->bimestre1->id, 'nota' => 'B'],
            ['estudiante_id' => $estudiante->id, 'curso_id' => $this->curso->id, 'competencia_id' => $this->comp1->id, 'bimestre_id' => $this->bimestre2->id, 'nota' => 'A'],
            ['estudiante_id' => $estudiante->id, 'curso_id' => $this->curso->id, 'competencia_id' => $this->comp2->id, 'bimestre_id' => $this->bimestre2->id, 'nota' => 'A'],
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.analytics.reporte_estudiante_pdf', [
            'estudiante_id' => $estudiante->id,
            'grado_seccion_id' => $this->gradoSeccionA->id,
            'bimestres' => [$this->bimestre1->id, $this->bimestre2->id],
            'cursos' => [$this->curso->id],
            'incluir_comparativa' => 1
        ]));

        $response->assertStatus(200);
        $response->assertSee('Santísima Cruz de Chulucanas');
        $response->assertSee('PEREZ GOMEZ, JUAN CARLOS');
        $response->assertSee('Matemática');
        $response->assertSee('Resuelve problemas de cantidad');
        $response->assertSee('Imprimir / Guardar como PDF');
    }

    public function test_estudiante_no_matriculado_o_retirado_responde_404_en_reporte_pdf(): void
    {
        $estudiante = Estudiante::create([
            'nombres' => 'Luis',
            'apellido_paterno' => 'Retirado',
            'apellido_materno' => 'Test',
            'dni' => '71112233'
        ]);

        Matricula::create([
            'estudiante_id' => $estudiante->id,
            'grado_seccion_id' => $this->gradoSeccionA->id,
            'ano_lectivo_id' => $this->anoLectivo->id,
            'estado' => 'retirado'
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.analytics.reporte_estudiante_pdf', [
            'estudiante_id' => $estudiante->id,
            'grado_seccion_id' => $this->gradoSeccionA->id,
            'bimestres' => [$this->bimestre1->id],
        ]));

        $response->assertStatus(404);
    }

    public function test_admin_puede_generar_reporte_comparativo_pdf_a4(): void
    {
        $estudiante = Estudiante::create([
            'nombres' => 'Carlos',
            'apellido_paterno' => 'Sanchez',
            'apellido_materno' => 'Vera',
            'dni' => '73445566'
        ]);

        Matricula::create([
            'estudiante_id' => $estudiante->id,
            'grado_seccion_id' => $this->gradoSeccionA->id,
            'ano_lectivo_id' => $this->anoLectivo->id,
            'estado' => 'activo'
        ]);

        DB::table('notas_bimestrales')->insert([
            ['estudiante_id' => $estudiante->id, 'curso_id' => $this->curso->id, 'competencia_id' => $this->comp1->id, 'bimestre_id' => $this->bimestre1->id, 'nota' => 'B'],
            ['estudiante_id' => $estudiante->id, 'curso_id' => $this->curso->id, 'competencia_id' => $this->comp1->id, 'bimestre_id' => $this->bimestre2->id, 'nota' => 'A'],
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.analytics.reporte_comparativo_pdf', [
            'grado_seccion_id' => $this->gradoSeccionA->id,
            'bimestres' => [$this->bimestre1->id, $this->bimestre2->id],
            'cursos' => [$this->curso->id]
        ]));

        $response->assertStatus(200);
        $response->assertSee('Reporte Comparativo de Rendimiento Académico');
        $response->assertSee('Matemática');
        $response->assertSee('Resuelve problemas de cantidad');
        $response->assertSee('Imprimir / Guardar como PDF');
    }

    public function test_usuario_sin_rol_admin_no_puede_acceder_a_reportes_pdf(): void
    {
        $responseEst = $this->actingAs($this->docenteUser)->get(route('admin.analytics.reporte_estudiante_pdf', [
            'estudiante_id' => 1,
            'grado_seccion_id' => $this->gradoSeccionA->id,
        ]));
        $responseEst->assertStatus(403);

        $responseComp = $this->actingAs($this->docenteUser)->get(route('admin.analytics.reporte_comparativo_pdf', [
            'grado_seccion_id' => $this->gradoSeccionA->id,
        ]));
        $responseComp->assertStatus(403);
    }
}
