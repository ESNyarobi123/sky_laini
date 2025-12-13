<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user (Customer or Agent).
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:customer,agent',
            'referral_code' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => UserRole::from($validated['role']),
        ]);

        // Create customer or agent profile
        if ($user->isCustomer()) {
            $user->customer()->create([
                'phone' => $validated['phone'],
            ]);
        } elseif ($user->isAgent()) {
            // Create Agent profile
            $agent = \App\Models\Agent::create([
                'user_id' => $user->id,
                'phone' => $validated['phone'],
                'nida_number' => 'TEMP-' . uniqid(),
                'is_verified' => false,
                'is_online' => false,
            ]);

            // Create Wallet for agent
            \App\Models\Wallet::create([
                'agent_id' => $agent->id,
                'balance' => 0,
                'pending_balance' => 0,
            ]);
        }

        // Handle Referral Code if provided
        $referralApplied = false;
        $referralMessage = null;
        if (!empty($validated['referral_code'])) {
            $referralService = app(\App\Services\ReferralService::class);
            $referral = $referralService->applyReferralCode($user, $validated['referral_code']);
            
            if ($referral) {
                $referralApplied = true;
                if ($user->isCustomer()) {
                    $referralMessage = "Umepata TSh " . number_format($referral->discount_amount) . " discount kwenye order yako ya kwanza!";
                } else {
                    $referralMessage = "Utapata TSh " . number_format($referral->discount_amount) . " bonus ukikamilisha kazi yako ya kwanza!";
                }
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'referral_applied' => $referralApplied,
            'referral_message' => $referralMessage,
        ], 201);
    }

    /**
     * Login user.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
