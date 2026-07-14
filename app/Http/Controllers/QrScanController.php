<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\QrCode;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QrScanController extends Controller
{
    public function __construct(private QrCodeService $qrCodeService)
    {
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

        if (!$user) {
            return redirect()->route('qr.guest-register', [
                'channel_id' => $channel->id,
                'organization_id' => $channel->organization_id,
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

        if ($channel->organization_id != $organizationId) {
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
            'email' => 'nullable|email|max:255',
            'mobile' => 'nullable|string|max:20',
            'channel_id' => 'required|exists:channels,id',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $channel = Channel::findOrFail($validated['channel_id']);

        if ($channel->organization_id != $validated['organization_id']) {
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

        $guest->channels()->attach($channel->id, ['role' => 'member']);

        auth()->login($guest);

        session(['current_organization_id' => $channel->organization_id]);

        return redirect()->route('dashboard.channels.show', [
            $channel->organization_id,
            $channel->id,
        ])->with('success', __('Welcome! You have been registered as a guest.'));
    }
}
