<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Organization;

class EnsureTenant
{
    public function handle(Request $request, Closure $next)
    {
        $orgId = $request->header('X-Organization-ID');

        if (!$orgId) {
            return response()->json(['message' => 'Organization ID missing'], 400);
        }

        if (!Organization::where('id', $orgId)->exists()) {
            return response()->json(['message' => 'Invalid Organization'], 404);
        }

        // Store org_id in request or singleton for global access
        // For now, we will just ensure it exists. 
        // In a real app, we might bind it to the container.
        // Check if authenticated user belongs to this organization
        if ($request->user() && $request->user()->organization_id !== $orgId) {
            return response()->json(['message' => 'Unauthorized access to this organization'], 403);
        }

        app()->instance('current_organization_id', $orgId);

        return $next($request);
    }
}
