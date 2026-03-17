<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return redirect('/login');
        }

        $user = $request->user();
        
        if ($role === 'admin' && !$user->isAdmin()) {
            abort(403, 'Unauthorized access. Admin role required.');
        }
        
        if ($role === 'super_admin' && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access. Super Admin role required.');
        }
        
        if ($role === 'cashier' && $user->isSuperAdmin()) {
            return $next($request);
        }

        return $next($request);
    }
}
