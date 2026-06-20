<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Estudiante;
use App\Models\Apoderado;
use App\Models\AnoLectivo;
use App\Models\Grado;
use App\Models\Seccion;
use App\Models\GradoSeccion;
use App\Models\Matricula;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class EstudianteDniUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_estudiante_dni_update_propagates_correctly()
    {
        // 1. Seed Roles
        $adminRole = Role::create(['nombre' => 'admin']);
        $estudianteRole = Role::create(['nombre' => 'estudiante']);

        // 2. Create Admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@school.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);

        // 3. Create active academic year and grade/section
        $ano = AnoLectivo::create(['anio' => 2026, 'activo' => true]);
        $grado = Grado::create(['nombre' => '1ro Secundaria', 'nivel' => 'secundaria', 'orden' => 7]);
        $seccion = Seccion::create(['nombre' => 'A']);
        $gradoSeccion = GradoSeccion::create([
            'grado_id' => $grado->id,
            'seccion_id' => $seccion->id,
            'ano_lectivo_id' => $ano->id,
            'activo' => true
        ]);

        // 4. Create Student user and student record
        $oldDni = '12345678';
        $newDni = '87654321';

        $studentUser = User::create([
            'name' => 'JUAN PEREZ',
            'email' => $oldDni,
            'dni' => $oldDni,
            'password' => Hash::make($oldDni),
            'role_id' => $estudianteRole->id,
            'must_change_password' => true,
        ]);

        $estudiante = Estudiante::create([
            'user_id' => $studentUser->id,
            'dni' => $oldDni,
            'codigo_estudiante' => '123456789012',
            'nombres' => 'JUAN',
            'apellido_paterno' => 'PEREZ',
            'apellido_materno' => 'GOMEZ',
            'fecha_nacimiento' => '2015-05-15',
            'sexo' => 'M',
            'nivel' => 'secundaria',
            'estado' => 'activo',
        ]);

        Matricula::create([
            'estudiante_id' => $estudiante->id,
            'grado_seccion_id' => $gradoSeccion->id,
            'ano_lectivo_id' => $ano->id,
            'estado' => 'matriculado',
        ]);

        // 5. Create associated parent/guardian (Apoderado)
        $apoderado = Apoderado::create([
            'estudiante_dni' => $oldDni,
            'dni' => '11112222',
            'nombres' => 'CARLOS',
            'apellido_paterno' => 'PEREZ',
            'apellido_materno' => 'VALDEZ',
            'direccion' => 'Calle Principal 123',
            'telefono' => '999888777',
            'parentesco' => 'PADRE',
        ]);

        // 6. Act: Send PUT request as admin to update student DNI
        $response = $this->actingAs($admin)
            ->put(route('admin.estudiantes.update', $estudiante), [
                'dni' => $newDni,
                'codigo_estudiante' => '123456789012',
                'nombres' => 'JUAN',
                'apellido_paterno' => 'PEREZ',
                'apellido_materno' => 'GOMEZ',
                'fecha_nacimiento' => '2015-05-15',
                'sexo' => 'M',
                'apoderado_nombres' => 'CARLOS',
                'apoderado_apellido_paterno' => 'PEREZ',
                'apoderado_apellido_materno' => 'VALDEZ',
                'apoderado_dni' => '11112222',
                'apoderado_direccion' => 'Calle Principal 123',
                'apoderado_telefono' => '999888777',
                'apoderado_parentesco' => 'PADRE',
            ]);

        // 7. Assertions
        $response->assertStatus(302); // Redirect back on success
        
        // Assert student was updated
        $estudiante->refresh();
        $this->assertEquals($newDni, $estudiante->dni);

        // Assert student user credentials were updated
        $studentUser->refresh();
        $this->assertEquals($newDni, $studentUser->dni);
        $this->assertEquals($newDni, $studentUser->email); // Should be updated since email was old DNI
        $this->assertTrue(Hash::check($newDni, $studentUser->password)); // Password should update since must_change_password was true

        // Assert apoderado relationship was updated via cascading update (database ON UPDATE CASCADE)
        $apoderado->refresh();
        $this->assertEquals($newDni, $apoderado->estudiante_dni);
    }
}
