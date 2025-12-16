<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentFaceVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaceVerificationController extends Controller
{
    /**
     * Get face verification status and instructions.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $agent = $user->agent;

        if (!$agent) {
            return response()->json([
                'message' => 'Agent profile not found.'
            ], 404);
        }

        $faceVerification = $agent->faceVerification;

        return response()->json([
            'agent_id' => $agent->id,
            'face_verified' => $agent->face_verified ?? false,
            'verification' => $faceVerification ? [
                'id' => $faceVerification->id,
                'status' => $faceVerification->status,
                'is_complete' => $faceVerification->isComplete(),
                'completion_percentage' => $faceVerification->getCompletionPercentage(),
                'missing_images' => $faceVerification->getMissingImages(),
                'rejection_reason' => $faceVerification->rejection_reason,
                'created_at' => $faceVerification->created_at->toIso8601String(),
                'images' => [
                    'center' => $faceVerification->face_center ? url('storage/' . $faceVerification->face_center) : null,
                    'left' => $faceVerification->face_left ? url('storage/' . $faceVerification->face_left) : null,
                    'right' => $faceVerification->face_right ? url('storage/' . $faceVerification->face_right) : null,
                    'up' => $faceVerification->face_up ? url('storage/' . $faceVerification->face_up) : null,
                    'down' => $faceVerification->face_down ? url('storage/' . $faceVerification->face_down) : null,
                ],
            ] : null,
            'instructions' => [
                'sw' => [
                    'title' => 'Uthibitishaji wa Uso',
                    'description' => 'Tafadhali piga picha za uso wako kwa mwelekeo tofauti ili kuthibitisha utambulisho wako.',
                    'steps' => [
                        'center' => 'Angalia moja kwa moja kwenye kamera',
                        'left' => 'Geuza kichwa chako kushoto',
                        'right' => 'Geuza kichwa chako kulia',
                        'up' => 'Angalia juu',
                        'down' => 'Angalia chini',
                    ],
                ],
                'en' => [
                    'title' => 'Face Verification',
                    'description' => 'Please take photos of your face from different angles to verify your identity.',
                    'steps' => [
                        'center' => 'Look straight at the camera',
                        'left' => 'Turn your head to the left',
                        'right' => 'Turn your head to the right',
                        'up' => 'Look up',
                        'down' => 'Look down',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Start a new face verification session.
     */
    public function start(Request $request): JsonResponse
    {
        $user = $request->user();
        $agent = $user->agent;

        if (!$agent) {
            return response()->json([
                'message' => 'Agent profile not found.'
            ], 404);
        }

        // Check if there's already a pending verification
        $existingVerification = AgentFaceVerification::where('agent_id', $agent->id)
            ->where('status', 'pending')
            ->first();

        if ($existingVerification) {
            return response()->json([
                'message' => 'Una mchakato wa uthibitishaji unaoendelea. Tafadhali ukamilishe.',
                'verification_id' => $existingVerification->id,
                'completion_percentage' => $existingVerification->getCompletionPercentage(),
                'missing_images' => $existingVerification->getMissingImages(),
            ], 400);
        }

        // Check if already verified
        if ($agent->face_verified) {
            return response()->json([
                'message' => 'Uso wako umeshahakikiwa.',
                'face_verified' => true,
            ]);
        }

        // Create new face verification record
        $faceVerification = AgentFaceVerification::create([
            'agent_id' => $agent->id,
            'status' => 'pending',
            'device_info' => $request->header('User-Agent'),
            'ip_address' => $request->ip(),
            'metadata' => [
                'started_at' => now()->toIso8601String(),
                'platform' => $request->input('platform', 'android'),
            ],
        ]);

        return response()->json([
            'message' => 'Mchakato wa uthibitishaji umeanza. Pakia picha zako za uso.',
            'verification_id' => $faceVerification->id,
            'required_images' => ['center', 'left', 'right', 'up', 'down'],
            'next_step' => 'center',
        ], 201);
    }

    /**
     * Upload a single face image.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'direction' => 'required|in:center,left,right,up,down',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $user = $request->user();
        $agent = $user->agent;

        if (!$agent) {
            return response()->json([
                'message' => 'Agent profile not found.'
            ], 404);
        }

        // Get or create verification record
        $faceVerification = AgentFaceVerification::where('agent_id', $agent->id)
            ->where('status', 'pending')
            ->first();

        if (!$faceVerification) {
            // Create one if doesn't exist
            $faceVerification = AgentFaceVerification::create([
                'agent_id' => $agent->id,
                'status' => 'pending',
                'device_info' => $request->header('User-Agent'),
                'ip_address' => $request->ip(),
            ]);
        }

        $direction = $request->input('direction');
        $field = 'face_' . $direction;

        // Delete old image if exists
        if ($faceVerification->$field) {
            Storage::disk('public')->delete($faceVerification->$field);
        }

        // Store new image
        $path = $request->file('image')->store('face_verifications/' . $agent->id, 'public');

        // Update verification record
        $faceVerification->update([
            $field => $path,
        ]);

        // Get next required direction
        $nextStep = $this->getNextStep($faceVerification->fresh());

        return response()->json([
            'message' => 'Picha imehifadhiwa.',
            'direction' => $direction,
            'image_url' => url('storage/' . $path),
            'completion_percentage' => $faceVerification->fresh()->getCompletionPercentage(),
            'is_complete' => $faceVerification->fresh()->isComplete(),
            'missing_images' => $faceVerification->fresh()->getMissingImages(),
            'next_step' => $nextStep,
        ]);
    }

    /**
     * Upload all face images at once.
     */
    public function uploadAll(Request $request): JsonResponse
    {
        $request->validate([
            'face_center' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'face_left' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'face_right' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'face_up' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'face_down' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $user = $request->user();
        $agent = $user->agent;

        if (!$agent) {
            return response()->json([
                'message' => 'Agent profile not found.'
            ], 404);
        }

        // Delete existing pending verification
        AgentFaceVerification::where('agent_id', $agent->id)
            ->where('status', 'pending')
            ->delete();

        // Store all images
        $centerPath = $request->file('face_center')->store('face_verifications/' . $agent->id, 'public');
        $leftPath = $request->file('face_left')->store('face_verifications/' . $agent->id, 'public');
        $rightPath = $request->file('face_right')->store('face_verifications/' . $agent->id, 'public');
        $upPath = $request->file('face_up')->store('face_verifications/' . $agent->id, 'public');
        $downPath = $request->file('face_down')->store('face_verifications/' . $agent->id, 'public');

        // Create verification record
        $faceVerification = AgentFaceVerification::create([
            'agent_id' => $agent->id,
            'face_center' => $centerPath,
            'face_left' => $leftPath,
            'face_right' => $rightPath,
            'face_up' => $upPath,
            'face_down' => $downPath,
            'status' => 'pending',
            'device_info' => $request->header('User-Agent'),
            'ip_address' => $request->ip(),
            'metadata' => [
                'uploaded_at' => now()->toIso8601String(),
                'platform' => $request->input('platform', 'android'),
            ],
        ]);

        // Create in-app notification for admins
        $this->notifyAdmins($agent, $faceVerification);

        return response()->json([
            'message' => 'Picha zote zimehifadhiwa. Tafadhali subiri uhakiki.',
            'verification_id' => $faceVerification->id,
            'status' => 'pending',
            'images' => [
                'center' => url('storage/' . $centerPath),
                'left' => url('storage/' . $leftPath),
                'right' => url('storage/' . $rightPath),
                'up' => url('storage/' . $upPath),
                'down' => url('storage/' . $downPath),
            ],
        ], 201);
    }

    /**
     * Submit verification for review.
     */
    public function submit(Request $request): JsonResponse
    {
        $user = $request->user();
        $agent = $user->agent;

        if (!$agent) {
            return response()->json([
                'message' => 'Agent profile not found.'
            ], 404);
        }

        $faceVerification = AgentFaceVerification::where('agent_id', $agent->id)
            ->where('status', 'pending')
            ->first();

        if (!$faceVerification) {
            return response()->json([
                'message' => 'Hakuna mchakato wa uthibitishaji unaoendelea.'
            ], 404);
        }

        if (!$faceVerification->isComplete()) {
            return response()->json([
                'message' => 'Tafadhali pakia picha zote kabla ya kuwasilisha.',
                'missing_images' => $faceVerification->getMissingImages(),
            ], 400);
        }

        // Notify admins
        $this->notifyAdmins($agent, $faceVerification);

        return response()->json([
            'message' => 'Uthibitishaji umewasilishwa. Utapokea jibu hivi karibuni.',
            'verification_id' => $faceVerification->id,
            'status' => $faceVerification->status,
        ]);
    }

    /**
     * Get next required step.
     */
    private function getNextStep(AgentFaceVerification $verification): ?string
    {
        $order = ['center', 'left', 'right', 'up', 'down'];
        
        foreach ($order as $direction) {
            $field = 'face_' . $direction;
            if (!$verification->$field) {
                return $direction;
            }
        }
        
        return null; // All complete
    }

    /**
     * Notify admins about new verification.
     */
    private function notifyAdmins(Agent $agent, AgentFaceVerification $verification): void
    {
        // Create in-app notifications for admins
        $admins = \App\Models\User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            \App\Models\InAppNotification::create([
                'user_id' => $admin->id,
                'type' => 'face_verification',
                'title' => 'Uthibitishaji Mpya wa Uso',
                'message' => 'Agent ' . ($agent->user->name ?? 'Unknown') . ' amewasilisha picha za uthibitishaji wa uso.',
                'data' => [
                    'agent_id' => $agent->id,
                    'verification_id' => $verification->id,
                ],
            ]);
        }
    }
}
