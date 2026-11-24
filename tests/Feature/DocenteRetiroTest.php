<?php

namespace Tests\Feature;

use App\Models\AnoLectivo;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\GradoSeccion;
use App\Models\Role;
use App\Models\Seccion;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DocenteRetiroTest extends TestCase
{
    private User $adminUser;
    private Docente $docente;
    private AnoLectivo $anoLectivo;
    private GradoSeccion $gradoSeccion;
    private Curso $curso;

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
            $table->string('email')->nullable();
            $table->string('dni')->nullable();
            $table->string('password');
            $table->foreignId('role_id')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('anos_lectivos', function (Blueprint $table) {
            $table->id();
            $table->integer('anio');
            $table->boolean('activo')->default(false);
            $table->timestamps();
        });

        Schema::create('grados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('nivel');
            $table->integer('orden')->default(0);
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
            $table->foreignId('tutor_id')->nullable();
            $table->foreignId('cotutor_id')->nullable();
            $table->integer('vacantes_totales')->default(30);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('nivel');
            $table->boolean('activo')->default(true);
            $table->boolean('es_taller')->default(false);
            $table->timestamps();
        });

        Schema::create('docentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('curso_id')->nullable();
            $table->string('dni', 8)->nullable();
            $table->string('apellido_paterno', 50)->nullable();
            $table->string('apellido_materno', 50)->nullable();
            $table->string('nombres', 50)->nullable();
            $table->string('nivel', 20)->nullable();
            $table->string('tipo', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->boolean('es_entrevistador')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('docente_cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id');
            $table->foreignId('curso_id');
            $table->timestamps();
        });

        Schema::create('asignaciones_docente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id');
            $table->foreignId('grado_seccion_id');
            $table->foreignId('curso_id')->nullable();
            $table->foreignId('ano_lectivo_id');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('directivos', function (Blueprint $table) {
            $table->id();
            $table->string('dni', 8)->unique();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('cargo', 60);
            $table->string('telefono', 20)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('email', 100)->nullable();
            $table->foreignId('user_id')->nullable();
            $table->timestamps();
        });

        $adminRole = Role::create(['nombre' => 'admin', 'descripcion' => 'Administrador']);

        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'must_change_password' => false,
        ]);

        $this->anoLectivo = AnoLectivo::create([
            'anio' => 2026,
            'activo' => true,
        ]);

        $grado = Grado::create(['nombre' => '1er Grado', 'nivel' => 'secundaria', 'orden' => 1]);
        $seccion = Seccion::create(['nombre' => 'A']);

        $this->gradoSeccion = GradoSeccion::create([
            'grado_id' => $grado->id,
            'seccion_id' => $seccion->id,
            'ano_lectivo_id' => $this->anoLectivo->id,
            'vacantes_totales' => 30,
            'activo' => true,
        ]);

        $this->curso = Curso::create([
            'nombre' => 'Matemática',
            'nivel' => 'secundaria',
            'activo' => true,
        ]);

        $docenteUser = User::create([
            'name' => 'Profesor Prueba',
            'email' => 'profesor@test.com',
            'password' => bcrypt('password'),
            'must_change_password' => false,
        ]);

        $this->docente = Docente::create([
            'user_id' => $docenteUser->id,
            'dni' => '87654321',
            'apellido_paterno' => 'Gómez',
            'apellido_materno' => 'Pérez',
            'nombres' => 'Raúl',
            'nivel' => 'secundaria',
            'tipo' => 'especialista',
            'activo' => true,
        ]);

        $this->docente->cursos()->attach($this->curso->id);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('directivos');
        Schema::dropIfExists('asignaciones_docente');
        Schema::dropIfExists('docente_cursos');
        Schema::dropIfExists('docentes');
        Schema::dropIfExists('cursos');
        Schema::dropIfExists('grado_secciones');
        Schema::dropIfExists('secciones');
        Schema::dropIfExists('grados');
        Schema::dropIfExists('anos_lectivos');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');

        parent::tearDown();
    }

    public function test_admin_puede_retirar_docente(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.docentes.retirar', $this->docente));

        $response->assertRedirect(route('admin.docentes.index'));
        $response->assertSessionHas('success');

        $this->docente->refresh();
        $this->assertFalse($this->docente->activo);
        $this->assertTrue($this->docente->isRetirado());
    }

    public function test_admin_puede_reincorporar_docente(): void
    {
        $this->docente->update(['activo' => false]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.docentes.reincorporar', $this->docente));

        $response->assertRedirect(route('admin.docentes.index'));
        $response->assertSessionHas('success');

        $this->docente->refresh();
        $this->assertTrue($this->docente->activo);
        $this->assertTrue($this->docente->isActivo());
    }

    public function test_no_se_puede_asignar_docente_retirado(): void
    {
        $this->docente->update(['activo' => false]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.docentes.asignar', $this->docente), [
            'asignaciones' => [
                [
                    'grado_seccion_id' => $this->gradoSeccion->id,
                    'curso_id' => $this->curso->id,
                ]
            ]
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('asignaciones_docente', [
            'docente_id' => $this->docente->id,
            'grado_seccion_id' => $this->gradoSeccion->id,
        ]);
    }

    public function test_no_se_puede_asignar_como_tutor_a_docente_retirado(): void
    {
        $this->docente->update(['activo' => false]);

        $response = $this->actingAs($this->adminUser)->patch(route('admin.grado-secciones.tutores', $this->gradoSeccion), [
            'tutor_id' => $this->docente->id,
        ]);

        $response->assertSessionHasErrors(['tutor_id']);
        $this->gradoSeccion->refresh();
        $this->assertNull($this->gradoSeccion->tutor_id);
    }

    public function test_no_se_puede_asignar_cursos_en_seccion_a_docente_retirado(): void
    {
        $this->docente->update(['activo' => false]);

        $response = $this->actingAs($this->adminUser)->patch(route('admin.grado-secciones.update-docentes', $this->gradoSeccion), [
            'asignaciones' => [
                [
                    'curso_id' => $this->curso->id,
                    'docente_id' => $this->docente->id,
                ]
            ]
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('asignaciones_docente', [
            'docente_id' => $this->docente->id,
            'grado_seccion_id' => $this->gradoSeccion->id,
        ]);
    }

    public function test_vista_muestra_badge_retirado(): void
    {
        $this->docente->update(['activo' => false]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.docentes.index'));

        $response->assertStatus(200);
        $response->assertSee('Retirado');
    }
}
