<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Channel;
use App\Models\QrCode;
use App\Models\User;
use App\Models\WorkflowInstance;
use App\Services\QrCodeService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantChannelController extends Controller
{
    protected $qrCodeService;

    public function __construct(QrCodeService $qrCodeService, private WorkflowService $workflowService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function index()
    {
        $organization = auth()->user()->currentOrganization;
        if (!$organization) {
            abort(403);
        }

        $treeChannels = Channel::visibleTo(auth()->user(), $organization->id)->where('status','active')
            ->with('parentChannels:id')->withCount('users','posts')->orderBy('name')->get()->keyBy('id');
        $treeChildren = [];
        foreach ($treeChannels as $node) {
            $parent = $node->parentChannels->first(fn ($item) => $treeChannels->has($item->id));
            $treeChildren[$parent?->id ?? 0][] = $node;
        }
        return view('tenant.channels.index', compact('organization','treeChannels','treeChildren'));
    }

    public function create(Request $request)
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

        $parentOptions = Channel::visibleTo(auth()->user(), $organization->id)->where('status','active')->orderBy('name')->get()->filter(fn ($item) => $item->canAdminister(auth()->user()));
        abort_unless(Channel::isOrgManager(auth()->user(),$organization->id) || $parentOptions->isNotEmpty(),403);

        // Deep-link from the channels index "Add Sub-Channel" button —
        // pre-selects the parent so the admin doesn't have to find it again.
        $preselectedParentId = $parentOptions->contains('id', (int) $request->query('parent'))
            ? (int) $request->query('parent')
            : null;

        return view('tenant.channels.create', compact('organization', 'parentOptions', 'preselectedParentId'));
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
            'booking_enabled' => 'nullable|boolean',
            'booking_property_id' => 'nullable|integer',
            'type' => 'required|in:public,private',
            'parent_channel_id' => [
                'nullable',
                'integer',
                \Illuminate\Validation\Rule::exists('channels', 'id')->where('organization_id', $organization->id),
            ],
        ] + \App\Support\Designs::channelRules());

        $parent = !empty($validated['parent_channel_id']) ? Channel::where('organization_id',$organization->id)->findOrFail($validated['parent_channel_id']) : null;
        abort_unless(Channel::isOrgManager(auth()->user(),$organization->id) || ($parent && $parent->canAdminister(auth()->user())),403);
        $design = \App\Support\Designs::channelAttributes($validated);
        $design['design_options'] = app(\App\Services\HotelPropertyService::class)->configure($organization,$request->user(),$request->boolean('booking_enabled'),$request->integer('booking_property_id') ?: null,$design['design_options'] ?? [],$parent);
        $channel = Channel::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(6),
            'type' => $validated['type'],
            'audience_profile' => $validated['type'] === 'public' ? 'public' : 'business',
            'organization_id' => $organization->id,
            'status' => 'active',
            'admin_user_id' => auth()->id(),
        ] + $design);

        if (!empty($validated['parent_channel_id'])) {
            $channel->parentChannels()->sync([$validated['parent_channel_id']]);
        }

        // Followers of the organization join new main channels silently (docs/27).
        app(\App\Services\SubscriptionService::class)->syncFollowers($channel->fresh());

        // Generate QR code for this channel. No explicit $url here — generate()
        // builds it from its own freshly-generated $code, which is what keeps
        // the encoded link and the QrCode.code column (used by qr.redirect
        // to look the row back up) in sync.
        try {
            $this->qrCodeService->generate($channel, $organization, "Channel: {$channel->name}");
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
        // Only managers and the channel's own members may open it (and see its members).
        abort_unless($channel->membersVisibleTo(auth()->user()), 403);

        $stats = [
            'members' => $channel->users()->wherePivot('status', 'approved')->count(),
            'posts' => $channel->feedQuery()->count(),
            'qr_codes' => $channel->qrCodes()->count(),
        ];

        $members = $channel->users()->wherePivot('status', 'approved')->orderByDesc('channel_user.joined_at')->get();
        $pendingMembers = $channel->users()->wherePivot('status', 'pending')->get();

        $qrCodes = $channel->qrCodes()->latest()->get()->map(function ($qrCode) {
            $qrCode->preview_image = $this->qrCodeService->generateImage($qrCode, 200);
            return $qrCode;
        });

        return view('tenant.channels.show', compact('channel', 'organization', 'stats', 'members', 'pendingMembers', 'qrCodes'));
    }

    public function edit(Channel $channel)
    {
        $organization = auth()->user()->currentOrganization;
        if (!$organization || $channel->organization_id !== $organization->id) {
            abort(403);
        }
        abort_unless($channel->canAdminister(auth()->user()), 403);

        $parentOptions = Channel::visibleTo(auth()->user(), $organization->id)
            ->whereNotIn('id', array_merge([$channel->id],Channel::descendantIdsOf([$channel->id])))
            ->orderBy('name')
            ->get()->filter(fn ($item) => $item->canAdminister(auth()->user()));
        $currentParentId = $channel->parentChannels()->first()?->id;

        return view('tenant.channels.edit', compact('channel', 'organization', 'parentOptions', 'currentParentId'));
    }

    public function update(Request $request, Channel $channel)
    {
        $organization = auth()->user()->currentOrganization;
        if (!$organization || $channel->organization_id !== $organization->id) {
            abort(403);
        }
        abort_unless($channel->canAdminister(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'booking_enabled' => 'nullable|boolean',
            'booking_property_id' => 'nullable|integer',
            'type' => 'required|in:public,private',
            'parent_channel_id' => [
                'nullable',
                'integer',
                \Illuminate\Validation\Rule::exists('channels', 'id')->where('organization_id', $organization->id),
                \Illuminate\Validation\Rule::notIn(array_merge([$channel->id],Channel::descendantIdsOf([$channel->id]))),
            ],
        ] + \App\Support\Designs::channelRules());

        $parent = !empty($validated['parent_channel_id']) ? Channel::where('organization_id',$organization->id)->findOrFail($validated['parent_channel_id']) : null;
        abort_unless(Channel::isOrgManager(auth()->user(),$organization->id) || ($parent && $parent->canAdminister(auth()->user())) || (!$parent && !$channel->parentChannels()->exists()),403);
        $design = \App\Support\Designs::channelAttributes($validated,$channel);
        $design['design_options'] = app(\App\Services\HotelPropertyService::class)->configure($organization,$request->user(),$request->boolean('booking_enabled'),$request->integer('booking_property_id') ?: null,$design['design_options'] ?? [],$channel);
        $channel->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
        ] + $design);

        $channel->parentChannels()->sync(
            !empty($validated['parent_channel_id']) ? [$validated['parent_channel_id']] : []
        );

        // Followers of the organization join new main channels silently (docs/27).
        app(\App\Services\SubscriptionService::class)->syncFollowers($channel->fresh());

        return redirect()->route('tenant.channels.show', $channel->id)
            ->with('success', __('Channel updated successfully'));
    }

    public function destroy(Channel $channel)
    {
        $organization = auth()->user()->currentOrganization;
        if (!$organization || $channel->organization_id !== $organization->id) {
            abort(403);
        }
        abort_unless($channel->canAdminister(auth()->user()), 403);

        $channel->delete();

        return redirect()->route('tenant.channels.index')
            ->with('success', __('Channel deleted successfully'));
    }

    /**
     * Pause (archive) or resume (activate) a channel without deleting it.
     */
    public function toggleStatus(Channel $channel)
    {
        $organization = auth()->user()->currentOrganization;
        if (!$organization || $channel->organization_id !== $organization->id) {
            abort(403);
        }
        abort_unless($channel->canAdminister(auth()->user()), 403);


        $channel->update([
            'status' => $channel->status === 'active' ? 'archived' : 'active',
        ]);
        app(\App\Services\SubscriptionService::class)->syncFollowers($channel);

        return back()->with('success', $channel->status === 'active'
            ? __('Channel resumed.')
            : __('Channel paused.'));
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
            'can_post' => 'nullable|boolean',
            'can_edit' => 'nullable|boolean',
            'can_delete' => 'nullable|boolean',
        ]);
        $pivot = ['role' => $validated['role']] + $this->permissionsFrom($request);

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
            $channel->users()->updateExistingPivot($user->id, $pivot);
        } else {
            $channel->users()->attach($user->id, $pivot + ['joined_at' => now()]);
        }

        return back()->with('success', __(':name was added to the channel.', ['name' => $user->full_name]));
    }

    public function updateMemberRole(Request $request, Channel $channel, User $user)
    {
        $this->authorizeChannelAccess($channel);

        $validated = $request->validate([
            'role' => 'required|in:member,moderator,admin',
            'can_post' => 'nullable|boolean',
            'can_edit' => 'nullable|boolean',
            'can_delete' => 'nullable|boolean',
        ]);

        $channel->users()->updateExistingPivot($user->id, ['role' => $validated['role']] + $this->permissionsFrom($request));

        return back()->with('success', __('Role and permissions updated.'));
    }

    /** Each permission is independent of the role label (ADR-014). */
    private function permissionsFrom(Request $request): array
    {
        return [
            'can_post' => $request->boolean('can_post'),
            'can_edit' => $request->boolean('can_edit'),
            'can_delete' => $request->boolean('can_delete'),
        ];
    }

    /** How posts are presented in this channel (inherited by sub-channels without their own settings). */
    public function updatePostSettings(Request $request, Channel $channel)
    {
        $this->authorizeChannelAccess($channel);

        $validated = $request->validate([
            'inherit' => 'nullable|boolean',
            'layout' => 'required_unless:inherit,1|in:list,grid,compact',
            'card_style' => ['required_unless:inherit,1', \Illuminate\Validation\Rule::in(array_merge(['post'], array_keys(config('posts.card_styles'))))],
            'excerpt' => 'nullable|integer|min:60|max:1000',
        ]);

        $options = $channel->design_options ?? [];
        if ($request->boolean('inherit')) {
            unset($options['posts']);
        } else {
            $options['posts'] = [
                'layout' => $validated['layout'],
                'card_style' => $validated['card_style'],
                'excerpt' => (int) ($validated['excerpt'] ?? 280),
                'show_author' => $request->boolean('show_author'),
                'show_date' => $request->boolean('show_date'),
                'show_views' => $request->boolean('show_views'),
                'show_reactions' => $request->boolean('show_reactions'),
                'show_comments' => $request->boolean('show_comments'),
            ];
        }

        $channel->update(['design_options' => $options ?: null]);

        return back()->with('success', __('Post display settings saved.'));
    }

    public function removeMember(Channel $channel, User $user)
    {
        $this->authorizeChannelAccess($channel);

        $channel->users()->detach($user->id);

        return back()->with('success', __('Member removed from channel.'));
    }

    public function approveJoinRequest(Channel $channel, User $user)
    {
        $this->authorizeChannelAccess($channel);

        $instance = $this->pendingJoinInstance($channel, $user);

        if ($instance) {
            $this->workflowService->approve($instance, auth()->user());
        }

        $channel->users()->updateExistingPivot($user->id, ['status' => 'approved']);

        return back()->with('success', __(':name was approved to join the channel.', ['name' => $user->full_name]));
    }

    public function rejectJoinRequest(Channel $channel, User $user)
    {
        $this->authorizeChannelAccess($channel);

        $instance = $this->pendingJoinInstance($channel, $user);

        if ($instance) {
            $this->workflowService->reject($instance, auth()->user());
        }

        $channel->users()->detach($user->id);

        return back()->with('success', __('Join request rejected.'));
    }

    public function generateQrCode(Request $request, Channel $channel)
    {
        $this->authorizeChannelAccess($channel);

        $validated = $request->validate([
            'label' => 'nullable|string|max:120',
        ]);

        $this->qrCodeService->generate(
            $channel,
            $channel->organization,
            $validated['label'] ?? "QR: {$channel->name}"
        );

        return back()->with('success', __('QR code generated.'));
    }

    public function downloadQrCode(Channel $channel, \App\Models\QrCode $qrCode)
    {
        $this->authorizeChannelAccess($channel);
        abort_unless($qrCode->ownable_type === Channel::class && $qrCode->ownable_id === $channel->id, 404);

        $image = base64_decode($this->qrCodeService->generateImage($qrCode, 600));
        $filename = Str::slug($channel->name) . '-qr-' . $qrCode->id . '.svg';

        return response($image, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function toggleQrCode(Channel $channel, \App\Models\QrCode $qrCode)
    {
        $this->authorizeChannelAccess($channel);
        abort_unless($qrCode->ownable_type === Channel::class && $qrCode->ownable_id === $channel->id, 404);

        $wasActive = $qrCode->is_active;
        $wasActive ? $this->qrCodeService->deactivate($qrCode) : $this->qrCodeService->reactivate($qrCode);

        return back()->with('success', $wasActive ? __('QR code deactivated.') : __('QR code reactivated.'));
    }

    public function destroyQrCode(Channel $channel, \App\Models\QrCode $qrCode)
    {
        $this->authorizeChannelAccess($channel);
        abort_unless($qrCode->ownable_type === Channel::class && $qrCode->ownable_id === $channel->id, 404);

        $this->qrCodeService->delete($qrCode);

        return back()->with('success', __('QR code deleted.'));
    }

    public function updateQrWelcomeMessage(Request $request, Channel $channel, \App\Models\QrCode $qrCode)
    {
        $this->authorizeChannelAccess($channel);
        abort_unless($qrCode->ownable_type === Channel::class && $qrCode->ownable_id === $channel->id, 404);

        $validated = $request->validate([
            'welcome_message' => 'nullable|string|max:500',
        ]);

        $qrCode->update([
            'metadata' => array_merge($qrCode->metadata ?? [], [
                'welcome_message' => $validated['welcome_message'] ?? null,
            ]),
        ]);

        return back()->with('success', __('Welcome message saved.'));
    }

    public function printQrCode(Channel $channel, \App\Models\QrCode $qrCode)
    {
        $this->authorizeChannelAccess($channel);
        abort_unless($qrCode->ownable_type === Channel::class && $qrCode->ownable_id === $channel->id, 404);

        $qrImage = $this->qrCodeService->generateImage($qrCode, 500);

        return view('tenant.channels.qr-print', compact('channel', 'qrCode', 'qrImage'));
    }

    private function pendingJoinInstance(Channel $channel, User $user): ?WorkflowInstance
    {
        return WorkflowInstance::where('workflowable_type', Channel::class)
            ->where('workflowable_id', $channel->id)
            ->where('requested_by', $user->id)
            ->where('status', 'pending')
            ->latest('id')
            ->first();
    }

    private function authorizeChannelAccess(Channel $channel): void
    {
        $organization = auth()->user()->currentOrganization;
        abort_unless($organization && $channel->organization_id === $organization->id, 403);
        abort_unless($channel->canAdminister(auth()->user()), 403);
    }
}
