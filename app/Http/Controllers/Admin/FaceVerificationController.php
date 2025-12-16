<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentFaceVerification;
use Illuminate\Http\Request;

class FaceVerificationController extends Controller
{
    /**
     * Display pending face verifications.
     */
    public function index()
    {
        $pendingVerifications = AgentFaceVerification::with(['agent.user'])
            ->where('status', 'pending')
            ->whereNotNull('face_center')
            ->whereNotNull('face_left')
            ->whereNotNull('face_right')
            ->whereNotNull('face_up')
            ->whereNotNull('face_down')
            ->latest()
            ->paginate(10);

        return view('admin.face-verification.index', compact('pendingVerifications'));
    }

    /**
     * Show a specific face verification.
     */
    public function show(AgentFaceVerification $verification)
    {
        $verification->load(['agent.user', 'agent.documents', 'verifiedBy']);
        
        return view('admin.face-verification.show', compact('verification'));
    }

    /**
     * Approve face verification.
     */
    public function approve(AgentFaceVerification $verification)
    {
        $verification->update([
            'status' => 'approved',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        // Update agent's face_verified status
        $verification->agent->update([
            'face_verified' => true,
        ]);

        // Notify the agent
        \App\Models\InAppNotification::create([
            'user_id' => $verification->agent->user_id,
            'type' => 'face_verification_approved',
            'title' => 'Uso Umehakikiwa!',
            'message' => 'Uthibitishaji wako wa uso umekubaliwa. Sasa unaweza kuendelea kutumia huduma.',
            'data' => [
                'verification_id' => $verification->id,
            ],
        ]);

        return back()->with('success', 'Uthibitishaji wa uso umekubaliwa.');
    }

    /**
     * Reject face verification.
     */
    public function reject(Request $request, AgentFaceVerification $verification)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $verification->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        // Notify the agent
        \App\Models\InAppNotification::create([
            'user_id' => $verification->agent->user_id,
            'type' => 'face_verification_rejected',
            'title' => 'Uthibitishaji wa Uso Umekataliwa',
            'message' => 'Uthibitishaji wako wa uso umekataliwa. Sababu: ' . $request->rejection_reason,
            'data' => [
                'verification_id' => $verification->id,
                'reason' => $request->rejection_reason,
            ],
        ]);

        return back()->with('success', 'Uthibitishaji wa uso umekataliwa.');
    }

    /**
     * View a face image.
     */
    public function viewImage(AgentFaceVerification $verification, string $direction)
    {
        $field = 'face_' . $direction;
        
        if (!in_array($direction, ['center', 'left', 'right', 'up', 'down'])) {
            abort(404, 'Invalid direction');
        }

        $path = storage_path('app/public/' . $verification->$field);

        if (!file_exists($path)) {
            abort(404, 'Image not found');
        }

        $mimeType = mime_content_type($path) ?? 'image/jpeg';

        return response()->file($path, [
            'Content-Type' => $mimeType,
        ]);
    }

    /**
     * Get all face verifications history.
     */
    public function history()
    {
        $verifications = AgentFaceVerification::with(['agent.user', 'verifiedBy'])
            ->whereIn('status', ['approved', 'rejected'])
            ->latest()
            ->paginate(20);

        return view('admin.face-verification.history', compact('verifications'));
    }
}
