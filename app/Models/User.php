<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'dni',
        'password',
        'role_id',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function docente(): HasOne
    {
        return $this->hasOne(Docente::class);
    }

    public function estudiante(): HasOne
    {
        return $this->hasOne(Estudiante::class);
    }

    public function directivo(): HasOne
    {
        return $this->hasOne(Directivo::class);
    }

    public function isAdmin(): bool
    {
        return $this->role?->nombre === 'admin';
    }

    public function isDirectivo(): bool
    {
        return $this->role?->nombre === 'directivo';
    }

    public function isDocente(): bool
    {
        return $this->role?->nombre === 'docente';
    }

    public function isEstudiante(): bool
    {
        return $this->role?->nombre === 'estudiante';
    }

    /**
     * Determina si el usuario tiene permisos de edición/escritura administrativa.
     * Los directivos tienen perfil de SOLO CONSULTA.
     */
    public function puedeEditar(): bool
    {
        return $this->isAdmin();
    }

    public function directivoActivo(): ?Directivo
    {
        return Directivo::where('user_id', $this->id)->where('activo', true)->first();
    }

    public function cargoDirectivo(): ?string
    {
        return $this->directivoActivo()?->cargo;
    }

    public function nivelPermitidoDirectivo(): ?string
    {
        $cargo = $this->cargoDirectivo();
        if ($cargo === Directivo::CARGO_SUBDIRECTOR_PRIMARIA) {
            return 'primaria';
        }
        if ($cargo === Directivo::CARGO_SUBDIRECTOR_SECUNDARIA) {
            return 'secundaria';
        }
        return null;
    }

    public function esEntrevistador(): bool
    {
        if ($this->role?->nombre === 'admin') {
            return true;
        }
        return (bool) ($this->docente?->es_entrevistador);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => mb_strtoupper($value),
        );
    }
}
