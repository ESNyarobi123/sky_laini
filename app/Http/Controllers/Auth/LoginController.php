<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $input = $request->input('email');
        $fieldType = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $request->validate([
            'email' => 'required|string',
            'password' => 'required',
        ]);

        $credentials = [
            $fieldType => $input,
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Redirect based on role
            if ($user->isCustomer()) {
                return redirect()->route('customer.dashboard');
            } elseif ($user->isAgent()) {
                return redirect()->route('agent.dashboard');
            } elseif ($user->isAdmin()) {
                return redirect('/admin/dashboard');
            }

            return redirect('/');
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
