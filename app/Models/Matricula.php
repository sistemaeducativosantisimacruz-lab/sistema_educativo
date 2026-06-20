<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Matricula extends Model
{
    use HasFactory;

    protected $fillable = [
        'estudiante_id',
        'grado_seccion_id',
        'ano_lectivo_id',
        'estado',
        'tipo_matricula',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function gradoSeccion(): BelongsTo
    {
        return $this->belongsTo(GradoSeccion::class);
    }

    public function anoLectivo(): BelongsTo
    {
        return $this->belongsTo(AnoLectivo::class);
    }

    public function promediosBimestrales(): HasMany
    {
        return $this->hasMany(PromedioBimestral::class);
    }

    public function mensualidades(): HasMany
    {
        return $this->hasMany(Mensualidad::class);
    }
}
