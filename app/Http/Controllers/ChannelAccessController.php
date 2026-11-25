<?php

namespace App\Http\Controllers;

use App\Models\{Channel, Organization, QrCode, User};
use App\Services\{ChannelAccessService, QrCodeService};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChannelAccessController extends Controller
{
    public function __construct(private ChannelAccessService $access) {}

    public function edit(Organization $organization, User $user)
    {
        $this->access->authorizeManager(auth()->user(), $organization);
        abort_unless($organization->users()->where('users.id', $user->id)->exists(), 404);
        $channels = Channel::where('organization_id', $organization->id)->with(['parentChannels', 'users' => fn ($q) => $q->where('users.id', $user->id)])->orderBy('name')->get();
        return view('users.channel-access', compact('organization', 'user', 'channels'));
    }

    public function update(Request $request, Organization $organization, User $user)
    {
        $this->access->authorizeManager($request->user(), $organization);
        abort_unless($organization->users()->where('users.id', $user->id)->exists(), 404);
        $data = $request->validate($this->rules());
        $this->access->replace($organization, $user, $this->access->normalize($organization, $data['grants'] ?? []));
        return back()->with('success', __('Channel permissions saved.'));
    }

    public function invitations(Organization $organization)
    {
        $this->access->authorizeManager(auth()->user(), $organization);
        $channels = Channel::where('organization_id', $organization->id)->with('parentChannels')->orderBy('name')->get();
        $invitations = QrCode::where('organization_id', $organization->id)->where('metadata->kind', 'channel_access')->latest()->paginate(12);
        return view('users.channel-invitations', compact('organization', 'channels', 'invitations'));
    }

    public function storeInvitation(Request $request, Organization $organization)
    {
        $this->access->authorizeManager($request->user(), $organization);
        $data = $request->validate($this->rules() + ['label' => 'required|string|max:120', 'email' => 'nullable|email|max:180', 'days' => 'required|integer|min:1|max:30']);
        $grants = $this->access->normalize($organization, $data['grants'] ?? []);
        if (!$grants) return back()->withErrors(['grants' => __('Select at least one channel.')])->withInput();
        $code = Str::random(48);
        $invite = QrCode::create(['organization_id' => $organization->id, 'ownable_type' => Organization::class, 'ownable_id' => $organization->id,
            'code' => $code, 'label' => $data['label'], 'url' => route('channel-invitations.show', $code), 'is_active' => true,
            'expires_at' => now()->addDays($data['days']), 'metadata' => ['kind' => 'channel_access', 'created_by' => $request->user()->id, 'email' => $data['email'] ?? null, 'grants' => $grants]]);
        return redirect()->route('dashboard.channel-invitations.preview', [$organization, $invite])->with('success', __('Invitation QR created.'));
    }

    public function preview(Organization $organization, QrCode $invite, QrCodeService $qr)
    {
        $this->access->authorizeManager(auth()->user(), $organization);
        $this->checkInvitation($organization, $invite);
        $image = $qr->generateImage($invite, 320);
        return view('users.channel-invitation-preview', compact('organization', 'invite', 'image'));
    }

    public function revoke(Organization $organization, QrCode $invite)
    {
        $this->access->authorizeManager(auth()->user(), $organization);
        $this->checkInvitation($organization, $invite);
        $invite->update(['is_active' => false]);
        return back()->with('success', __('Invitation cancelled.'));
    }

    public function show(string $code)
    {
        $invite = QrCode::where('code', $code)->firstOrFail();
        abort_unless(($invite->metadata['kind'] ?? '') === 'channel_access', 404);
        $channels = Channel::where('organization_id', $invite->organization_id)->whereIn('id', array_keys($invite->metadata['grants'] ?? []))->get();
        return response()->view('users.accept-channel-invitation', compact('invite', 'channels'))->header('Referrer-Policy', 'no-referrer')->header('Cache-Control', 'no-store');
    }

    public function accept(Request $request, string $code)
    {
        $invite = QrCode::where('code', $code)->firstOrFail();
        abort_unless(($invite->metadata['kind'] ?? '') === 'channel_access' && $invite->canBeScanned(), 410);
        if (!$request->user()) {
            $request->session()->put('pending_channel_invitation', $code);
            return redirect()->route($request->input('action') === 'register' ? 'register' : 'login');
        }
        $organization = $this->access->redeem($code, $request->user());
        session(['current_organization_id' => $organization->id]);
        return redirect()->route('tenant.channels.index')->with('success', __('Invitation accepted. Your channel permissions are ready.'));
    }

    public function resume(Request $request)
    {
        $code = $request->session()->pull('pending_channel_invitation');
        if (!$code) return null;
        try {
            $organization = $this->access->redeem($code, $request->user());
            session(['current_organization_id' => $organization->id]);
            return redirect()->route('tenant.channels.index')->with('success', __('Invitation accepted. Your channel permissions are ready.'));
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return redirect()->route('channel-invitations.show', $code)->withErrors($exception->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            return redirect()->route('user.feed')->with('error', __('This invitation no longer exists.'));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
            return redirect()->route('channel-invitations.show', $code)->with('error', __('The invitation could not be accepted. It may have expired or been cancelled.'));
        }
    }

    private function rules(): array
    {
        return ['grants' => 'nullable|array|max:1000', 'grants.*' => 'array:selected,full,post,edit,delete',
            'grants.*.selected' => 'nullable|boolean', 'grants.*.full' => 'nullable|boolean',
            'grants.*.post' => 'nullable|boolean', 'grants.*.edit' => 'nullable|boolean', 'grants.*.delete' => 'nullable|boolean'];
    }

    private function checkInvitation(Organization $organization, QrCode $invite): void
    {
        abort_unless($invite->organization_id === $organization->id && ($invite->metadata['kind'] ?? '') === 'channel_access', 404);
    }
}
