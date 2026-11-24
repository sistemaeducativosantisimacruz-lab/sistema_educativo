<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Directivo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DirectivoController extends Controller
{
    /**
     * Almacenar un nuevo directivo con cuenta de usuario de acceso.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dni' => ['required', 'string', 'digits:8', 'unique:directivos,dni'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'cargo' => ['required', 'string', Rule::in(Directivo::CARGOS)],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:100'],
        ], [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos numéricos.',
            'dni.unique' => 'Ya existe un directivo registrado con este DNI.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'cargo.required' => 'El cargo directivo es obligatorio.',
            'cargo.in' => 'El cargo seleccionado no es válido.',
            'email.email' => 'El formato del correo electrónico no es válido.',
        ]);

        DB::transaction(function () use ($validated) {
            $directivoRole = Role::firstOrCreate(['nombre' => 'directivo'], ['descripcion' => 'Directivo']);

            // Desactivar titular anterior del mismo cargo si existía
            Directivo::where('cargo', $validated['cargo'])
                ->where('activo', true)
                ->update(['activo' => false]);

            // Crear o actualizar cuenta de usuario
            $nombreCompleto = trim("{$validated['nombres']} {$validated['apellidos']}");
            $user = User::where('dni', $validated['dni'])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $nombreCompleto,
                    'dni' => $validated['dni'],
                    'email' => $validated['email'] ?? null,
                    'password' => Hash::make($validated['dni']),
                    'role_id' => $directivoRole->id,
                    'must_change_password' => true,
                ]);
            } else {
                $user->update([
                    'name' => $nombreCompleto,
                    'role_id' => $directivoRole->id,
                    'email' => $validated['email'] ?? $user->email,
                ]);
            }

            $validated['user_id'] = $user->id;
            $validated['activo'] = true;

            Directivo::create($validated);
        });

        return redirect()->route('admin.docentes.index', ['tab' => 'directivos'])
            ->with('success', 'Directivo registrado exitosamente con credenciales de consulta (usuario y clave inicial: DNI).');
    }

    /**
     * Actualizar los datos de un directivo.
     */
    public function update(Request $request, Directivo $directivo)
    {
        $validated = $request->validate([
            'dni' => ['required', 'string', 'digits:8', Rule::unique('directivos', 'dni')->ignore($directivo->id)],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'cargo' => ['required', 'string', Rule::in(Directivo::CARGOS)],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:100'],
        ], [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos numéricos.',
            'dni.unique' => 'Ya existe otro directivo con este DNI.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'cargo.required' => 'El cargo directivo es obligatorio.',
            'cargo.in' => 'El cargo seleccionado no es válido.',
            'email.email' => 'El formato del correo electrónico no es válido.',
        ]);

        DB::transaction(function () use ($directivo, $validated) {
            $directivo->update($validated);

            if ($directivo->user) {
                $directivo->user->update([
                    'name' => trim("{$validated['nombres']} {$validated['apellidos']}"),
                    'dni' => $validated['dni'],
                    'email' => $validated['email'] ?? $directivo->user->email,
                ]);
            }
        });

        return redirect()->route('admin.docentes.index', ['tab' => 'directivos'])
            ->with('success', 'Datos del directivo actualizados exitosamente.');
    }

    /**
     * Reemplazar al titular actual de un cargo por una nueva persona.
     */
    public function reemplazar(Request $request, Directivo $directivo)
    {
        $validated = $request->validate([
            'dni' => ['required', 'string', 'digits:8', Rule::unique('directivos', 'dni')->ignore($directivo->id)],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:100'],
        ], [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos numéricos.',
            'dni.unique' => 'Ya existe un directivo con este DNI.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
        ]);

        DB::transaction(function () use ($directivo, $validated) {
            $directivoRole = Role::firstOrCreate(['nombre' => 'directivo'], ['descripcion' => 'Directivo']);

            // El titular saliente pasa a cesado / inactivo
            $directivo->update(['activo' => false]);

            // Crear o buscar usuario para el nuevo titular
            $nombreCompleto = trim("{$validated['nombres']} {$validated['apellidos']}");
            $user = User::where('dni', $validated['dni'])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $nombreCompleto,
                    'dni' => $validated['dni'],
                    'email' => $validated['email'] ?? null,
                    'password' => Hash::make($validated['dni']),
                    'role_id' => $directivoRole->id,
                    'must_change_password' => true,
                ]);
            } else {
                $user->update([
                    'name' => $nombreCompleto,
                    'role_id' => $directivoRole->id,
                    'email' => $validated['email'] ?? $user->email,
                ]);
            }

            // Crear nuevo registro para el nuevo titular activo en ese cargo
            Directivo::create([
                'dni' => $validated['dni'],
                'nombres' => $validated['nombres'],
                'apellidos' => $validated['apellidos'],
                'cargo' => $directivo->cargo,
                'telefono' => $validated['telefono'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'email' => $validated['email'] ?? null,
                'user_id' => $user->id,
                'activo' => true,
            ]);
        });

        return redirect()->route('admin.docentes.index', ['tab' => 'directivos'])
            ->with('success', "Se ha reemplazado al titular del cargo {$directivo->cargo}. Las nuevas credenciales han sido generadas.");
    }

    /**
     * Quitar o desvincular a un directivo de su cargo (solo tendrá acceso a su perfil).
     */
    public function quitar(Directivo $directivo)
    {
        $directivo->update(['activo' => false]);

        return redirect()->route('admin.docentes.index', ['tab' => 'directivos'])
            ->with('success', "El directivo {$directivo->nombre_completo} ha sido desvinculado del cargo de {$directivo->cargo}. A partir de ahora solo tendrá acceso a su perfil personal.");
    }

    /**
     * Reincorporar o reactivar a un directivo desvinculado.
     */
    public function reincorporar(Directivo $directivo)
    {
        // Desactivar a cualquier otro titular del mismo cargo
        Directivo::where('cargo', $directivo->cargo)
            ->where('id', '!=', $directivo->id)
            ->where('activo', true)
            ->update(['activo' => false]);

        $directivo->update(['activo' => true]);

        return redirect()->route('admin.docentes.index', ['tab' => 'directivos'])
            ->with('success', "El directivo {$directivo->nombre_completo} ha sido reactivado en el cargo de {$directivo->cargo}.");
    }

    /**
     * Eliminar un directivo del registro.
     */
    public function destroy(Directivo $directivo)
    {
        $directivo->delete();

        return redirect()->route('admin.docentes.index', ['tab' => 'directivos'])
            ->with('success', 'Directivo eliminado del registro exitosamente.');
    }
}
