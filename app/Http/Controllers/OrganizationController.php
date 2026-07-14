<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::with('users')->paginate(15);
        return view('admin.organizations.index', compact('organizations'));
    }

    public function create()
    {
        return view('admin.organizations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:organizations',
            'description' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'max_channels' => 'required|integer|min:1|max:100',
            'owner_first_name' => 'required|string|max:255',
            'owner_last_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:users,email',
            'owner_password' => 'required|string|min:8|confirmed',
        ]);

        $organization = Organization::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'city' => $validated['city'],
            'country' => $validated['country'],
            'max_channels' => $validated['max_channels'],
            'is_active' => true,
            'status' => 'active',
        ]);

        // Every organization starts with one default brand (channels live under brands).
        Brand::create([
            'organization_id' => $organization->id,
            'name' => $organization->name,
            'slug' => Str::slug($organization->name),
            'is_active' => true,
        ]);

        $owner = User::create([
            'first_name' => $validated['owner_first_name'],
            'last_name' => $validated['owner_last_name'],
            'email' => $validated['owner_email'],
            'password' => bcrypt($validated['owner_password']),
            'ipage_id' => 'ORG-' . Str::random(8),
            'email_verified_at' => now(),
        ]);

        // Layer 2 owner: global role + scoped organization membership.
        $owner->assignRole('organization_admin');

        OrganizationMembership::create([
            'organization_id' => $organization->id,
            'user_id' => $owner->id,
            'role' => 'organization_admin',
            'status' => 'active',
            'joined_date' => now(),
        ]);

        return redirect()->route('admin.organizations.show', $organization->id)
            ->with('success', __('Organization created successfully'));
    }

    public function show(Organization $organization)
    {
        return view('admin.organizations.show', compact('organization'));
    }

    public function edit(Organization $organization)
    {
        return view('admin.organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:organizations,name,' . $organization->id,
            'description' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'max_channels' => 'required|integer|min:1|max:100',
            'is_active' => 'boolean',
        ]);

        $organization->update($validated);

        return redirect()->route('admin.organizations.show', $organization->id)
            ->with('success', __('Organization updated successfully'));
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return redirect()->route('admin.organizations.index')
            ->with('success', __('Organization deleted successfully'));
    }

    public function suspend(Organization $organization)
    {
        $organization->update(['status' => 'suspended', 'is_active' => false]);

        return back()->with('success', __('Organization suspended'));
    }

    public function activate(Organization $organization)
    {
        $organization->update(['status' => 'active', 'is_active' => true]);

        return back()->with('success', __('Organization reactivated'));
    }

    public function cancel(Organization $organization)
    {
        $organization->update(['status' => 'cancelled', 'is_active' => false]);

        return back()->with('success', __('Organization subscription cancelled'));
    }
}
