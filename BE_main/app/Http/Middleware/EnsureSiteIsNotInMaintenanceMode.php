<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSiteIsNotInMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = SiteSetting::current();

        if (!$settings->maintenance_mode) {
            return $next($request);
        }

        $user = $request->user();
        $roleName = $user?->role?->name;

        if (in_array($roleName, ['admin', 'staff'], true)) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'He thong dang bao tri. Vui long thu lai sau.',
            'data' => [
                'maintenance_mode' => true,
            ],
        ], 503);
    }
}
