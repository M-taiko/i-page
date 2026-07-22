<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\QrCode;
use App\Models\User;
use App\Services\OtpService;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QrScanController extends Controller
{
    public function __construct(
        private QrCodeService $qrCodeService,
        private OtpService $otpService
    ) {
    }

    /**
     * Manual entry point: accepts either a bare QR code or a full pasted
     * share URL (e.g. https://.../qr/{code}) and forwards to the same
     * redirect() flow used by an actual camera scan.
     */
    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
        ]);

        $input = trim($validated['code']);
        $code = Str::contains($input, '/qr/')
            ? trim(Str::afterLast($input, '/qr/'), '/')
            : $input;

        if ($code === '') {
            return redirect()->route('guest.home')->with('error', __('Please enter a valid code.'));
        }

        return redirect()->route('qr.redirect', ['code' => $code]);
    }

    public function redirect(Request $request, string $code)
    {
        $qrCode = $this->qrCodeService->findByCode($code);

        if (!$qrCode) {
            return redirect('/')->with('error', __('QR code not found or expired'));
        }

        try {
            $this->qrCodeService->logScan($qrCode, $qrCode->organization_id);
        } catch (\Exception $e) {
            return redirect('/')->with('error', __('This QR code is no longer active'));
        }

        $ownable = $qrCode->ownable;

        if ($ownable instanceof Channel) {
            return $this->redirectToChannel($ownable, $request);
        }

        return redirect($qrCode->url ?? '/');
    }

    private function redirectToChannel(Channel $channel, Request $request)
    {
        $user = auth()->user();

        if ($channel->type === 'private') {
            if (!$user) {
                // So the post-login redirect (redirect()->intended()) lands
                // back on this exact QR link instead of the default feed.
                session(['url.intended' => $request->fullUrl()]);

                return view('qr.private-access', ['channel' => $channel, 'state' => 'login-required']);
            }

            if (!$channel->canBeAccessedBy($user)) {
                return view('qr.private-access', [
                    'channel' => $channel,
                    'state' => 'denied',
                    'hasPendingRequest' => $channel->hasPendingRequestFrom($user),
                ]);
            }

            session(['current_organization_id' => $channel->organization_id]);

            return redirect()->route('dashboard.channels.show', [
                $channel->organization_id,
                $channel->id,
            ]);
        }

        if (!$user) {
            // Public channels are browsable by anyone — land the guest
            // straight on the read-only public view, no signup required.
            // (The QR self-registration flow is still reachable from that
            // page's own "Subscribe" action if the guest wants to join.)
            return redirect()->route('guest.channel-detail', [
                'organization' => $channel->organization_id,
                'channelSlug' => $channel->slug,
            ]);
        }

        session(['current_organization_id' => $channel->organization_id]);

        return redirect()->route('dashboard.channels.show', [
            $channel->organization_id,
            $channel->id,
        ]);
    }

    public function guestRegisterForm(Request $request)
    {
        $channelId = $request->query('channel_id');
        $organizationId = $request->query('organization_id');

        $channel = Channel::findOrFail($channelId);

        if ($channel->organization_id != $organizationId || $channel->type === 'private') {
            abort(403);
        }

        return view('qr.guest-register', [
            'channel' => $channel,
            'organization' => $channel->organization,
        ]);
    }

    public function guestRegisterStore(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|required_without:mobile',
            'mobile' => 'nullable|string|max:20|required_without:email',
            'channel_id' => 'required|exists:channels,id',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $channel = Channel::findOrFail($validated['channel_id']);

        if ($channel->organization_id != $validated['organization_id'] || $channel->type === 'private') {
            abort(403);
        }

        $guest = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'password' => bcrypt(Str::random(32)),
            'ipage_id' => 'GUEST-' . Str::random(8),
        ]);

        $otpChannel = $validated['mobile'] ? 'sms' : 'email';
        $destination = $validated['mobile'] ?: $validated['email'];

        $this->otpService->generate($guest, $otpChannel, $destination);

        session(['otp_pending_guest' => [
            'user_id' => $guest->id,
            'channel_id' => $channel->id,
            'channel' => $otpChannel,
            'destination' => $destination,
        ]]);

        return redirect()->route('otp.verify')
            ->with('success', __('We sent you a verification code.'));
    }
}
