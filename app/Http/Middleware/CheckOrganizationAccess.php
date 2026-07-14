<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrganizationAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $organizationId = $request->route('organization');

        if ($organizationId) {
            $organization = Organization::find($organizationId);

            if (!$organization) {
                abort(404, 'Organization not found');
            }

            session(['current_organization_id' => $organizationId]);
            view()->share('currentOrganization', $organization);
        }

        return $next($request);
    }
}
