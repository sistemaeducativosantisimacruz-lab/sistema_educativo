<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->must_change_password && !session('skip_password_change')) {
            // Permitir solo la ruta de cambio de contraseña, el skip y el logout
            if (! $request->routeIs('password.force_change') && 
                ! $request->routeIs('password.update_forced') && 
                ! $request->routeIs('password.skip_forced') && 
                ! $request->routeIs('logout')) {
                return redirect()->route('password.force_change');
            }
        }

        return $next($request);
    }
}
