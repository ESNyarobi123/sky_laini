<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'nida_number' => 'required|string',
            'id_document' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120', // Allow PDF for ID
            'passport_photo' => 'required|image|max:5120',
        ]);

        $agent = $request->user()->agent;

        // Store Files
        $idPath = $request->file('id_document')->store('agent_documents', 'public');
        $passportPath = $request->file('passport_photo')->store('agent_documents', 'public');

        // Update Agent Profile
        $agent->update([
            'nida_number' => $request->nida_number,
            // In a real app, we'd store these paths in a separate 'agent_documents' table or JSON column
            // For now, we'll assume we have columns or just log it for the admin to see
        ]);

        // Create Document Records
        // Create Document Records
        \App\Models\AgentDocument::create([
            'agent_id' => $agent->id,
            'document_type' => 'nida_id',
            'file_path' => $idPath,
            'file_name' => $request->file('id_document')->getClientOriginalName(),
            'verification_status' => \App\VerificationStatus::Pending
        ]);

        \App\Models\AgentDocument::create([
            'agent_id' => $agent->id,
            'document_type' => 'passport_photo',
            'file_path' => $passportPath,
            'file_name' => $request->file('passport_photo')->getClientOriginalName(),
            'verification_status' => \App\VerificationStatus::Pending
        ]);

        return back()->with('success', 'Documents uploaded successfully. Please wait for verification.');
    }

    /**
     * Upload face verification images (Liveness Detection)
     */
    public function uploadFaceVerification(Request $request)
    {
        $request->validate([
            'face_center' => 'required|image|max:5120',
            'face_left' => 'required|image|max:5120',
            'face_right' => 'required|image|max:5120',
            'face_up' => 'required|image|max:5120',
            'face_down' => 'required|image|max:5120',
        ], [
            'face_center.required' => 'Picha ya katikati inahitajika',
            'face_left.required' => 'Picha ya kushoto inahitajika',
            'face_right.required' => 'Picha ya kulia inahitajika',
            'face_up.required' => 'Picha ya juu inahitajika',
            'face_down.required' => 'Picha ya chini inahitajika',
        ]);

        $agent = $request->user()->agent;

        if (!$agent) {
            return back()->with('error', 'Agent profile not found.');
        }

        // Store all face images
        $faceImages = [];
        $directions = ['center', 'left', 'right', 'up', 'down'];
        
        foreach ($directions as $direction) {
            $field = 'face_' . $direction;
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store("face_verifications/{$agent->id}", 'public');
                $faceImages[$field] = $path;
            }
        }

        // Create or update face verification record
        $verification = \App\Models\AgentFaceVerification::updateOrCreate(
            [
                'agent_id' => $agent->id,
                'status' => 'pending',
            ],
            array_merge($faceImages, [
                'device_info' => $request->header('User-Agent'),
                'ip_address' => $request->ip(),
                'metadata' => [
                    'submitted_from' => 'web',
                    'submitted_at' => now()->toISOString(),
                ],
            ])
        );

        // Notify admins about new face verification
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'type' => 'face_verification_submitted',
                'title' => 'Uthibitishaji Mpya wa Uso',
                'message' => "Agent {$agent->user->name} amewasilisha picha za uso kwa uhakiki.",
                'data' => [
                    'verification_id' => $verification->id,
                    'agent_id' => $agent->id,
                    'agent_name' => $agent->user->name,
                ],
            ]);
        }

        return back()->with('success', 'Picha za uso zimepakiwa! Tafadhali subiri uhakiki.');
    }
}

