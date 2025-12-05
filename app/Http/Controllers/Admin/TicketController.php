<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::with('user')
            ->latest()
            ->paginate(15);
            
        return view('admin.tickets.index', compact('tickets'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['user', 'messages.user', 'relatedRequest']);
        return view('admin.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
            'status' => 'nullable|in:open,in_progress,closed'
        ]);

        $ticket->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->message
        ]);

        if ($request->status) {
            $ticket->update(['status' => $request->status]);
        } else {
            // Auto update status if replying
            if ($ticket->status === 'open') {
                $ticket->update(['status' => 'in_progress']);
            }
        }

        return back()->with('success', 'Reply sent successfully.');
    }
}
