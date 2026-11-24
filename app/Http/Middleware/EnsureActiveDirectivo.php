<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveDirectivo
{
    /**
     * Verifica que si el usuario tiene rol directivo, su registro esté activo y con cargo asignado.
     * De lo contrario, se restringe el acceso y solo puede ver su perfil de usuario.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isDirectivo()) {
            if (!$user->directivoActivo()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Tu cargo directivo se encuentra inactivo o ha sido desvinculado.'
                    ], 403);
                }

                return redirect()->route('profile.edit')
                    ->with('warning', 'Tu cargo directivo se encuentra inactivo o ha sido desvinculado. Solo tienes acceso a tu perfil de usuario.');
            }
        }

        return $next($request);
    }
}
