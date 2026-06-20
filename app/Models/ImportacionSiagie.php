<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportacionSiagie extends Model
{
    use HasFactory;

    protected $table = 'importaciones_siagie';

    protected $fillable = [
        'admin_id',
        'grado_seccion_id',
        'ano_lectivo_id',
        'nombre_archivo',
        'tipo',
        'estudiantes_importados',
        'errores',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'errores' => 'array',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function gradoSeccion(): BelongsTo
    {
        return $this->belongsTo(GradoSeccion::class);
    }

    public function anoLectivo(): BelongsTo
    {
        return $this->belongsTo(AnoLectivo::class);
    }
}
