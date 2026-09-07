<?php

namespace App\Policies;

use App\Models\GradoSeccion;
use App\Models\User;
use App\Models\AnoLectivo;
use Illuminate\Auth\Access\HandlesAuthorization;

class GradoSeccionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the section.
     */
    public function view(User $user, GradoSeccion $gradoSeccion): bool
    {
        // Asumimos que los admins siempre tienen acceso
        if ($user->role && $user->role->nombre === 'admin') {
            return true;
        }

        // Si no es admin ni docente, denegar
        if (!$user->docente) {
            return false;
        }

        $docente = $user->docente;
        
        // El tutor tiene acceso irrestricto a su sección
        $esTutor = $gradoSeccion->tutor_id === $docente->id;
        if ($esTutor) {
            return true;
        }

        // Obtener año lectivo activo
        $anoActivo = AnoLectivo::where('activo', true)->first();
        if (!$anoActivo) {
            return false;
        }

        // Verificar si tiene alguna asignación de curso en esa sección
        return $docente->asignaciones()
            ->where('ano_lectivo_id', $anoActivo->id)
            ->where('grado_seccion_id', $gradoSeccion->id)
            ->exists();
    }
}
