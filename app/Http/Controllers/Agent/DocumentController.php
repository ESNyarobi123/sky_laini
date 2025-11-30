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
}
