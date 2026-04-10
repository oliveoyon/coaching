<?php

namespace App\Http\Middleware;

use App\Support\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\PermissionRegistrar;

class SetCurrentTenant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $tenantContext = app(CurrentTenant::class);

        if ($user === null || $user->tenant === null) {
            $tenantContext->set(null);
            app(PermissionRegistrar::class)->setPermissionsTeamId(null);

            return $next($request);
        }

        $tenant = $user->tenant;

        if (! $tenant->isAccessible()) {
            abort(403, 'Tenant is not active.');
        }

        $tenantContext->set($tenant);
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getKey());

        return $next($request);
    }
}
