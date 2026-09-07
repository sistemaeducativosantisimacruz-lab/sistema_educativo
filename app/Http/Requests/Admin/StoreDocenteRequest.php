<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dni'              => 'required|string|size:8|unique:docentes,dni|unique:users,dni',
            'nombres'          => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'email'            => 'nullable|email|unique:users,email',
            'celular'          => 'nullable|string|max:15',
            'nivel'            => 'nullable|in:primaria,secundaria',
            'tipo'             => 'nullable|in:especialista,polidocente',
            'curso_ids'        => 'nullable|array',
            'curso_ids.*'      => 'exists:cursos,id',
        ];
    }

    public function messages(): array
    {
        return [
            'dni.unique'   => 'El DNI ya está registrado en el sistema.',
            'email.unique' => 'El correo ya está registrado en el sistema.',
        ];
    }
}
