<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsignacionDocente extends Model
{
    use HasFactory;

    protected $table = 'asignaciones_docente';

    protected $fillable = [
        'docente_id',
        'grado_seccion_id',
        'curso_id',       // NULL si el docente es polidocente
        'ano_lectivo_id',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class);
    }

    public function gradoSeccion(): BelongsTo
    {
        return $this->belongsTo(GradoSeccion::class);
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function anoLectivo(): BelongsTo
    {
        return $this->belongsTo(AnoLectivo::class);
    }

    public function listasCotejo(): HasMany
    {
        return $this->hasMany(ListaCotejo::class);
    }
}
