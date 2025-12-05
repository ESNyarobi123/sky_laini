<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index()
    {
        $tickets = \App\Models\SupportTicket::where('user_id', auth()->id())
            ->latest()
            ->paginate(5);
            
        $recentRequests = auth()->user()->customer->lineRequests()
            ->where('payment_status', 'paid')
            ->where('status', '!=', \App\RequestStatus::Completed)
            ->latest()
            ->get();

        return view('customer.support.index', compact('tickets', 'recentRequests'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'required|in:general,refund,complaint',
            'message' => 'required|string',
            'related_request_id' => 'nullable|exists:line_requests,id',
        ]);

        \App\Models\SupportTicket::create([
            'user_id' => auth()->id(),
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'message' => $validated['message'],
            'related_request_id' => $validated['related_request_id'] ?? null,
            'status' => 'open',
        ]);

        return back()->with('success', 'Ticket created successfully! We will get back to you shortly.');
    }
}
