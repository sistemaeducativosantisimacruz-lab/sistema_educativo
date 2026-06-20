<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Estudiante extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'dni',
        'codigo_estudiante',
        'apellido_paterno',
        'apellido_materno',
        'nombres',
        'fecha_nacimiento',
        'sexo',
        'nivel',
        'estado',
        'colegio_inicial',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

    //Relaciones 

    /** El usuario del sistema asociado al estudiante. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Matrículas del estudiante a lo largo de los años lectivos. */
    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class);
    }

    /** Calificaciones registradas para el estudiante. */
    public function notasBimestrales(): HasMany
    {
        return $this->hasMany(NotaBimestral::class);
    }

    /** Familiares (apoderados/padres) vinculados al estudiante. */
    public function familiares(): HasMany
    {
        return $this->hasMany(Apoderado::class, 'estudiante_dni', 'dni');
    }

    /**
     * Apoderado principal vinculado al estudiante.
     */
    public function apoderado(): HasOne
    {
        return $this->hasOne(Apoderado::class, 'estudiante_dni', 'dni')->where('es_apoderado', true);
    }

    /**
     * Padre del estudiante.
     */
    public function padre(): HasOne
    {
        return $this->hasOne(Apoderado::class, 'estudiante_dni', 'dni')->where('parentesco', 'PADRE');
    }

    /**
     * Madre del estudiante.
     */
    public function madre(): HasOne
    {
        return $this->hasOne(Apoderado::class, 'estudiante_dni', 'dni')->where('parentesco', 'MADRE');
    }

    //Mutadores (normalización automática a mayúsculas)  

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

    //Accesores útiles

    /** Nombre completo: APELLIDO PATERNO APELLIDO MATERNO, NOMBRES */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->apellido_paterno} {$this->apellido_materno}, {$this->nombres}";
    }
}
