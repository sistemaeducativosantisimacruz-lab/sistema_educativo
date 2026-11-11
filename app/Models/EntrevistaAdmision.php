<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntrevistaAdmision extends Model
{
    use HasFactory;

    protected $table = 'entrevistas_admision';

    protected $fillable = [
        'ano_lectivo_postula',
        'nivel',
        'grado_postula',
        'estudiante_dni',
        'estudiante_nombres',
        'estudiante_apellido_paterno',
        'estudiante_apellido_materno',
        'estudiante_sexo',
        'estudiante_fecha_nacimiento',
        'colegio_procedencia',
        'padre_dni',
        'padre_nombres',
        'padre_telefono',
        'padre_ocupacion',
        'padre_grado_estudios',
        'padre_domicilio',
        'madre_dni',
        'madre_nombres',
        'madre_telefono',
        'madre_ocupacion',
        'madre_grado_estudios',
        'madre_domicilio',
        'apoderado_dni',
        'apoderado_nombres',
        'apoderado_telefono',
        'apoderado_ocupacion',
        'apoderado_grado_estudios',
        'apoderado_domicilio',
        'apoderado_parentesco',
        'respuestas_cuestionario',
        'hermanos_datos',
        'docente_entrevistador_id',
        'fecha_entrevista',
        'resultado_conformidad',
        'estado',
        'estado_entrevista',
        'observaciones',
        'estudiante_id',
        'matricula_id',
        'grado_seccion_asignada_id',
        'archivo_excel_origen',
    ];

    protected function casts(): array
    {
        return [
            'estudiante_fecha_nacimiento' => 'date',
            'fecha_entrevista' => 'date',
            'respuestas_cuestionario' => 'array',
            'hermanos_datos' => 'array',
        ];
    }

    public function esBorrador(): bool
    {
        return $this->estado_entrevista === 'en_progreso';
    }

    public function estaCompletada(): bool
    {
        return $this->estado_entrevista === 'completada';
    }

    public function getSexoTextoAttribute(): string
    {
        return match ($this->estudiante_sexo) {
            'M' => 'Masculino',
            'F' => 'Femenino',
            default => 'No especificado',
        };
    }

    public function docenteEntrevistador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_entrevistador_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class);
    }

    public function seccionAsignada(): BelongsTo
    {
        return $this->belongsTo(GradoSeccion::class, 'grado_seccion_asignada_id');
    }

    public function getNombreCompletoEstudianteAttribute(): string
    {
        return trim("{$this->estudiante_apellido_paterno} {$this->estudiante_apellido_materno}, {$this->estudiante_nombres}");
    }
}

