<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * Upload agent documents.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nida_number' => 'required|string',
            'id_document' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'passport_photo' => 'required|image|max:5120',
        ]);

        $user = $request->user();
        $agent = $user->agent;

        // Auto-create agent profile if missing (for users registered before fix)
        if (!$agent && $user->isAgent()) {
            $agent = \App\Models\Agent::create([
                'user_id' => $user->id,
                'phone' => $user->phone,
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

        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found. Please ensure you are registered as an agent.'], 404);
        }

        // Store Files
        $idPath = $request->file('id_document')->store('agent_documents', 'public');
        $passportPath = $request->file('passport_photo')->store('agent_documents', 'public');

        // Update Agent Profile
        $agent->update([
            'nida_number' => $request->nida_number,
        ]);

        // Create Document Records
        AgentDocument::create([
            'agent_id' => $agent->id,
            'document_type' => 'nida_id',
            'file_path' => $idPath,
            'file_name' => $request->file('id_document')->getClientOriginalName(),
            'verification_status' => 'pending' // Assuming string or enum value
        ]);

        AgentDocument::create([
            'agent_id' => $agent->id,
            'document_type' => 'passport_photo',
            'file_path' => $passportPath,
            'file_name' => $request->file('passport_photo')->getClientOriginalName(),
            'verification_status' => 'pending'
        ]);

        return response()->json(['message' => 'Documents uploaded successfully'], 201);
    }

    /**
     * Get document status.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $agent = $user->agent;
        
        // Auto-create agent profile if missing (for users registered before fix)
        if (!$agent && $user->isAgent()) {
            $agent = \App\Models\Agent::create([
                'user_id' => $user->id,
                'phone' => $user->phone,
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

        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found. Please ensure you are registered as an agent.'], 404);
        }

        $documents = $agent->documents;

        return response()->json([
            'agent_id' => $agent->id,
            'is_verified' => $agent->is_verified,
            'nida_number' => $agent->nida_number,
            'documents' => $documents,
        ]);
    }
}
