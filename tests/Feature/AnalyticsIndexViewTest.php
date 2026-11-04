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
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class AnalyticsIndexViewTest extends TestCase
{
    private User $adminUser;
    private AnoLectivo $anoLectivo;
    private GradoSeccion $gs1;
    private GradoSeccion $gs2;

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
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('anos_lectivos', function (Blueprint $table) {
            $table->id();
            $table->integer('anio')->nullable();
            $table->integer('ano')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('bimestres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ano_lectivo_id');
            $table->integer('numero');
            $table->string('nombre')->nullable();
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
            $table->timestamps();
        });

        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('nivel')->default('secundaria');
            $table->boolean('activo')->default(true);
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
            $table->string('apellido_paterno');
            $table->string('apellido_materno')->nullable();
            $table->string('nombres');
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

        $roleAdmin = Role::create(['nombre' => 'admin', 'descripcion' => 'Admin']);
        $this->adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $roleAdmin->id,
        ]);

        $this->anoLectivo = AnoLectivo::create(['ano' => 2026, 'activo' => true]);

        Bimestre::create(['ano_lectivo_id' => $this->anoLectivo->id, 'numero' => 1, 'nombre' => 'B1']);
        Bimestre::create(['ano_lectivo_id' => $this->anoLectivo->id, 'numero' => 2, 'nombre' => 'B2']);

        $g1 = Grado::create(['nombre' => '1ro Secundaria', 'nivel' => 'secundaria', 'orden' => 1]);
        $sA = Seccion::create(['nombre' => 'A']);
        $sB = Seccion::create(['nombre' => 'B']);

        $this->gs1 = GradoSeccion::create(['grado_id' => $g1->id, 'seccion_id' => $sA->id, 'ano_lectivo_id' => $this->anoLectivo->id]);
        $this->gs2 = GradoSeccion::create(['grado_id' => $g1->id, 'seccion_id' => $sB->id, 'ano_lectivo_id' => $this->anoLectivo->id]);

        Curso::create(['nombre' => 'Matemática', 'nivel' => 'secundaria', 'activo' => true]);
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

    public function test_admin_can_access_analytics_index_view_with_defaults(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.analytics.index'));
        $response->assertStatus(200);
        $response->assertViewHas('comparativaSecciones');
        $response->assertViewHas('comparativaBimestres');
        $response->assertViewHas('nivel', 'secundaria');
        $response->assertViewHas('gradoSeccionId', $this->gs1->id);
    }

    public function test_admin_can_filter_and_sections_are_dynamic(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.analytics.index', [
            'tab' => 'comparativo',
            'grado_seccion_id' => $this->gs1->id,
            'bimestre_id' => 1
        ]));

        $response->assertStatus(200);
        $comparativa = $response->viewData('comparativaSecciones');
        $this->assertNotEmpty($comparativa['categories']);
        $this->assertContains('1.° A', $comparativa['categories']);
        $this->assertContains('1.° B', $comparativa['categories']);
        $this->assertEquals($this->gs1->grado_id, $comparativa['grado_id']);
    }
}
