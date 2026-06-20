<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnoLectivo extends Model
{
    use HasFactory;

    protected $table = 'anos_lectivos';

    protected $fillable = ['anio', 'activo'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function gradoSecciones(): HasMany
    {
        return $this->hasMany(GradoSeccion::class);
    }

    public function bimestres(): HasMany
    {
        return $this->hasMany(Bimestre::class);
    }
}
