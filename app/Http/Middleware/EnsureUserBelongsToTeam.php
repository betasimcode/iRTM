<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToTeam
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        // Admin puede todo
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Si la ruta tiene team_id (ej: /teams/{team})
        $teamId = $request->route('team');

        if ($teamId && $user->team_id != $teamId) {
            abort(403, 'Unauthorized team access.');
        }

        return $next($request);
    }
}
