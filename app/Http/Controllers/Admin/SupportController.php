<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index()
    {
        // Get users who have tickets, ordered by latest ticket
        $userIds = SupportTicket::select('user_id')
            ->distinct()
            ->get()
            ->pluck('user_id');

        $users = User::whereIn('id', $userIds)
            ->with(['customer', 'agent'])
            ->get()
            ->map(function ($user) {
                $user->last_ticket = $user->tickets()->latest()->first();
                return $user;
            })
            ->sortByDesc('last_ticket.created_at');

        $customers = $users->filter(fn($u) => $u->role === UserRole::Customer);
        $agents = $users->filter(fn($u) => $u->role === UserRole::Agent);

        // Stats
        $stats = [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::where('status', 'open')->count(),
            'closed' => SupportTicket::where('status', 'closed')->count(),
            'today' => SupportTicket::whereDate('created_at', today())->count(),
        ];

        return view('admin.support.index', compact('customers', 'agents', 'stats'));
    }

    public function show(User $user)
    {
        $tickets = $user->tickets()
            ->with(['messages' => function($query) {
                $query->oldest(); // Chronological order for messages
            }])
            ->latest()
            ->get();
            
        return response()->json($tickets);
    }

    public function reply(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'ticket_id' => 'nullable|exists:support_tickets,id'
        ]);

        $ticket = null;
        if ($request->ticket_id) {
            $ticket = SupportTicket::find($request->ticket_id);
        } else {
            // Find latest open ticket
            $ticket = SupportTicket::where('user_id', $request->user_id)
                ->where('status', 'open')
                ->latest()
                ->first();
                
            // If no open ticket, find any latest ticket and re-open
            if (!$ticket) {
                $ticket = SupportTicket::where('user_id', $request->user_id)->latest()->first();
                if ($ticket) {
                    $ticket->update(['status' => 'in_progress']);
                }
            }
        }

        if (!$ticket) {
            // Create new ticket if absolutely none exist
            $ticket = SupportTicket::create([
                'user_id' => $request->user_id,
                'subject' => 'Admin Message',
                'message' => $request->message,
                'status' => 'open',
                'category' => 'general'
            ]);
            // The initial message is the ticket message itself, so we don't need a support_message for it?
            // Or should we duplicate it? Let's keep it simple.
        } else {
            // Add message to existing ticket
            $ticket->messages()->create([
                'user_id' => auth()->id(),
                'message' => $request->message
            ]);
        }

        return response()->json(['success' => true]);
    }
}
