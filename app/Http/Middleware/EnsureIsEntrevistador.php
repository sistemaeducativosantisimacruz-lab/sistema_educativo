<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsEntrevistador
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $request->user()->esEntrevistador()) {
            abort(403, 'No tiene asignado el rol de docente entrevistador para el proceso de admisión.');
        }

        return $next($request);
    }
}
