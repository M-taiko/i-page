<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OtpService
{
    private const TTL_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;

    /**
     * Generate a fresh OTP for a destination, invalidating any prior
     * unconsumed codes for that same destination.
     */
    public function generate(?User $user, string $channel, string $destination): OtpCode
    {
        OtpCode::where('destination', $destination)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        $code = (string) random_int(100000, 999999);

        $otp = OtpCode::create([
            'user_id' => $user?->id,
            'channel' => $channel,
            'destination' => $destination,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ]);

        $this->dispatch($channel, $destination, $code);

        return $otp;
    }

    public function verify(string $destination, string $code): bool
    {
        $otp = OtpCode::where('destination', $destination)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if (!$otp || $otp->isExpired() || $otp->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        if (!Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');
            return false;
        }

        $otp->update(['consumed_at' => now()]);

        return true;
    }

    /**
     * No SMS/email provider is wired up yet; log the code so the flow is
     * fully testable end-to-end until a real provider (Section 21 of the
     * enterprise review, Unified Notification Center) is integrated.
     */
    private function dispatch(string $channel, string $destination, string $code): void
    {
        Log::info("OTP code for {$channel}:{$destination} = {$code}");
    }
}
