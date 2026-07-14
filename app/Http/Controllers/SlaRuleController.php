<?php

namespace App\Http\Controllers;

use App\Models\SlaRule;
use App\Models\Organization;
use Illuminate\Http\Request;

class SlaRuleController extends Controller
{
    public function create(Organization $organization)
    {
        $this->authorize('update', $organization);

        return view('organizations.sla-rules.form', compact('organization'));
    }

    public function store(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'brand_id' => 'nullable|exists:brands,id',
            'location_id' => 'nullable|exists:locations,id',
            'category_id' => 'nullable|exists:ticket_categories,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'first_response_time' => 'nullable|integer|min:1',
            'resolution_time' => 'nullable|integer|min:1',
            're_open_response_time' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $rule = $organization->slaRules()->create($validated);

        return redirect()->route('organizations.settings', $organization)
            ->with('success', 'SLA rule created successfully.');
    }

    public function edit(Organization $organization, SlaRule $rule)
    {
        $this->authorize('update', $organization);
        $this->authorize('update', $rule);

        return view('organizations.sla-rules.form', compact('organization', 'rule'));
    }

    public function update(Request $request, Organization $organization, SlaRule $rule)
    {
        $this->authorize('update', $organization);
        $this->authorize('update', $rule);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'brand_id' => 'nullable|exists:brands,id',
            'location_id' => 'nullable|exists:locations,id',
            'category_id' => 'nullable|exists:ticket_categories,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'first_response_time' => 'nullable|integer|min:1',
            'resolution_time' => 'nullable|integer|min:1',
            're_open_response_time' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $rule->update($validated);

        return redirect()->route('organizations.settings', $organization)
            ->with('success', 'SLA rule updated successfully.');
    }

    public function destroy(Organization $organization, SlaRule $rule)
    {
        $this->authorize('update', $organization);
        $this->authorize('delete', $rule);

        $rule->delete();

        return redirect()->route('organizations.settings', $organization)
            ->with('success', 'SLA rule deleted successfully.');
    }
}
