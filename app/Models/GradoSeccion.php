<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradoSeccion extends Model
{
    use HasFactory;

    protected $table = 'grado_secciones';

    protected $fillable = ['grado_id', 'seccion_id', 'ano_lectivo_id', 'activo', 'tutor_id', 'cotutor_id'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class);
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class);
    }

    public function anoLectivo(): BelongsTo
    {
        return $this->belongsTo(AnoLectivo::class);
    }

    public function asignacionesDocente(): HasMany
    {
        return $this->hasMany(AsignacionDocente::class);
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class);
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'tutor_id');
    }

    public function cotutor(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'cotutor_id');
    }
}
