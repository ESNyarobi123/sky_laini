<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\LineRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    /**
     * Get chat messages for a line request.
     */
    public function index(Request $request, LineRequest $lineRequest): JsonResponse
    {
        // Ensure user has access
        $user = $request->user();
        if (!$this->canAccessChat($user, $lineRequest)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $lineRequest->chatMessages()
            ->with('sender:id,name')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        $this->markMessagesAsRead($user, $lineRequest);

        return response()->json([
            'messages' => $messages,
            'line_request' => [
                'id' => $lineRequest->id,
                'request_number' => $lineRequest->request_number,
                'status' => $lineRequest->status,
            ],
            'participants' => [
                'customer' => $lineRequest->customer?->user?->only(['id', 'name']),
                'agent' => $lineRequest->agent?->user?->only(['id', 'name']),
            ],
        ]);
    }

    /**
     * Send a new message.
     */
    public function store(Request $request, LineRequest $lineRequest): JsonResponse
    {
        $user = $request->user();
        if (!$this->canAccessChat($user, $lineRequest)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message' => 'required_without:attachment|string|max:1000',
            'attachment' => 'nullable|file|max:5120', // 5MB max
        ]);

        $senderType = $user->isCustomer() ? 'customer' : 'agent';
        $attachmentPath = null;
        $attachmentType = null;

        // Handle file attachment
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('chat-attachments', 'public');
            
            $mimeType = $file->getMimeType();
            if (str_contains($mimeType, 'image')) {
                $attachmentType = 'image';
            } elseif (str_contains($mimeType, 'audio')) {
                $attachmentType = 'voice';
            } else {
                $attachmentType = 'file';
            }
        }

        $message = ChatMessage::create([
            'line_request_id' => $lineRequest->id,
            'sender_id' => $user->id,
            'sender_type' => $senderType,
            'message' => $validated['message'] ?? '',
            'attachment' => $attachmentPath,
            'attachment_type' => $attachmentType,
        ]);

        $message->load('sender:id,name');

        // TODO: Broadcast real-time event here
        // broadcast(new ChatMessageSent($message))->toOthers();

        return response()->json([
            'message' => $message,
            'success' => true,
        ], 201);
    }

    /**
     * Mark messages as read.
     */
    public function markAsRead(Request $request, LineRequest $lineRequest): JsonResponse
    {
        $user = $request->user();
        if (!$this->canAccessChat($user, $lineRequest)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->markMessagesAsRead($user, $lineRequest);

        return response()->json(['success' => true]);
    }

    /**
     * Get unread message count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = ChatMessage::where('sender_id', '!=', $user->id)
            ->where('is_read', false);

        if ($user->isCustomer()) {
            $customerRequests = $user->customer?->lineRequests()->pluck('id') ?? collect();
            $query->whereIn('line_request_id', $customerRequests);
        } else {
            $agentRequests = $user->agent?->lineRequests()->pluck('id') ?? collect();
            $query->whereIn('line_request_id', $agentRequests);
        }

        return response()->json([
            'unread_count' => $query->count(),
        ]);
    }

    /**
     * Get all chats for user.
     */
    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();
        $conversations = [];

        if ($user->isCustomer() && $user->customer) {
            $requests = $user->customer->lineRequests()
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
                    'participant' => $req->agent?->user?->only(['id', 'name']),
                    'last_message' => $lastMessage?->message ?? 'No messages yet',
                    'last_message_at' => $lastMessage?->created_at,
                    'unread_count' => $unreadCount,
                    'status' => $req->status,
                ];
            }
        } elseif ($user->isAgent() && $user->agent) {
            $requests = $user->agent->lineRequests()
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
                    'participant' => $req->customer?->user?->only(['id', 'name']),
                    'last_message' => $lastMessage?->message ?? 'No messages yet',
                    'last_message_at' => $lastMessage?->created_at,
                    'unread_count' => $unreadCount,
                    'status' => $req->status,
                ];
            }
        }

        return response()->json(['conversations' => $conversations]);
    }

    /**
     * Check if user can access the chat.
     */
    private function canAccessChat($user, LineRequest $lineRequest): bool
    {
        if ($user->isCustomer()) {
            return $lineRequest->customer?->user_id === $user->id;
        }
        
        if ($user->isAgent()) {
            return $lineRequest->agent_id === $user->agent?->id;
        }

        return false;
    }

    /**
     * Mark messages as read for user.
     */
    private function markMessagesAsRead($user, LineRequest $lineRequest): void
    {
        $lineRequest->chatMessages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}
