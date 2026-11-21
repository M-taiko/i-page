<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Organization;
use App\Support\Designs;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Layer 1: Super Admin creates/manages channels for ANY organization.
 */
class AdminChannelController extends Controller
{
    public function create(Organization $organization)
    {
        $parentOptions = Channel::where('organization_id', $organization->id)->orderBy('name')->get();

        return view('admin.channels.form', compact('organization', 'parentOptions'));
    }

    public function store(Request $request, Organization $organization)
    {
        $validated = $this->validateChannel($request, $organization);

        $channel = $organization->channels()->create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(6),
            'type' => $validated['type'],
            'status' => 'active',
            'admin_user_id' => auth()->id(),
        ] + Designs::channelAttributes($validated));

        if (!empty($validated['parent_channel_id'])) {
            $channel->parentChannels()->sync([$validated['parent_channel_id']]);
        }

        // Followers of the organization join new main channels silently (docs/27).
        app(\App\Services\SubscriptionService::class)->syncFollowers($channel->fresh());

        return redirect()->route('admin.organizations.show', $organization)
            ->with('success', 'Channel created successfully.');
    }

    public function edit(Organization $organization, Channel $channel)
    {
        abort_unless($channel->organization_id === $organization->id, 404);

        $parentOptions = Channel::where('organization_id', $organization->id)
            ->where('id', '!=', $channel->id)
            ->orderBy('name')
            ->get();
        $currentParentId = $channel->parentChannels()->first()?->id;

        return view('admin.channels.form', compact('organization', 'channel', 'parentOptions', 'currentParentId'));
    }

    public function update(Request $request, Organization $organization, Channel $channel)
    {
        abort_unless($channel->organization_id === $organization->id, 404);

        $validated = $this->validateChannel($request, $organization, $channel);

        $channel->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
        ] + Designs::channelAttributes($validated, $channel));

        $channel->parentChannels()->sync(
            !empty($validated['parent_channel_id']) ? [$validated['parent_channel_id']] : []
        );

        // Followers of the organization join new main channels silently (docs/27).
        app(\App\Services\SubscriptionService::class)->syncFollowers($channel->fresh());

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

    private function validateChannel(Request $request, Organization $organization, ?Channel $channel = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:public,private',
            'parent_channel_id' => [
                'nullable',
                'integer',
                \Illuminate\Validation\Rule::exists('channels', 'id')->where('organization_id', $organization->id),
                \Illuminate\Validation\Rule::notIn($channel ? [$channel->id] : []),
            ],
        ] + Designs::channelRules());
    }
}
