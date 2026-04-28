<?php

namespace App\Http\Middleware;

use App\Enums\RoleName;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Usage: middleware('role:master,admin')
     *
     * @param  string[]  $roles  One or more RoleName values allowed.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role) {
            abort(403, 'Unauthorized.');
        }

        $userRole = $user->role->name instanceof RoleName
            ? $user->role->name->value
            : (string) $user->role->name;

        if (! in_array($userRole, $roles, true)) {
            abort(403, 'Forbidden: insufficient role.');
        }

        return $next($request);
    }
}
