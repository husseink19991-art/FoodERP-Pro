<?php

namespace App\Http\Middleware;

use Closure;
use App\Domains\Tenant\Models\Tenant;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $domain = $request->getHost();
        $tenant = Tenant::where('domain', $domain)->first();

        if (!$tenant) {
            // Fallback to header for API requests
            $tenantId = $request->header('X-Tenant-ID');
            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
            }
        }

        if (!$tenant || !$tenant->is_active) {
            return response()->json(['error' => 'Tenant not found or inactive'], 403);
        }

        // Set the tenant in the service container
        app()->instance(Tenant::class, $tenant);
        
        // Scope all queries to this tenant by default (can be implemented via Global Scopes in models)
        // \App\Domains\Shared\Models\BaseTenantModel::setTenantId($tenant->id);

        return $next($request);
    }
}
