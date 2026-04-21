<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OtpService
{
    /**
     * Generate a 4 or 6 digit OTP.
     */
    public function generateOtp($length = 4)
    {
        if ($length == 6) {
            return rand(100000, 999999);
        }
        return rand(1000, 9999);
    }

    /**
     * Send OTP to the user (Mock version: logged to storage/logs/laravel.log).
     */
    public function sendOtp(User $user, $otp)
    {
        // For Field Staff Only
        if (!$user->hasRole('fieldstaff')) {
            return false;
        }

        $user->update([
            'otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10), // 10 mins expiry
        ]);

        // LOGGING THE OTP (Mock behavior)
        Log::info("MOCK OTP SENT: User ID {$user->id} ({$user->name}) received OTP: {$otp}");
        
        return true;
    }

    /**
     * Verify the provided OTP for a user.
     */
    public function verifyOtp(User $user, $otp)
    {
        if (!$user->otp || !$user->otp_expires_at) {
            return false;
        }

        if ($user->otp !== $otp) {
            return false;
        }

        if (Carbon::now()->isAfter($user->otp_expires_at)) {
            return false;
        }

        // Clear OTP after successful verification
        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return true;
    }
}
