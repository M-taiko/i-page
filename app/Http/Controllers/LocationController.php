<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Organization;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function create(Organization $organization)
    {
        $this->authorize('update', $organization);

        return view('organizations.locations.form', compact('organization'));
    }

    public function store(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'nullable|exists:brands,id',
            'description' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'timezone' => 'nullable|string|timezone',
            'location_type' => 'nullable|in:branch,headquarters,warehouse,kiosk',
            'is_active' => 'boolean',
        ]);

        $location = $organization->locations()->create($validated);

        return redirect()->route('organizations.settings', $organization)
            ->with('success', 'Location created successfully.');
    }

    public function edit(Organization $organization, Location $location)
    {
        $this->authorize('update', $organization);
        $this->authorize('update', $location);

        return view('organizations.locations.form', compact('organization', 'location'));
    }

    public function update(Request $request, Organization $organization, Location $location)
    {
        $this->authorize('update', $organization);
        $this->authorize('update', $location);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'nullable|exists:brands,id',
            'description' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'timezone' => 'nullable|string|timezone',
            'location_type' => 'nullable|in:branch,headquarters,warehouse,kiosk',
            'is_active' => 'boolean',
        ]);

        $location->update($validated);

        return redirect()->route('organizations.settings', $organization)
            ->with('success', 'Location updated successfully.');
    }

    public function destroy(Organization $organization, Location $location)
    {
        $this->authorize('update', $organization);
        $this->authorize('delete', $location);

        $location->delete();

        return redirect()->route('organizations.settings', $organization)
            ->with('success', 'Location deleted successfully.');
    }
}
