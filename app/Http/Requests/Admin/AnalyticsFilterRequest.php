<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AnalyticsFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tipo_vista' => 'nullable|string|in:bimestral,comparativa',
            'bimestre_id' => 'nullable|integer|exists:bimestres,id',
            'bimestres_comp' => 'nullable|array',
            'bimestres_comp.*' => 'integer|exists:bimestres,id',
            'nivel' => 'nullable|string|in:primaria,secundaria',
            'grado_seccion_id' => 'nullable|integer|exists:grado_secciones,id',
            'curso_id' => 'nullable|integer|exists:cursos,id',
        ];
    }
}
