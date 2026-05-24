<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return redirect('/login');
        }

        $userRole = $request->user()->role;

        // Admin bisa akses semua
        if ($userRole === 'admin') {
            return $next($request);
        }

        // Cek apakah user memiliki role yang diizinkan
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // Arahkan ke ringkasan (bukan `/` yang memaksa ke /sales) agar tidak redirect loop
        return redirect()->route('dashboard.index')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
    }
}
