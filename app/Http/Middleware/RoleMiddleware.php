<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $userRoles = Auth::user()->getRoleNames()->toArray(); // ambil semua role user

        // Cek apakah ada intersection antara role user dan roles yang diijinkan
        if (!array_intersect($roles, $userRoles)) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
