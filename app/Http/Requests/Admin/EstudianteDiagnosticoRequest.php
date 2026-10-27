<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class EstudianteDiagnosticoRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     * En esta iteración el acceso está restringido exclusivamente a administradores (role:admin).
     * El acceso para docentes queda registrado como pendiente futuro tras definir la política por asignación/tutoría.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()?->role?->nombre === 'admin');
    }

    /**
     * Reglas de validación para los parámetros del diagnóstico por estudiante.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bimestre_id' => 'required|integer',
            'grado_seccion_id' => 'required|integer',
            'curso_id' => 'nullable|integer',
        ];
    }
}
