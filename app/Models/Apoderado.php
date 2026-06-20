<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Apoderado extends Model
{
    protected $table = 'apoderados';

    protected $fillable = [
        'estudiante_dni',
        'dni',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'direccion',
        'telefono',
        'parentesco',
        'es_apoderado',
    ];

    protected function casts(): array
    {
        return [
            'es_apoderado' => 'boolean',
        ];
    }

    /**
     * Mutador: guardar nombres en mayúsculas automáticamente.
     */
    protected static function booted(): void
    {
        static::saving(function (Apoderado $apoderado) {
            if ($apoderado->nombres)          $apoderado->nombres          = mb_strtoupper($apoderado->nombres);
            if ($apoderado->apellido_paterno) $apoderado->apellido_paterno = mb_strtoupper($apoderado->apellido_paterno);
            if ($apoderado->apellido_materno) $apoderado->apellido_materno = mb_strtoupper($apoderado->apellido_materno);
            if ($apoderado->direccion)        $apoderado->direccion        = mb_strtoupper($apoderado->direccion);
        });
    }

    /**
     * Devuelve el nombre completo del apoderado.
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim(
            implode(' ', array_filter([
                $this->apellido_paterno,
                $this->apellido_materno . ',',
                $this->nombres,
            ]))
        );
    }

    /**
     * Relación: un apoderado pertenece a un estudiante (por DNI).
     */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_dni', 'dni');
    }
}
