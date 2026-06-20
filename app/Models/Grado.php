<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grado extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'nivel', 'orden'];

    public function gradoSecciones(): HasMany
    {
        return $this->hasMany(GradoSeccion::class);
    }
}
