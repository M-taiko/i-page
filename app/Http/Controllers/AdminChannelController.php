<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Layer 1: Super Admin creates/manages channels for ANY organization,
 * scoped to one of that organization's brands.
 */
class AdminChannelController extends Controller
{
    public function create(Organization $organization)
    {
        $brands = $organization->brands()->where('is_active', true)->get();

        return view('admin.channels.form', compact('organization', 'brands'));
    }

    public function store(Request $request, Organization $organization)
    {
        $validated = $this->validateChannel($request, $organization);

        $organization->channels()->create([
            'brand_id' => $validated['brand_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(6),
            'type' => $validated['type'],
            'status' => 'active',
            'admin_user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.organizations.show', $organization)
            ->with('success', 'Channel created successfully.');
    }

    public function edit(Organization $organization, Channel $channel)
    {
        abort_unless($channel->organization_id === $organization->id, 404);

        $brands = $organization->brands()->where('is_active', true)->get();

        return view('admin.channels.form', compact('organization', 'channel', 'brands'));
    }

    public function update(Request $request, Organization $organization, Channel $channel)
    {
        abort_unless($channel->organization_id === $organization->id, 404);

        $validated = $this->validateChannel($request, $organization);

        $channel->update([
            'brand_id' => $validated['brand_id'],
            'name' => $validated['name'],
            'type' => $validated['type'],
        ]);

        return redirect()->route('admin.organizations.show', $organization)
            ->with('success', 'Channel updated successfully.');
    }

    public function destroy(Organization $organization, Channel $channel)
    {
        abort_unless($channel->organization_id === $organization->id, 404);

        $channel->delete();

        return redirect()->route('admin.organizations.show', $organization)
            ->with('success', 'Channel deleted successfully.');
    }

    private function validateChannel(Request $request, Organization $organization): array
    {
        return $request->validate([
            'brand_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('brands', 'id')->where('organization_id', $organization->id),
            ],
            'name' => 'required|string|max:255',
            'type' => 'required|in:public,private',
        ]);
    }
}
