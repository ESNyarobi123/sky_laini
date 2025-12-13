<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Models\Wallet;
use App\Services\ReferralService;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    protected ReferralService $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|unique:users',
            'role' => 'required|in:customer,agent',
            'password' => 'required|string|min:8|confirmed',
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
            Customer::create([
                'user_id' => $user->id,
                'phone' => $validated['phone'],
            ]);
        } elseif ($user->isAgent()) {
            $agent = \App\Models\Agent::create([
                'user_id' => $user->id,
                'phone' => $validated['phone'],
                'nida_number' => 'TEMP-' . uniqid(),
                'is_verified' => false,
                'is_online' => false,
            ]);

            // Create Wallet for agent
            Wallet::create([
                'agent_id' => $agent->id,
                'balance' => 0,
                'pending_balance' => 0,
            ]);
        }

        // Handle Referral Code if provided
        if (!empty($validated['referral_code'])) {
            $referral = $this->referralService->applyReferralCode($user, $validated['referral_code']);
            
            if ($referral) {
                $message = $user->isCustomer() 
                    ? "Karibu! Umepata TSh " . number_format($referral->discount_amount) . " discount kwenye order yako ya kwanza!"
                    : "Karibu! Utapata TSh " . number_format($referral->discount_amount) . " bonus ukikamilisha kazi yako ya kwanza!";
                
                session()->flash('referral_success', $message);
            }
        }

        Auth::login($user);

        // Redirect based on role
        if ($user->isCustomer()) {
            return redirect()->route('customer.dashboard');
        } elseif ($user->isAgent()) {
            return redirect()->route('agent.dashboard');
        }

        return redirect('/');
    }
}

