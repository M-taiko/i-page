<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OtpController extends Controller
{
    public function __construct(private OtpService $otpService)
    {
    }

    public function show(): View|RedirectResponse
    {
        $pending = session('otp_pending_guest');

        if (!$pending) {
            return redirect()->route('guest.home');
        }

        return view('otp.verify', ['destination' => $pending['destination']]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $pending = session('otp_pending_guest');

        if (!$pending) {
            return redirect()->route('guest.home');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        if (!$this->otpService->verify($pending['destination'], $validated['code'])) {
            return back()->withErrors(['code' => __('That code is invalid or has expired.')]);
        }

        $guest = User::findOrFail($pending['user_id']);
        $channel = Channel::findOrFail($pending['channel_id']);

        $guest->forceFill([
            'email_verified_at' => now(),
            'mobile_verified_at' => now(),
        ])->save();

        $guest->channels()->syncWithoutDetaching([
            $channel->id => ['role' => 'member'],
        ]);

        auth()->login($guest);
        session()->forget('otp_pending_guest');
        session(['current_organization_id' => $channel->organization_id]);

        return redirect()->route('dashboard.channels.show', [
            $channel->organization_id,
            $channel->id,
        ])->with('success', __('Welcome! Your account is verified.'));
    }

    public function resend(): RedirectResponse
    {
        $pending = session('otp_pending_guest');

        if (!$pending) {
            return redirect()->route('guest.home');
        }

        $this->otpService->generate(
            User::find($pending['user_id']),
            $pending['channel'],
            $pending['destination']
        );

        return back()->with('success', __('A new code has been sent.'));
    }
}
