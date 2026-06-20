<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Docente extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'curso_id',
        'nivel',
        'tipo',
        'dni',
        'celular',
        'apellido_paterno',
        'apellido_materno',
        'nombres',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => 'string',
        ];
    }

    /* ── Relaciones ──────────────────────────────────────── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Cursos asignados al docente (many-to-many via docente_cursos).
     * Aplica principalmente a especialistas.
     */
    public function cursos(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'docente_cursos')
                    ->withTimestamps();
    }

    /**
     * Relación legacy mantenida por compatibilidad con código existente.
     * Para nuevos registros usar ->cursos() en su lugar.
     */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionDocente::class);
    }

    public function tutoriaSecciones(): HasMany
    {
        return $this->hasMany(GradoSeccion::class, 'tutor_id');
    }

    public function plantillas(): HasMany
    {
        return $this->hasMany(PlantillaListaCotejo::class);
    }

    /* ── Helpers ─────────────────────────────────────────── */

    /** Devuelve true si el docente es polidocente (cubre todo en un grado). */
    public function esPolidocente(): bool
    {
        return $this->tipo === 'polidocente';
    }

    /** Devuelve true si el docente es especialista (uno o más cursos específicos). */
    public function esEspecialista(): bool
    {
        return $this->tipo === 'especialista';
    }

    /** Nombres de los cursos separados por coma, para mostrar en listas. */
    public function nombresCursos(): string
    {
        if ($this->esPolidocente()) {
            return 'Todos los cursos (Polidocente)';
        }
        return $this->cursos->whereNotIn('codigo', ['TIC', 'AUT', 'CAS'])->pluck('nombre')->join(', ') ?: '—';
    }

    /* ── Mutators ────────────────────────────────────────── */

    protected function nombres(): Attribute
    {
        return Attribute::make(set: fn ($v) => mb_strtoupper($v));
    }

    protected function apellidoPaterno(): Attribute
    {
        return Attribute::make(set: fn ($v) => mb_strtoupper($v));
    }

    protected function apellidoMaterno(): Attribute
    {
        return Attribute::make(set: fn ($v) => mb_strtoupper($v));
    }
}
