<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

class TenantOrganizationController extends Controller
{
    public function dashboard()
    {
        if (auth()->user()->hasRole('super_admin')) {
            $organization = auth()->user()->organizations()->first();
        } else {
            $organization = auth()->user()->currentOrganization;
        }

        if (!$organization) {
            abort(403, 'No organization context');
        }

        $this->authorize('view', $organization);

        return view('organizations.dashboard', compact('organization'));
    }

    public function settings()
    {
        if (auth()->user()->hasRole('super_admin')) {
            $organization = auth()->user()->organizations()->first();
        } else {
            $organization = auth()->user()->currentOrganization;
        }

        if (!$organization) {
            abort(403, 'No organization context');
        }

        $this->authorize('update', $organization);

        return view('organizations.settings', compact('organization'));
    }

    public function update(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        $organization->update($validated);

        return redirect()->route('organizations.settings', $organization)
            ->with('success', 'Organization updated successfully.');
    }
}
