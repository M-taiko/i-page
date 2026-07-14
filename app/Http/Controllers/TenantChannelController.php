<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Channel;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantChannelController extends Controller
{
    protected $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function index()
    {
        $organization = auth()->user()->currentOrganization;
        if (!$organization) {
            abort(403);
        }

        $channels = Channel::where('organization_id', $organization->id)
            ->with('brand')
            ->withCount('users', 'posts')
            ->paginate(10);

        return view('tenant.channels.index', compact('organization', 'channels'));
    }

    public function create()
    {
        $organization = auth()->user()->currentOrganization;
        if (!$organization) {
            abort(403);
        }

        $currentCount = Channel::where('organization_id', $organization->id)->count();
        if ($currentCount >= $organization->max_channels) {
            return redirect()->route('tenant.channels.index')
                ->with('error', __('You have reached the maximum number of channels'));
        }

        $brands = $organization->brands()->where('is_active', true)->get();

        return view('tenant.channels.create', compact('organization', 'brands'));
    }

    public function store(Request $request)
    {
        $organization = auth()->user()->currentOrganization;
        if (!$organization) {
            abort(403);
        }

        $currentCount = Channel::where('organization_id', $organization->id)->count();
        if ($currentCount >= $organization->max_channels) {
            return redirect()->route('tenant.channels.index')
                ->with('error', __('You have reached the maximum number of channels'));
        }

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'type' => 'required|in:public,private',
            'brand_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('brands', 'id')->where('organization_id', $organization->id),
            ],
        ]);

        $channel = Channel::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(6),
            'type' => $validated['type'],
            'brand_id' => $validated['brand_id'],
            'organization_id' => $organization->id,
            'status' => 'active',
            'admin_user_id' => auth()->id(),
        ]);

        // Generate QR code for this channel
        try {
            $this->qrCodeService->generate(
                $channel,
                $organization,
                "Channel: {$channel->name}",
                route('qr.redirect', ['code' => Str::random(32)])
            );
        } catch (\Exception $e) {
            // QR generation is optional
        }

        return redirect()->route('tenant.channels.show', $channel->id)
            ->with('success', __('Channel created successfully'));
    }

    public function show(Channel $channel)
    {
        $organization = auth()->user()->currentOrganization;
        if (!$organization || $channel->organization_id !== $organization->id) {
            abort(403);
        }

        $stats = [
            'members' => $channel->users()->count(),
            'posts' => $channel->posts()->count(),
            'qr_codes' => $channel->qrCodes()->count(),
        ];

        $members = $channel->users()->orderByDesc('channel_user.joined_at')->get();

        return view('tenant.channels.show', compact('channel', 'organization', 'stats', 'members'));
    }

    public function edit(Channel $channel)
    {
        $organization = auth()->user()->currentOrganization;
        if (!$organization || $channel->organization_id !== $organization->id) {
            abort(403);
        }

        $brands = $organization->brands()->where('is_active', true)->get();

        return view('tenant.channels.edit', compact('channel', 'organization', 'brands'));
    }

    public function update(Request $request, Channel $channel)
    {
        $organization = auth()->user()->currentOrganization;
        if (!$organization || $channel->organization_id !== $organization->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'type' => 'required|in:public,private',
            'brand_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('brands', 'id')->where('organization_id', $organization->id),
            ],
        ]);

        $channel->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'brand_id' => $validated['brand_id'],
        ]);

        return redirect()->route('tenant.channels.show', $channel->id)
            ->with('success', __('Channel updated successfully'));
    }

    public function destroy(Channel $channel)
    {
        $organization = auth()->user()->currentOrganization;
        if (!$organization || $channel->organization_id !== $organization->id) {
            abort(403);
        }

        $channel->delete();

        return redirect()->route('tenant.channels.index')
            ->with('success', __('Channel deleted successfully'));
    }

    /**
     * Invite a user (registered or not) into this channel with a role.
     * If the email doesn't belong to an existing user, an account is created for them.
     */
    public function inviteMember(Request $request, Channel $channel)
    {
        $this->authorizeChannelAccess($channel);

        $validated = $request->validate([
            'email' => 'required|email|max:180',
            'first_name' => 'nullable|string|max:80',
            'last_name' => 'nullable|string|max:80',
            'role' => 'required|in:member,moderator,admin',
        ]);

        $user = User::firstOrCreate(
            ['email' => $validated['email']],
            [
                'first_name' => ($validated['first_name'] ?? null) ?: Str::before($validated['email'], '@'),
                'last_name' => ($validated['last_name'] ?? null) ?: '',
                'ipage_id' => 'IP' . str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT),
                'password' => bcrypt(Str::random(16)),
                'email_verified_at' => now(),
            ]
        );

        if ($channel->users()->where('user_id', $user->id)->exists()) {
            $channel->users()->updateExistingPivot($user->id, ['role' => $validated['role']]);
        } else {
            $channel->users()->attach($user->id, ['role' => $validated['role'], 'joined_at' => now()]);
        }

        return back()->with('success', __(':name was added to the channel.', ['name' => $user->full_name]));
    }

    public function updateMemberRole(Request $request, Channel $channel, User $user)
    {
        $this->authorizeChannelAccess($channel);

        $validated = $request->validate([
            'role' => 'required|in:member,moderator,admin',
        ]);

        $channel->users()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        return back()->with('success', __('Role updated.'));
    }

    public function removeMember(Channel $channel, User $user)
    {
        $this->authorizeChannelAccess($channel);

        $channel->users()->detach($user->id);

        return back()->with('success', __('Member removed from channel.'));
    }

    private function authorizeChannelAccess(Channel $channel): void
    {
        $organization = auth()->user()->currentOrganization;
        abort_unless($organization && $channel->organization_id === $organization->id, 403);
    }
}
