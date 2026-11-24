<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Directivo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DirectivoController extends Controller
{
    /**
     * Almacenar un nuevo directivo.
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

        Directivo::create($validated);

        return redirect()->route('admin.docentes.index', ['tab' => 'directivos'])
            ->with('success', 'Directivo registrado exitosamente.');
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

        $directivo->update($validated);

        return redirect()->route('admin.docentes.index', ['tab' => 'directivos'])
            ->with('success', 'Datos del directivo actualizados exitosamente.');
    }

    /**
     * Eliminar un directivo.
     */
    public function destroy(Directivo $directivo)
    {
        $directivo->delete();

        return redirect()->route('admin.docentes.index', ['tab' => 'directivos'])
            ->with('success', 'Directivo eliminado del registro exitosamente.');
    }
}
