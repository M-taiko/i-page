<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationActive
{
    /**
     * Blocks write actions when the acting organization is suspended or cancelled.
     * Reads (GET/HEAD) are always allowed so an admin can still see billing/status.
     * Super Admin bypasses this entirely.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('super_admin')) {
            return $next($request);
        }

        // Reads are always permitted.
        if (in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $organization = $this->resolveOrganization($request, $user);

        if ($organization && !$organization->isActive()) {
            $message = $organization->isCancelled()
                ? __('This organization\'s subscription has been cancelled.')
                : __('This organization is currently suspended.');

            return back()->with('error', $message);
        }

        return $next($request);
    }

    private function resolveOrganization(Request $request, $user): ?Organization
    {
        $routeOrg = $request->route('organization');

        if ($routeOrg instanceof Organization) {
            return $routeOrg;
        }

        if ($routeOrg) {
            return Organization::find($routeOrg);
        }

        return $user?->currentOrganization;
    }
}
