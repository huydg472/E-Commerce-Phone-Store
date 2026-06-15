<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * Allow only users whose role includes one of the given permissions.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($permissions === []) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if (! $user->isAdminOrStaff()) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        if (! $user->hasPermission($permissions)) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        return $next($request);
    }
}
