<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Generate profile picture URL (uses route to bypass storage symlink issues).
     */
    private function getProfilePictureUrl(?string $filename): ?string
    {
        if (!$filename) {
            return null;
        }
        return url('/profile-pictures/' . $filename);
    }

    /**
     * Get current user profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['customer', 'agent.wallet']);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_picture' => $this->getProfilePictureUrl($user->profile_picture),
                'role' => $user->role->value ?? 'customer',
                'created_at' => $user->created_at,
            ],
        ]);
    }

    /**
     * Update user name.
     */
    public function updateName(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:2',
        ]);

        $user = $request->user();
        $user->update(['name' => $validated['name']]);

        return response()->json([
            'success' => true,
            'message' => 'Jina limebadilishwa kikamilifu',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_picture' => $this->getProfilePictureUrl($user->profile_picture),
            ],
        ]);
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        // Check current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Nenosiri la sasa si sahihi',
                'errors' => [
                    'current_password' => ['Nenosiri la sasa si sahihi'],
                ],
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nenosiri limebadilishwa kikamilifu',
        ]);
    }

    /**
     * Upload profile picture.
     */
    public function uploadProfilePicture(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB
        ]);

        $user = $request->user();

        // Delete old profile picture if exists
        if ($user->profile_picture) {
            Storage::disk('public')->delete('profile_pictures/' . $user->profile_picture);
        }

        // Store new profile picture
        $file = $request->file('profile_picture');
        $filename = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        
        $stored = $file->storeAs('profile_pictures', $filename, 'public');

        if (!$stored) {
            return response()->json([
                'success' => false,
                'message' => 'Imeshindikana kupakia picha. Tafadhali jaribu tena.',
            ], 500);
        }

        // Update user
        $user->update(['profile_picture' => $filename]);
        $user->refresh(); // Refresh to get updated data

        $profilePictureUrl = $this->getProfilePictureUrl($filename);

        return response()->json([
            'success' => true,
            'message' => 'Picha ya wasifu imepakiwa kikamilifu',
            'profile_picture_url' => $profilePictureUrl,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_picture' => $profilePictureUrl,
                'role' => $user->role->value ?? 'customer',
            ],
        ]);
    }

    /**
     * Delete profile picture.
     */
    public function deleteProfilePicture(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->profile_picture) {
            Storage::disk('public')->delete('profile_pictures/' . $user->profile_picture);
            $user->update(['profile_picture' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Picha ya wasifu imefutwa kikamilifu',
        ]);
    }

    /**
     * Update full profile (name, phone, etc).
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|min:2',
            'phone' => 'sometimes|string|max:20',
        ]);

        $user = $request->user();
        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Wasifu umebadilishwa kikamilifu',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_picture' => $this->getProfilePictureUrl($user->profile_picture),
                'role' => $user->role->value ?? 'customer',
            ],
        ]);
    }
}
