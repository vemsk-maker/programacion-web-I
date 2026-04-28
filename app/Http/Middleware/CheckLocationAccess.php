<?php

namespace App\Http\Middleware;

use App\Enums\RoleName;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLocationAccess
{
    /**
     * Handle an incoming request.
     *
     * Reads `location_id` from route parameters first, then from the query string.
     * master and admin bypass the check entirely.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        // master / admin have unrestricted access
        if ($user->isAdmin()) {
            return $next($request);
        }

        $locationId = $request->route('location_id')
            ?? $request->route('location')
            ?? $request->integer('location_id');

        if (! $locationId) {
            // No location context in this request — allow through
            return $next($request);
        }

        if (! $user->hasLocationAccess((int) $locationId)) {
            abort(403, 'Forbidden: no access to this location.');
        }

        return $next($request);
    }
}
