<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Repositories\Contracts\ChannelRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ChannelController extends Controller
{
    protected $channelRepository;

    public function __construct(ChannelRepositoryInterface $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    public function index($organization): View
    {
        $query = Channel::where('organization_id', $organization);

        // Search by name
        if (request('q')) {
            $query->where('name', 'like', '%' . request('q') . '%');
        }

        // Filter by type (public/private)
        if (request('type')) {
            $query->where('type', request('type'));
        }

        $channels = $query->paginate(15);
        return view('channels.index', compact('organization', 'channels'));
    }

    public function create($organization): View
    {
        return view('channels.create', compact('organization'));
    }

    public function store(Request $request, $organization): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:120|unique:channels',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:public,private',
        ]);

        $validated['admin_user_id'] = auth()->id();
        $validated['organization_id'] = $organization;
        $validated['slug'] = Str::slug($request->name) . '-' . Str::random(6);

        $this->channelRepository->create($validated);

        return redirect()->route('dashboard.channels.index', $organization)
            ->with('success', 'Channel created successfully');
    }

    public function show($organization, $channelId): View
    {
        $channel = Channel::with(['users', 'admin'])
            ->withCount(['users', 'posts'])
            ->findOrFail($channelId);

        $posts = $channel->posts()
            ->where('status', 'published')
            ->with(['author', 'organization', 'reactions', 'comments'])
            ->latest('published_at')
            ->paginate(10);

        return view('channels.show', compact('channel', 'organization', 'posts'));
    }

    public function edit($organization, $channelId): View
    {
        $channel = Channel::findOrFail($channelId);
        $this->authorize('update', $channel);
        return view('channels.edit', compact('channel', 'organization'));
    }

    public function update(Request $request, $organization, $channelId): RedirectResponse
    {
        $channel = Channel::findOrFail($channelId);
        $this->authorize('update', $channel);

        $validated = $request->validate([
            'name' => 'required|string|min:3|max:120|unique:channels,name,' . $channel->id,
            'type' => 'required|in:public,private',
            'audience_profile' => 'required|in:business,public,private',
            'audience_count' => 'nullable|integer|min:1|max:100000',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('channels/logos', 'public');
        }

        $this->channelRepository->update($channel, $validated);

        return redirect()->route('dashboard.channels.show', [$organization, $channel->id])
            ->with('success', 'Channel updated successfully');
    }

    public function destroy($organization, $channelId): RedirectResponse
    {
        $channel = Channel::findOrFail($channelId);
        $this->authorize('delete', $channel);
        $this->channelRepository->delete($channel);

        return redirect()->route('dashboard.home', $organization)
            ->with('success', 'Channel deleted successfully');
    }

    public function subscribe($organization, $channelId): RedirectResponse
    {
        $channel = Channel::findOrFail($channelId);

        if (!auth()->user()->subscribedChannels()->where('channel_id', $channel->id)->exists()) {
            auth()->user()->subscribedChannels()->attach($channel->id);
            $message = 'Successfully subscribed to ' . $channel->name;
        } else {
            $message = 'You are already subscribed to ' . $channel->name;
        }

        return redirect()->route('dashboard.channels.show', [$organization, $channelId])
            ->with('success', $message);
    }

    public function unsubscribe($organization, $channelId): RedirectResponse
    {
        $channel = Channel::findOrFail($channelId);
        auth()->user()->subscribedChannels()->detach($channel->id);

        return redirect()->route('dashboard.channels.show', [$organization, $channelId])
            ->with('success', 'Successfully unsubscribed from ' . $channel->name);
    }

    public function toggleNotifications($organization, $channelId): RedirectResponse
    {
        $channel = Channel::findOrFail($channelId);
        $user = auth()->user();

        // Check if user is subscribed
        $subscription = $user->subscribedChannels()
            ->where('channel_id', $channel->id)
            ->first();

        if ($subscription) {
            $notificationsMuted = $subscription->pivot->muted_at !== null;
            $pivot = $subscription->pivot;
            $pivot->muted_at = $notificationsMuted ? null : now();
            $pivot->save();

            $message = $notificationsMuted
                ? 'Notifications enabled for ' . $channel->name
                : 'Notifications disabled for ' . $channel->name;
        } else {
            $message = 'You are not subscribed to ' . $channel->name;
        }

        return redirect()->route('dashboard.channels.show', [$organization, $channelId])
            ->with('success', $message);
    }
}
