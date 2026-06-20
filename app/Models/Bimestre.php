<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bimestre extends Model
{
    use HasFactory;

    protected $fillable = [
        'ano_lectivo_id',
        'numero',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'cerrado_en',
        'cerrado_por',
        'notas_publicadas_primaria',
        'notas_publicadas_secundaria',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'cerrado_en' => 'datetime',
            'notas_publicadas_primaria' => 'boolean',
            'notas_publicadas_secundaria' => 'boolean',
        ];
    }

    public function anoLectivo(): BelongsTo
    {
        return $this->belongsTo(AnoLectivo::class);
    }

    public function cerradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }

    public function promediosBimestrales(): HasMany
    {
        return $this->hasMany(PromedioBimestral::class);
    }
}
