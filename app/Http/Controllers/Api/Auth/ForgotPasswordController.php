<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->email;
        $otpCode = rand(100000, 999999);
        
        // Delete existing OTPs for this email
        Otp::where('email', $email)->delete();

        // Create new OTP
        Otp::create([
            'email' => $email,
            'otp' => $otpCode,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        \Illuminate\Support\Facades\Log::info('Generated OTP for ' . $email . ': ' . $otpCode);

        // Send Email
        try {
            \Illuminate\Support\Facades\Log::info('Attempting to send OTP email to: ' . $email);
            \Illuminate\Support\Facades\Log::info('Mail Config:', [
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'username' => config('mail.mailers.smtp.username'),
                'from' => config('mail.from'),
            ]);

            Mail::to($email)->send(new OtpMail($otpCode));
            
            \Illuminate\Support\Facades\Log::info('OTP email sent successfully to: ' . $email);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Mail Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send email: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'OTP sent successfully to your email.']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string',
        ]);

        $otpRecord = Otp::where('email', $request->email)
                        ->where('otp', $request->otp)
                        ->first();

        if (!$otpRecord) {
            return response()->json(['message' => 'Invalid OTP.'], 400);
        }

        if (Carbon::now()->isAfter($otpRecord->expires_at)) {
            return response()->json(['message' => 'OTP has expired.'], 400);
        }

        return response()->json(['message' => 'OTP verified successfully.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $otpRecord = Otp::where('email', $request->email)
                        ->where('otp', $request->otp)
                        ->first();

        if (!$otpRecord) {
            return response()->json(['message' => 'Invalid OTP.'], 400);
        }

        if (Carbon::now()->isAfter($otpRecord->expires_at)) {
            return response()->json(['message' => 'OTP has expired.'], 400);
        }

        // Update Password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete OTP
        $otpRecord->delete();

        return response()->json(['message' => 'Password reset successfully.']);
    }
}
