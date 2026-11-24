<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Directivo extends Model
{
    use HasFactory;

    protected $table = 'directivos';

    public const CARGO_DIRECTOR = 'Director(a)';
    public const CARGO_SUBDIRECTOR_SECUNDARIA = 'Subdirector(a) de Secundaria';
    public const CARGO_SUBDIRECTOR_PRIMARIA = 'Subdirector(a) de Primaria';

    public const CARGOS = [
        self::CARGO_DIRECTOR,
        self::CARGO_SUBDIRECTOR_SECUNDARIA,
        self::CARGO_SUBDIRECTOR_PRIMARIA,
    ];

    protected $fillable = [
        'dni',
        'nombres',
        'apellidos',
        'cargo',
        'telefono',
        'direccion',
        'email',
        'user_id',
    ];

    /**
     * Accesor para nombre completo en formato "Apellidos, Nombres".
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->apellidos}, {$this->nombres}");
    }

    /**
     * Usuario del sistema asociado (opcional).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
