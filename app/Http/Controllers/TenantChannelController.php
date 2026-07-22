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

        $channels = Channel::where('organization_id', $organization->id)
            ->with('brand')
            ->withCount('users', 'posts')
            ->paginate(10);

        // Every channel should have one QR to display/print here; backfill any
        // that predate QR generation or whose creation-time generation failed.
        foreach ($channels as $channel) {
            $qrCode = $channel->qrCodes()->latest()->first();

            if (!$qrCode) {
                $qrCode = $this->qrCodeService->generate($channel, $organization, "QR: {$channel->name}");
            }

            $qrCode->preview_image = $this->qrCodeService->generateImage($qrCode, 180);
            $channel->primaryQrCode = $qrCode;
        }

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

        $stats = [
            'members' => $channel->users()->wherePivot('status', 'approved')->count(),
            'posts' => $channel->posts()->count(),
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
     * Pause (archive) or resume (activate) a channel without deleting it.
     */
    public function toggleStatus(Channel $channel)
    {
        $organization = auth()->user()->currentOrganization;
        if (!$organization || $channel->organization_id !== $organization->id) {
            abort(403);
        }

        $channel->update([
            'status' => $channel->status === 'active' ? 'archived' : 'active',
        ]);

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
    }
}
