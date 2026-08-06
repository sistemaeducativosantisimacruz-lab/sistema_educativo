<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoMatricula extends Model
{
    use HasFactory;

    protected $fillable = [
        'matricula_id',
        'tipo_movimiento',
        'fecha_movimiento',
        'motivo',
        'observaciones',
        'user_id',
    ];

    public function matricula()
    {
        return $this->belongsTo(Matricula::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
