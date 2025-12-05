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

        $agent = $request->user()->agent;

        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
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
        $agent = $request->user()->agent;
        
        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
        }

        $documents = $agent->documents;

        return response()->json($documents);
    }
}
