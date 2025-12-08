<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    /**
     * List support tickets.
     */
    public function index(Request $request): JsonResponse
    {
        $tickets = SupportTicket::where('user_id', $request->user()->id)
            ->with('messages')
            ->latest()
            ->paginate(15);

        return response()->json($tickets);
    }

    /**
     * Create a new support ticket.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'required|in:general,refund,complaint',
            'message' => 'required|string',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'message' => $validated['message'], // Initial message usually stored in ticket or as first message? 
            // The web controller stores it in 'message' column of ticket.
            'status' => 'open',
        ]);

        // Also create a message entry if the system uses separate message table for conversation
        // But looking at web controller, it just stores in ticket. 
        // However, admin reply uses 'SupportMessage' model probably?
        // Let's check SupportTicket model.

        return response()->json($ticket, 201);
    }

    /**
     * Show a specific ticket.
     */
    public function show(Request $request, SupportTicket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($ticket->load('messages'));
    }

    /**
     * Reply to a ticket.
     */
    public function reply(Request $request, SupportTicket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $message = $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        return response()->json($message, 201);
    }

    /**
     * Close a ticket.
     */
    public function close(Request $request, SupportTicket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($ticket->status === 'closed') {
            return response()->json(['message' => 'Ticket is already closed'], 400);
        }

        $ticket->update(['status' => 'closed']);

        return response()->json([
            'message' => 'Ticket closed successfully',
            'ticket' => $ticket->fresh(),
        ]);
    }
}
