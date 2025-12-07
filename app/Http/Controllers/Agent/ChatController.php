<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\LineRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Display chat list.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $agent = $user->agent;

        $conversations = [];

        if ($agent) {
            $requests = $agent->lineRequests()
                ->with(['customer.user', 'chatMessages' => fn($q) => $q->latest()->limit(1)])
                ->latest()
                ->get();

            foreach ($requests as $req) {
                $lastMessage = $req->chatMessages->first();
                $unreadCount = $req->chatMessages()
                    ->where('sender_id', '!=', $user->id)
                    ->where('is_read', false)
                    ->count();

                $conversations[] = [
                    'line_request_id' => $req->id,
                    'request_number' => $req->request_number,
                    'participant' => [
                        'id' => $req->customer?->user?->id,
                        'name' => $req->customer?->user?->name,
                    ],
                    'last_message' => $lastMessage?->message ?? 'No messages yet',
                    'last_message_at' => $lastMessage?->created_at,
                    'unread_count' => $unreadCount,
                    'status' => $req->status,
                ];
            }
        }

        return view('agent.chat.index', compact('conversations'));
    }

    /**
     * Show chat for specific line request.
     */
    public function show(Request $request, LineRequest $lineRequest): View|JsonResponse
    {
        $user = $request->user();
        $agent = $user->agent;

        // Ensure agent owns this request
        if (!$agent || $lineRequest->agent_id !== $agent->id) {
            abort(403, 'Unauthorized');
        }

        // Get messages
        $messages = $lineRequest->chatMessages()
            ->with('sender:id,name')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark as read
        $lineRequest->chatMessages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        // Get conversations for sidebar
        $conversations = [];

        if ($agent) {
            $requests = $agent->lineRequests()
                ->with(['customer.user', 'chatMessages' => fn($q) => $q->latest()->limit(1)])
                ->latest()
                ->get();

            foreach ($requests as $req) {
                $lastMessage = $req->chatMessages->first();
                $unreadCount = $req->chatMessages()
                    ->where('sender_id', '!=', $user->id)
                    ->where('is_read', false)
                    ->count();

                $conversations[] = [
                    'line_request_id' => $req->id,
                    'request_number' => $req->request_number,
                    'participant' => [
                        'id' => $req->customer?->user?->id,
                        'name' => $req->customer?->user?->name,
                    ],
                    'last_message' => $lastMessage?->message ?? 'No messages yet',
                    'last_message_at' => $lastMessage?->created_at,
                    'unread_count' => $unreadCount,
                    'status' => $req->status,
                ];
            }
        }

        $participant = [
            'id' => $lineRequest->customer?->user?->id,
            'name' => $lineRequest->customer?->user?->name,
            'phone' => $lineRequest->customer_phone,
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'messages' => $messages,
                'participant' => $participant,
            ]);
        }

        return view('agent.chat.index', [
            'conversations' => $conversations,
            'messages' => $messages,
            'selectedRequest' => $lineRequest,
            'participant' => $participant,
        ]);
    }

    /**
     * Send a message.
     */
    public function store(Request $request, LineRequest $lineRequest): JsonResponse
    {
        $user = $request->user();
        $agent = $user->agent;

        // Ensure agent owns this request
        if (!$agent || $lineRequest->agent_id !== $agent->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = ChatMessage::create([
            'line_request_id' => $lineRequest->id,
            'sender_id' => $user->id,
            'sender_type' => 'agent',
            'message' => $validated['message'],
        ]);

        $message->load('sender:id,name');

        return response()->json([
            'message' => $message,
            'success' => true,
        ], 201);
    }
}
