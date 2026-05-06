<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class OtpService
{
    /**
     * Generate a random 4 or 6-digit OTP.
     */
    public function generateOtp($length = 6)
    {
        if ($length == 6) {
            return rand(100000, 999999);
        }
        return rand(1000, 9999);
    }

    /**
     * Send OTP to the user via Email.
     */
    public function sendOtp(User $user, $otp)
    {
        // Update user's OTP and expiry
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10), // 10 mins expiry
        ]);

        try {
            // Send Email
            Mail::to($user->email)->send(new OtpMail($otp, $user->name));
            
            // Log for debugging
            Log::info("OTP SENT: User ID {$user->id} ({$user->name}) received OTP: {$otp} via email.");
            return true;
        } catch (\Exception $e) {
            Log::error("FAILED TO SEND OTP EMAIL: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify the provided OTP for the user.
     */
    public function verifyOtp(User $user, $otp)
    {
        if (!$user->otp || !$user->otp_expires_at) {
            return false;
        }

        // Check if OTP matches and is not expired
        if ($user->otp === $otp && Carbon::now()->isBefore($user->otp_expires_at)) {
            // Clear OTP after successful verification
            $user->update([
                'otp' => null,
                'otp_expires_at' => null,
            ]);
            return true;
        }

        return false;
    }
}
