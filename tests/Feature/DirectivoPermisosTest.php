<?php

namespace Tests\Feature;

use App\Models\AnoLectivo;
use App\Models\Directivo;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\GradoSeccion;
use App\Models\Role;
use App\Models\Seccion;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DirectivoPermisosTest extends TestCase
{
    private Role $adminRole;
    private Role $directivoRole;
    private User $adminUser;
    private AnoLectivo $anoLectivo;

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
            $table->timestamps();
            $table->softDeletes();
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
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('anos_lectivos', function (Blueprint $table) {
            $table->id();
            $table->integer('anio');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
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
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('docentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('dni', 8)->nullable();
            $table->string('apellido_paterno', 50)->nullable();
            $table->string('apellido_materno', 50)->nullable();
            $table->string('nombres', 50)->nullable();
            $table->string('nivel', 20)->nullable();
            $table->string('tipo', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('es_entrevistador')->default(false);
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

        Schema::create('docente_cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id');
            $table->foreignId('curso_id');
            $table->timestamps();
        });

        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('dni', 15)->nullable();
            $table->string('apellido_paterno', 50)->nullable();
            $table->string('apellido_materno', 50)->nullable();
            $table->string('nombres', 50)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('codigo_estudiante')->nullable();
            $table->string('estado')->default('activo');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->nullable();
            $table->foreignId('grado_seccion_id');
            $table->foreignId('ano_lectivo_id');
            $table->string('estado')->default('matriculado');
            $table->timestamps();
        });

        Schema::create('asignaciones_docente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')->nullable();
            $table->foreignId('grado_seccion_id');
            $table->foreignId('curso_id')->nullable();
            $table->foreignId('ano_lectivo_id')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        $this->adminRole = Role::create(['nombre' => 'admin', 'descripcion' => 'Administrador']);
        $this->directivoRole = Role::create(['nombre' => 'directivo', 'descripcion' => 'Equipo Directivo']);

        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $this->adminRole->id,
            'must_change_password' => false,
        ]);

        $this->anoLectivo = AnoLectivo::create([
            'anio' => 2026,
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-12-20',
            'activo' => true,
        ]);
    }

    private function crearDirectivo(string $dni, string $nombres, string $apellidos, string $cargo, bool $activo = true): array
    {
        $user = User::create([
            'name' => "{$apellidos}, {$nombres}",
            'email' => "{$dni}@test.com",
            'dni' => $dni,
            'password' => bcrypt($dni),
            'role_id' => $this->directivoRole->id,
            'must_change_password' => false,
        ]);

        $directivo = Directivo::create([
            'dni' => $dni,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'cargo' => $cargo,
            'user_id' => $user->id,
            'activo' => $activo,
        ]);

        return [$directivo, $user];
    }

    public function test_director_puede_acceder_a_vistas_de_consulta(): void
    {
        [$directivo, $user] = $this->crearDirectivo('11112222', 'Carlos', 'Gómez', Directivo::CARGO_DIRECTOR, true);

        // Puede consultar docentes
        $response = $this->actingAs($user)->get(route('admin.docentes.index'));
        $response->assertStatus(200);

        // En la vista no ve botones de registrar nuevo docente o directivo
        $response->assertDontSee('Registrar Nuevo Docente');
        $response->assertDontSee('+ Nuevo Directivo');

        // Puede consultar grados y secciones
        $responseGs = $this->actingAs($user)->get(route('admin.grado-secciones.index'));
        $responseGs->assertStatus(200);
        $responseGs->assertDontSee('Añadir Nueva Sección');
    }

    public function test_directivo_no_puede_ejecutar_acciones_mutantes_obtiene_403(): void
    {
        [$directivo, $user] = $this->crearDirectivo('11112222', 'Carlos', 'Gómez', Directivo::CARGO_DIRECTOR, true);

        // Intento de registrar nuevo docente -> bloqueado con 403
        $respDocente = $this->actingAs($user)->post(route('admin.docentes.store'), [
            'dni' => '88887777',
            'nombres' => 'Juan',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'Soto',
        ]);
        $respDocente->assertStatus(403);

        // Intento de registrar directivo -> 403
        $respDir = $this->actingAs($user)->post(route('admin.directivos.store'), [
            'dni' => '88886666',
            'nombres' => 'María',
            'apellidos' => 'Flores',
            'cargo' => Directivo::CARGO_SUBDIRECTOR_PRIMARIA,
        ]);
        $respDir->assertStatus(403);

        // Intento de eliminar directivo -> 403
        $respDel = $this->actingAs($user)->delete(route('admin.directivos.destroy', $directivo));
        $respDel->assertStatus(403);
    }

    public function test_subdirector_primaria_solo_consulta_secciones_de_primaria(): void
    {
        [$directivo, $user] = $this->crearDirectivo('33334444', 'Lucía', 'Paredes', Directivo::CARGO_SUBDIRECTOR_PRIMARIA, true);

        $gradoPrimaria = Grado::create(['nombre' => '1° Primaria', 'nivel' => 'primaria', 'orden' => 1]);
        $gradoSecundaria = Grado::create(['nombre' => '1° Secundaria', 'nivel' => 'secundaria', 'orden' => 7]);
        $seccionA = Seccion::create(['nombre' => 'A']);

        $gsPrimaria = GradoSeccion::create([
            'grado_id' => $gradoPrimaria->id,
            'seccion_id' => $seccionA->id,
            'ano_lectivo_id' => $this->anoLectivo->id,
            'activo' => true,
        ]);

        $gsSecundaria = GradoSeccion::create([
            'grado_id' => $gradoSecundaria->id,
            'seccion_id' => $seccionA->id,
            'ano_lectivo_id' => $this->anoLectivo->id,
            'activo' => true,
        ]);

        // Consulta index de grado-secciones: solo ve Primaria
        $response = $this->actingAs($user)->get(route('admin.grado-secciones.index'));
        $response->assertStatus(200);
        $response->assertSee('1° Primaria');
        $response->assertDontSee('1° Secundaria');

        // Detalle de Primaria -> 200
        $respDetallePrim = $this->actingAs($user)->get(route('admin.grado-secciones.detalle', $gsPrimaria));
        $respDetallePrim->assertStatus(200);

        // Detalle de Secundaria -> 403 Forbidden
        $respDetalleSec = $this->actingAs($user)->get(route('admin.grado-secciones.detalle', $gsSecundaria));
        $respDetalleSec->assertStatus(403);
    }

    public function test_directivo_inactivo_es_redirigido_a_perfil(): void
    {
        // Directivo cesado / inactivo
        [$directivo, $user] = $this->crearDirectivo('55556666', 'Raúl', 'Mendoza', Directivo::CARGO_DIRECTOR, false);

        // Intento de entrar al dashboard o a una vista admin
        $response = $this->actingAs($user)->get(route('admin.docentes.index'));
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('warning');

        $responseDash = $this->actingAs($user)->get('/dashboard');
        $responseDash->assertRedirect(route('profile.edit'));
    }

    public function test_admin_puede_reemplazar_directivo(): void
    {
        [$directivoSaliente, $userSaliente] = $this->crearDirectivo(
            '12345678',
            'Pedro',
            'Navarro',
            Directivo::CARGO_SUBDIRECTOR_SECUNDARIA,
            true
        );

        // Admin ejecuta reemplazo
        $response = $this->actingAs($this->adminUser)->post(route('admin.directivos.reemplazar', $directivoSaliente), [
            'dni' => '87654321',
            'nombres' => 'Fernando',
            'apellidos' => 'Salazar',
            'telefono' => '999888777',
            'direccion' => 'Av. Los Álamos 456',
            'email' => 'fernando.salazar@colegio.edu.pe',
        ]);

        $response->assertRedirect(route('admin.docentes.index', ['tab' => 'directivos']));
        $response->assertSessionHas('success');

        // El saliente queda inactivo
        $directivoSaliente->refresh();
        $this->assertFalse((bool)$directivoSaliente->activo);

        // El nuevo titular queda activo
        $this->assertDatabaseHas('directivos', [
            'dni' => '87654321',
            'nombres' => 'Fernando',
            'apellidos' => 'Salazar',
            'cargo' => Directivo::CARGO_SUBDIRECTOR_SECUNDARIA,
            'activo' => true,
        ]);

        // Se le crea cuenta de usuario con rol directivo
        $this->assertDatabaseHas('users', [
            'dni' => '87654321',
            'role_id' => $this->directivoRole->id,
        ]);
    }

    public function test_admin_puede_quitar_y_reincorporar_directivo(): void
    {
        [$directivo, $user] = $this->crearDirectivo('77889900', 'Elena', 'Torres', Directivo::CARGO_DIRECTOR, true);

        // Quitar del cargo
        $respQuitar = $this->actingAs($this->adminUser)->post(route('admin.directivos.quitar', $directivo));
        $respQuitar->assertRedirect(route('admin.docentes.index', ['tab' => 'directivos']));
        $respQuitar->assertSessionHas('success');

        $directivo->refresh();
        $this->assertFalse((bool)$directivo->activo);

        // Ahora Elena no puede acceder a las consultas
        $respConsulta = $this->actingAs($user)->get(route('admin.docentes.index'));
        $respConsulta->assertRedirect(route('profile.edit'));

        // Reincorporar al cargo
        $respReinc = $this->actingAs($this->adminUser)->post(route('admin.directivos.reincorporar', $directivo));
        $respReinc->assertRedirect(route('admin.docentes.index', ['tab' => 'directivos']));
        $respReinc->assertSessionHas('success');

        $directivo->refresh();
        $this->assertTrue((bool)$directivo->activo);

        // Ahora Elena puede volver a consultar
        $user->refresh();
        $respConsultaOk = $this->actingAs($user)->get(route('admin.docentes.index'));
        $respConsultaOk->assertStatus(200);
    }
}
