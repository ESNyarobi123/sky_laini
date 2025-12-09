<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::with('user')->latest()->paginate(10);
        return view('admin.agents.index', compact('agents'));
    }

    public function show(Agent $agent)
    {
        $agent->load(['user', 'documents', 'lineRequests.customer.user']);
        return view('admin.agents.show', compact('agent'));
    }

    public function toggleStatus(Agent $agent)
    {
        // Toggle verification status (Activate/Deactivate)
        $agent->update([
            'is_verified' => !$agent->is_verified
        ]);

        $status = $agent->is_verified ? 'activated' : 'deactivated';
        return back()->with('success', "Agent has been {$status} successfully.");
    }

    /**
     * View/download agent document (bypasses storage link issues).
     */
    public function viewDocument(\App\Models\AgentDocument $document)
    {
        $path = storage_path('app/public/' . $document->file_path);

        if (!file_exists($path)) {
            abort(404, 'Document not found');
        }

        $mimeType = $document->mime_type ?? mime_content_type($path) ?? 'application/octet-stream';

        return response()->file($path, [
            'Content-Type' => $mimeType,
        ]);
    }

    /**
     * Download agent document.
     */
    public function downloadDocument(\App\Models\AgentDocument $document)
    {
        $path = storage_path('app/public/' . $document->file_path);

        if (!file_exists($path)) {
            abort(404, 'Document not found');
        }

        return response()->download($path, $document->file_name);
    }
}
