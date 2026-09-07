<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\AnoLectivo;

class StoreEstudianteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dni'               => 'required|string|size:8|unique:estudiantes,dni|unique:users,dni',
            'codigo_estudiante' => 'nullable|string|max:20|unique:estudiantes,codigo_estudiante',
            'apellido_paterno'  => 'required|string|max:255',
            'apellido_materno'  => 'required|string|max:255',
            'nombres'           => 'required|string|max:255',
            'fecha_nacimiento'  => 'required|date',
            'sexo'              => 'required|in:M,F',
            'nivel'             => 'required|in:primaria,secundaria',
            'grado_seccion_id'  => 'required|exists:grado_secciones,id',
            'tipo_matricula'    => 'required|in:Normal,Beneficio,Exonerado',
            'apoderado_nombres'          => 'nullable|string|max:255',
            'apoderado_apellido_paterno' => 'nullable|string|max:255',
            'apoderado_apellido_materno' => 'nullable|string|max:255',
            'apoderado_dni'              => 'nullable|string|size:8',
            'apoderado_direccion'        => 'nullable|string|max:255',
            'apoderado_telefono'         => 'nullable|string|max:20',
            'apoderado_parentesco'       => 'nullable|string|max:50',
            'colegio_inicial'            => 'nullable|string|max:255',
            'padre_dni'                  => 'nullable|string|size:8',
            'padre_nombres'              => 'nullable|string|max:255',
            'padre_telefono'             => 'nullable|string|max:20',
            'madre_dni'                  => 'nullable|string|size:8',
            'madre_nombres'              => 'nullable|string|max:255',
            'madre_telefono'             => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'dni.unique'              => 'El DNI ya está registrado en el sistema.',
            'grado_seccion_id.exists' => 'La seccion seleccionada no existe.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $anoActivo = AnoLectivo::where('activo', true)->first();
            if (!$anoActivo) {
                $validator->errors()->add('general', 'No hay un año lectivo activo para matricular al estudiante.');
                return;
            }

            $estudianteExistente = Estudiante::where('dni', $this->dni)->first();
            if ($estudianteExistente) {
                $yaMatriculado = Matricula::where('estudiante_id', $estudianteExistente->id)
                    ->where('ano_lectivo_id', $anoActivo->id)
                    ->exists();
                if ($yaMatriculado) {
                    $validator->errors()->add('dni', 'El estudiante con DNI ' . $this->dni . ' ya se encuentra matriculado en el año lectivo activo.');
                }
            }
        });
    }
}
