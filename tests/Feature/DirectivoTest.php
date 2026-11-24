<?php

namespace Tests\Feature;

use App\Models\Directivo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DirectivoTest extends TestCase
{
    private User $adminUser;

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

        Schema::create('grados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('nivel');
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('docente_cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id');
            $table->foreignId('curso_id');
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
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('docente_cursos');
        Schema::dropIfExists('grados');
        Schema::dropIfExists('cursos');
        Schema::dropIfExists('docentes');
        Schema::dropIfExists('directivos');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');

        parent::tearDown();
    }

    public function test_admin_puede_registrar_director(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.directivos.store'), [
            'dni' => '12345678',
            'nombres' => 'Juan Carlos',
            'apellidos' => 'Pérez Ramos',
            'cargo' => Directivo::CARGO_DIRECTOR,
            'telefono' => '998877665',
            'direccion' => 'Av. Los Álamos 123',
            'email' => 'director@colegio.edu.pe',
        ]);

        $response->assertRedirect(route('admin.docentes.index', ['tab' => 'directivos']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('directivos', [
            'dni' => '12345678',
            'nombres' => 'Juan Carlos',
            'apellidos' => 'Pérez Ramos',
            'cargo' => 'Director(a)',
            'telefono' => '998877665',
            'direccion' => 'Av. Los Álamos 123',
            'email' => 'director@colegio.edu.pe',
        ]);
    }

    public function test_admin_puede_registrar_subdirector_secundaria_y_primaria(): void
    {
        $this->actingAs($this->adminUser)->post(route('admin.directivos.store'), [
            'dni' => '22334455',
            'nombres' => 'María Elena',
            'apellidos' => 'Gómez Silva',
            'cargo' => Directivo::CARGO_SUBDIRECTOR_SECUNDARIA,
            'telefono' => '912345678',
            'direccion' => 'Calle Las Flores 456',
        ]);

        $this->actingAs($this->adminUser)->post(route('admin.directivos.store'), [
            'dni' => '33445566',
            'nombres' => 'Rosa Luz',
            'apellidos' => 'Mendoza Torres',
            'cargo' => Directivo::CARGO_SUBDIRECTOR_PRIMARIA,
            'telefono' => '955443322',
            'direccion' => 'Jr. Los Olivos 789',
        ]);

        $this->assertDatabaseHas('directivos', [
            'dni' => '22334455',
            'cargo' => 'Subdirector(a) de Secundaria',
        ]);

        $this->assertDatabaseHas('directivos', [
            'dni' => '33445566',
            'cargo' => 'Subdirector(a) de Primaria',
        ]);
    }

    public function test_validacion_falla_si_dni_no_tiene_8_digitos_o_esta_duplicado(): void
    {
        Directivo::create([
            'dni' => '11223344',
            'nombres' => 'Pedro',
            'apellidos' => 'Quispe',
            'cargo' => Directivo::CARGO_DIRECTOR,
        ]);

        // DNI con 7 dígitos
        $response1 = $this->actingAs($this->adminUser)->post(route('admin.directivos.store'), [
            'dni' => '1234567',
            'nombres' => 'Carlos',
            'apellidos' => 'Sánchez',
            'cargo' => Directivo::CARGO_DIRECTOR,
        ]);
        $response1->assertSessionHasErrors(['dni']);

        // DNI duplicado
        $response2 = $this->actingAs($this->adminUser)->post(route('admin.directivos.store'), [
            'dni' => '11223344',
            'nombres' => 'Carlos',
            'apellidos' => 'Sánchez',
            'cargo' => Directivo::CARGO_DIRECTOR,
        ]);
        $response2->assertSessionHasErrors(['dni']);
    }

    public function test_validacion_falla_si_cargo_invalido(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.directivos.store'), [
            'dni' => '99887766',
            'nombres' => 'Luis',
            'apellidos' => 'Vargas',
            'cargo' => 'Cargo Inexistente',
        ]);

        $response->assertSessionHasErrors(['cargo']);
    }

    public function test_admin_puede_actualizar_directivo(): void
    {
        $directivo = Directivo::create([
            'dni' => '44556677',
            'nombres' => 'Ana',
            'apellidos' => 'Morales',
            'cargo' => Directivo::CARGO_SUBDIRECTOR_PRIMARIA,
            'telefono' => '999111222',
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('admin.directivos.update', $directivo), [
            'dni' => '44556677', // Mismo DNI
            'nombres' => 'Ana Sofía',
            'apellidos' => 'Morales Ruiz',
            'cargo' => Directivo::CARGO_SUBDIRECTOR_PRIMARIA,
            'telefono' => '999333444',
            'direccion' => 'Nueva Dirección 123',
            'email' => 'ana.morales@colegio.edu.pe',
        ]);

        $response->assertRedirect(route('admin.docentes.index', ['tab' => 'directivos']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('directivos', [
            'id' => $directivo->id,
            'nombres' => 'Ana Sofía',
            'apellidos' => 'Morales Ruiz',
            'telefono' => '999333444',
            'direccion' => 'Nueva Dirección 123',
            'email' => 'ana.morales@colegio.edu.pe',
        ]);
    }

    public function test_admin_puede_eliminar_directivo(): void
    {
        $directivo = Directivo::create([
            'dni' => '55667788',
            'nombres' => 'Roberto',
            'apellidos' => 'Castro',
            'cargo' => Directivo::CARGO_SUBDIRECTOR_SECUNDARIA,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.directivos.destroy', $directivo));

        $response->assertRedirect(route('admin.docentes.index', ['tab' => 'directivos']));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('directivos', [
            'id' => $directivo->id,
        ]);
    }

    public function test_vista_docentes_index_incluye_directivos(): void
    {
        Directivo::create([
            'dni' => '66778899',
            'nombres' => 'Esther',
            'apellidos' => 'Navarro',
            'cargo' => Directivo::CARGO_DIRECTOR,
            'telefono' => '987123456',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.docentes.index'));

        $response->assertStatus(200);
        $response->assertViewHas('directivos');
        $response->assertSee('Esther');
        $response->assertSee('Navarro');
        $response->assertSee('Director(a)');
        $response->assertSee('Equipo Directivo');
        $response->assertSee('Registrar Directivo');
    }
}
