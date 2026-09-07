<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'fecha_baja',
        'motivo_baja',
        'observaciones_baja',
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

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoMatricula::class)->orderBy('fecha_movimiento', 'desc');
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        $query->when($filters['grado_seccion_id'] ?? null, function ($query, $seccion) {
            $query->where('matriculas.grado_seccion_id', $seccion);
        })->when($filters['nivel'] ?? null, function ($query, $nivel) {
            $query->whereHas('gradoSeccion.grado', function ($q) use ($nivel) {
                $q->where('nivel', $nivel);
            });
        })->when($filters['grado_id'] ?? null, function ($query, $grado) {
            $query->whereHas('gradoSeccion', function ($q) use ($grado) {
                $q->where('grado_id', $grado);
            });
        })->when($filters['search'] ?? null, function ($query, $search) {
            $cleanSearch = str_replace(',', ' ', $search);
            $words = array_filter(explode(' ', $cleanSearch));

            $query->whereHas('estudiante', function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(function ($sub) use ($word) {
                        $sub->where('nombres', 'ilike', "%{$word}%")
                            ->orWhere('apellido_paterno', 'ilike', "%{$word}%")
                            ->orWhere('apellido_materno', 'ilike', "%{$word}%")
                            ->orWhere('dni', 'ilike', "%{$word}%");
                    });
                }
            });
        });
    }
}
