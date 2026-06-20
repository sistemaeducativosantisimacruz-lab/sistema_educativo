<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mensualidad extends Model
{
    use HasFactory;

    protected $table = 'mensualidades';

    protected $fillable = [
        'matricula_id',
        'mes',
        'anio',
        'estado',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'mes'  => 'integer',
            'anio' => 'integer',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** Devuelve el nombre del mes en español. */
    public function getNombreMesAttribute(): string
    {
        $meses = [
            1  => 'Enero',    2  => 'Febrero',   3  => 'Marzo',
            4  => 'Abril',    5  => 'Mayo',       6  => 'Junio',
            7  => 'Julio',    8  => 'Agosto',     9  => 'Septiembre',
            10 => 'Octubre',  11 => 'Noviembre',  12 => 'Diciembre',
        ];

        return $meses[$this->mes] ?? '—';
    }

    /** Badge CSS según estado */
    public function getBadgeClassAttribute(): string
    {
        return match ($this->estado) {
            'PAGÓ'        => 'bg-green-100 text-green-800',
            'EXONERADO'   => 'bg-blue-100 text-blue-800',
            'BENEFICIADO' => 'bg-purple-100 text-purple-800',
            default       => 'bg-red-100 text-red-800',   // DEBE
        };
    }

    /** Lista de todos los estados disponibles */
    public static function estados(): array
    {
        return ['DEBE', 'PAGÓ', 'EXONERADO', 'BENEFICIADO'];
    }

    /** Lista de meses para selects */
    public static function meses(): array
    {
        return [
            1  => 'Enero',    2  => 'Febrero',   3  => 'Marzo',
            4  => 'Abril',    5  => 'Mayo',       6  => 'Junio',
            7  => 'Julio',    8  => 'Agosto',     9  => 'Septiembre',
            10 => 'Octubre',  11 => 'Noviembre',  12 => 'Diciembre',
        ];
    }
}
