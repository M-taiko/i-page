<?php

namespace App\Http\Controllers;

use App\Models\AudienceSegment;
use Illuminate\Http\Request;

class AudienceSegmentController extends Controller
{
    public function index(Request $request)
    {
        // Super admin sees all segments
        if (auth()->user()->hasRole('super_admin')) {
            $segments = AudienceSegment::paginate(20);
        } else {
            $organization = auth()->user()->currentOrganization;
            if (!$organization) {
                abort(403, 'No organization context');
            }
            $segments = $organization->audienceSegments()->paginate(20);
        }

        return view('audience-segments.index', compact('segments'));
    }

    public function create()
    {
        if (auth()->user()->hasRole('super_admin')) {
            $organization = auth()->user()->organizations()->first() ?? auth()->user()->organizations()->first();
        } else {
            $organization = auth()->user()->currentOrganization;
        }

        if (!$organization) {
            abort(403, 'No organization context');
        }

        return view('audience-segments.form', compact('organization'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->hasRole('super_admin')) {
            $organization = auth()->user()->organizations()->first();
        } else {
            $organization = auth()->user()->currentOrganization;
        }

        if (!$organization) {
            abort(403, 'No organization context');
        }

        $this->authorize('create', AudienceSegment::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'rules' => 'nullable|array',
            'rules.*.type' => 'nullable|string',
            'rules.*.value' => 'nullable',
            'is_active' => 'boolean',
        ]);

        $rules = [];
        if ($validated['rules'] ?? null) {
            foreach ($validated['rules'] as $rule) {
                if (!empty($rule['value'])) {
                    $rules[] = [
                        'type' => $rule['type'] ?? null,
                        'value' => $rule['value'],
                    ];
                }
            }
        }

        $segment = $organization->audienceSegments()->create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'rules' => $rules,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('audience-segments.index')
            ->with('success', 'Audience segment created successfully.');
    }

    public function edit(AudienceSegment $segment)
    {
        $this->authorize('update', $segment);

        if (auth()->user()->hasRole('super_admin')) {
            $organization = $segment->organization;
        } else {
            $organization = auth()->user()->currentOrganization;
        }

        return view('audience-segments.form', compact('segment', 'organization'));
    }

    public function update(Request $request, AudienceSegment $segment)
    {
        $this->authorize('update', $segment);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'rules' => 'nullable|array',
            'rules.*.type' => 'nullable|string',
            'rules.*.value' => 'nullable',
            'is_active' => 'boolean',
        ]);

        $rules = [];
        if ($validated['rules'] ?? null) {
            foreach ($validated['rules'] as $rule) {
                if (!empty($rule['value'])) {
                    $rules[] = [
                        'type' => $rule['type'] ?? null,
                        'value' => $rule['value'],
                    ];
                }
            }
        }

        $segment->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'rules' => $rules,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('audience-segments.index')
            ->with('success', 'Audience segment updated successfully.');
    }

    public function destroy(AudienceSegment $segment)
    {
        $this->authorize('delete', $segment);

        $segment->delete();

        return redirect()->route('audience-segments.index')
            ->with('success', 'Audience segment deleted successfully.');
    }
}
