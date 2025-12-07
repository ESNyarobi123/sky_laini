<?php

namespace App\Http\Controllers\Customer;

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
        $customer = $user->customer;

        $conversations = [];

        if ($customer) {
            $requests = $customer->lineRequests()
                ->with(['agent.user', 'chatMessages' => fn($q) => $q->latest()->limit(1)])
                ->whereNotNull('agent_id')
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
                        'id' => $req->agent?->user?->id,
                        'name' => $req->agent?->user?->name,
                    ],
                    'is_online' => $req->agent?->is_online ?? false,
                    'last_message' => $lastMessage?->message ?? 'No messages yet',
                    'last_message_at' => $lastMessage?->created_at,
                    'unread_count' => $unreadCount,
                    'status' => $req->status,
                ];
            }
        }

        return view('customer.chat.index', compact('conversations'));
    }

    /**
     * Show chat for specific line request.
     */
    public function show(Request $request, LineRequest $lineRequest): View|JsonResponse
    {
        $user = $request->user();

        // Ensure user owns this request
        if ($lineRequest->customer?->user_id !== $user->id) {
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
        $customer = $user->customer;
        $conversations = [];

        if ($customer) {
            $requests = $customer->lineRequests()
                ->with(['agent.user', 'chatMessages' => fn($q) => $q->latest()->limit(1)])
                ->whereNotNull('agent_id')
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
                        'id' => $req->agent?->user?->id,
                        'name' => $req->agent?->user?->name,
                    ],
                    'is_online' => $req->agent?->is_online ?? false,
                    'last_message' => $lastMessage?->message ?? 'No messages yet',
                    'last_message_at' => $lastMessage?->created_at,
                    'unread_count' => $unreadCount,
                    'status' => $req->status,
                ];
            }
        }

        $participant = [
            'id' => $lineRequest->agent?->user?->id,
            'name' => $lineRequest->agent?->user?->name,
            'is_online' => $lineRequest->agent?->is_online ?? false,
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'messages' => $messages,
                'participant' => $participant,
            ]);
        }

        return view('customer.chat.index', [
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

        // Ensure user owns this request
        if ($lineRequest->customer?->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = ChatMessage::create([
            'line_request_id' => $lineRequest->id,
            'sender_id' => $user->id,
            'sender_type' => 'customer',
            'message' => $validated['message'],
        ]);

        $message->load('sender:id,name');

        return response()->json([
            'message' => $message,
            'success' => true,
        ], 201);
    }
}
