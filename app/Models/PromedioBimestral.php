<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromedioBimestral extends Model
{
    use HasFactory;

    protected $table = 'promedios_bimestrales';

    protected $fillable = [
        'matricula_id',
        'competencia_id',
        'bimestre_id',
        'promedio_numero',
        'promedio_letra',
        'calculado_en',
    ];

    protected function casts(): array
    {
        return [
            'promedio_numero' => 'decimal:2',
            'calculado_en' => 'datetime',
        ];
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class);
    }

    public function competencia(): BelongsTo
    {
        return $this->belongsTo(Competencia::class);
    }

    public function bimestre(): BelongsTo
    {
        return $this->belongsTo(Bimestre::class);
    }
}
