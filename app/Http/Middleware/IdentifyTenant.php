<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Skip for central domain (e.g., axiom.test)
        if ($host === config('app.central_domain', 'localhost')) {
            return $next($request);
        }

        $tenant = Tenant::where('domain', $host)->first();

        if (!$tenant) {
            abort(404, 'Tenant not found.');
        }

        // Set the current tenant in the service container for easy access
        app()->instance('tenant', $tenant);

        // Switch Database/Schema
        $this->switchToTenantDatabase($tenant->database_name);

        return $next($request);
    }

    protected function switchToTenantDatabase(string $dbName)
    {
        // For PostgreSQL "Separate Schema" approach:
        // We can either switch the search path or use a completely different database.
        // If we use separate databases, we need to define a dynamic connection.

        Config::set('database.connections.pgsql.search_path', $dbName);
        DB::purge('pgsql');
        DB::reconnect('pgsql');
    }
}
