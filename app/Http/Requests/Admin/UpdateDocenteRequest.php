<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $docente = $this->route('docente');

        return [
            'dni'              => 'required|string|size:8|unique:docentes,dni,' . $docente->id,
            'nombres'          => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'email'            => 'nullable|email|unique:users,email,' . $docente->user_id,
            'celular'          => 'nullable|string|max:15',
            'nivel'            => 'nullable|in:primaria,secundaria',
            'tipo'             => 'nullable|in:especialista,polidocente',
            'curso_ids'        => 'nullable|array',
            'curso_ids.*'      => 'nullable|exists:cursos,id',
        ];
    }

    public function messages(): array
    {
        return [
            'dni.unique'   => 'El DNI ya está registrado en otro usuario del sistema.',
            'email.unique' => 'El correo ya está registrado en otro usuario del sistema.',
        ];
    }
}
