<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
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
            \App\Models\Agent::create([
                'user_id' => $user->id,
                'phone' => $validated['phone'],
                'nida_number' => 'TEMP-' . uniqid(),
                'is_verified' => false,
                'is_online' => false,
            ]);
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
