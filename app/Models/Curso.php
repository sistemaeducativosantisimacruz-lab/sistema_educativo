<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'codigo', 'activo', 'nivel'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /**
     * Mapeo visual para los nombres de los cursos (ej. primaria que usa abreviaciones por SIAGIE)
     */
    public function getNombreAttribute($value)
    {
        $map = [
            'COMU'       => 'COMUNICACIÓN',
            'PPSS'       => 'PERSONAL SOCIAL',
            'INGLES EXT' => 'INGLÉS',
        ];

        $upperValue = strtoupper(trim($value));
        
        return $map[$upperValue] ?? $value;
    }

    /** Filtra cursos disponibles para un nivel dado (incluye los marcados como 'ambos'). */
    public function scopeParaNivel($query, string $nivel)
    {
        return $query->where(function ($q) use ($nivel) {
            $q->where('nivel', $nivel)->orWhere('nivel', 'ambos');
        });
    }

    /** Filtra solo cursos académicos (excluye competencias transversales como TIC y AUT, y Castellano como Segunda Lengua CAS). */
    public function scopeSoloCursos($query)
    {
        return $query->whereNotIn('codigo', ['TIC', 'AUT', 'CAS', '0002', '0006', '0007'])
                     ->whereNotIn('nombre', ['CASTELLANO COMO SEGUNDA LENGUA', 'GESTIONA SU APRENDIZAJE', 'DESENVOLVIMIENTO EN TIC']);
    }

    /** Docentes que tienen este curso asignado (many-to-many). */
    public function docentes(): BelongsToMany
    {
        return $this->belongsToMany(Docente::class, 'docente_cursos')
                    ->withTimestamps();
    }

    /** Asignaciones de docente que incluyen este curso. */
    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionDocente::class);
    }

    public function competencias(): HasMany
    {
        return $this->hasMany(Competencia::class);
    }

    public function notasBimestrales(): HasMany
    {
        return $this->hasMany(NotaBimestral::class);
    }
}
