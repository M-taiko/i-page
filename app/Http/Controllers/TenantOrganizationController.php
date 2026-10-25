<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Support\Designs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TenantOrganizationController extends Controller
{
    public function dashboard()
    {
        $organization = auth()->user()->currentOrganization;

        if (!$organization) {
            abort(403, 'No organization context');
        }

        $this->authorize('view', $organization);

        $days = \App\Services\DashboardStatsService::normalizeRange(request('range'));
        $stats = app(\App\Services\DashboardStatsService::class)->organization($organization, $days);

        return view('organizations.dashboard', compact('organization', 'stats', 'days'));
    }

    public function settings()
    {
        $organization = auth()->user()->currentOrganization;

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

    /** Change the public page design and its options (ADR-012). Also used by the super admin. */
    public function updateDesign(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'design_key' => 'nullable|string|max:60',
            'primary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'show_posts' => 'nullable|boolean',
            'show_stats' => 'nullable|boolean',
            'covers' => 'nullable|array|max:' . Designs::COVER_MAX_IMAGES,
            'covers.*' => 'image|max:4096',
            'existing_covers' => 'nullable|array',
            'existing_covers.*' => 'string',
            'cover_autoplay' => 'nullable|boolean',
            'cover_interval' => 'nullable|integer|min:2|max:15',
            'cover_effect' => 'nullable|in:slide,fade',
            'cover_arrows' => 'nullable|boolean',
            'cover_indicators' => 'nullable|boolean',
            'cover_height' => 'nullable|in:' . implode(',', array_keys(Designs::COVER_HEIGHTS)),
        ]);

        $designKey = $validated['design_key'] ?? null;
        if ($designKey && ! Designs::validFor($organization->template, $designKey)) {
            return back()->withErrors(['design_key' => __('The selected design does not match the organization template.')])->withInput();
        }

        // Cover images: keep the submitted existing ones (in the submitted order), append new uploads, delete the rest.
        $current = Designs::coverPaths($organization->design_options ?? []);
        $kept = array_values(array_filter($validated['existing_covers'] ?? [], fn ($path) => in_array($path, $current, true)));
        $new = [];
        foreach ($request->file('covers', []) as $file) {
            if (count($kept) + count($new) >= Designs::COVER_MAX_IMAGES) {
                break;
            }
            $new[] = $file->store('organizations/covers', 'public');
        }
        foreach (array_diff($current, $kept) as $removed) {
            Storage::disk('public')->delete($removed);
        }

        $options = Designs::cleanOptions([
            'primary_color' => $validated['primary_color'] ?? null,
            'secondary_color' => $validated['secondary_color'] ?? null,
            'cover_paths' => array_merge($kept, $new),
            'cover_autoplay' => $request->boolean('cover_autoplay'),
            'cover_interval' => $validated['cover_interval'] ?? 5,
            'cover_effect' => $validated['cover_effect'] ?? 'slide',
            'cover_arrows' => $request->boolean('cover_arrows'),
            'cover_indicators' => $request->boolean('cover_indicators'),
            'cover_height' => $validated['cover_height'] ?? 'standard',
            'show_posts' => $request->boolean('show_posts'),
            'show_stats' => $request->boolean('show_stats'),
        ]);

        $organization->update(['design_key' => $designKey, 'design_options' => $options]);

        return back()->with('success', __('Design updated successfully.'));
    }
}
