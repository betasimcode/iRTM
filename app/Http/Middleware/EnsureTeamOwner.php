<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeamOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        if (!$user->driver || $user->driver->role !== 'team_owner') {
            abort(403, 'Only team owners can perform this action.');
        }

        return $next($request);
    }
}
