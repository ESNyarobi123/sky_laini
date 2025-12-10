<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show the profile page.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load(['customer', 'agent.wallet']);

        // Get withdrawals for agent
        $withdrawals = collect();
        if ($user->isAgent() && $user->agent) {
            $withdrawals = \App\Models\Withdrawal::where('agent_id', $user->agent->id)
                ->latest()
                ->take(10)
                ->get();
        }

        // Get ratings for agent
        $ratings = collect();
        if ($user->isAgent() && $user->agent) {
            $ratings = \App\Models\Rating::where('agent_id', $user->agent->id)
                ->with(['customer.user', 'lineRequest'])
                ->latest()
                ->take(10)
                ->get();
        }

        return view('profile.index', compact('user', 'withdrawals', 'ratings'));
    }

    /**
     * Update user name.
     */
    public function updateName(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:2',
        ]);

        $request->user()->update(['name' => $validated['name']]);

        return back()->with('success', 'Jina limebadilishwa kikamilifu!');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Nenosiri la sasa si sahihi']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Nenosiri limebadilishwa kikamilifu!');
    }

    /**
     * Upload profile picture.
     */
    public function uploadPicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $user = $request->user();

        // Delete old profile picture if exists
        if ($user->profile_picture) {
            Storage::disk('public')->delete('profile_pictures/' . $user->profile_picture);
        }

        // Store new profile picture
        $file = $request->file('profile_picture');
        $filename = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        
        $file->storeAs('profile_pictures', $filename, 'public');

        // Update user
        $user->update(['profile_picture' => $filename]);

        return back()->with('success', 'Picha ya wasifu imepakiwa kikamilifu!');
    }

    /**
     * Delete profile picture.
     */
    public function deletePicture(Request $request)
    {
        $user = $request->user();

        if ($user->profile_picture) {
            Storage::disk('public')->delete('profile_pictures/' . $user->profile_picture);
            $user->update(['profile_picture' => null]);
        }

        return back()->with('success', 'Picha ya wasifu imefutwa!');
    }

    /**
     * Serve profile picture (bypasses storage symlink issues on shared hosting).
     */
    public function viewPicture($filename)
    {
        $path = 'profile_pictures/' . $filename;
        
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Profile picture not found');
        }

        $file = Storage::disk('public')->get($path);
        $mimeType = Storage::disk('public')->mimeType($path);

        return response($file, 200)->header('Content-Type', $mimeType);
    }

    /**
     * Request withdrawal (for agents).
     */
    public function requestWithdrawal(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isAgent() || !$user->agent) {
            return back()->withErrors(['error' => 'Huwezi kutoa pesa']);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1000',
            'payment_method' => 'required|in:mpesa,tigopesa,airtelmoney,bank',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
        ]);

        $agent = $user->agent;
        $wallet = $agent->wallet;

        if (!$wallet || $wallet->balance < $validated['amount']) {
            return back()->withErrors(['amount' => 'Salio haitoshi']);
        }

        // Create withdrawal
        \App\Models\Withdrawal::create([
            'agent_id' => $agent->id,
            'wallet_id' => $wallet->id,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'status' => 'pending',
        ]);

        // Deduct from wallet
        $wallet->decrement('balance', $validated['amount']);

        // Create transaction record
        $wallet->transactions()->create([
            'transaction_type' => 'debit',
            'amount' => $validated['amount'],
            'balance_before' => $wallet->balance + $validated['amount'],
            'balance_after' => $wallet->balance,
            'description' => 'Ombi la kutoa pesa',
        ]);

        return back()->with('success', 'Ombi la kutoa pesa limetumwa! Utapata pesa ndani ya masaa 24.');
    }
}
