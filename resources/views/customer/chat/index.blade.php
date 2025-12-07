@extends('layouts.dashboard')

@section('title', __('messages.chat.title') . ' - SKY LAINI')

@push('styles')
<style>
    .chat-container {
        height: calc(100vh - 200px);
        display: flex;
        flex-direction: column;
    }
    
    .conversations-list {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 24px;
        overflow: hidden;
    }
    
    .conversation-item {
        padding: 16px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .conversation-item:hover, .conversation-item.active {
        background: rgba(245, 158, 11, 0.1);
    }
    
    .conversation-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: black;
        font-size: 18px;
    }
    
    .chat-window {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 24px;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .chat-header {
        padding: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        display: flex;
        align-items: center;
        gap: 16px;
    }
    
    .messages-container {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .message-bubble {
        max-width: 70%;
        padding: 12px 16px;
        border-radius: 16px;
        position: relative;
        animation: fadeInUp 0.3s ease;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .message-sent {
        align-self: flex-end;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: black;
        border-bottom-right-radius: 4px;
    }
    
    .message-received {
        align-self: flex-start;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border-bottom-left-radius: 4px;
    }
    
    .message-time {
        font-size: 11px;
        opacity: 0.7;
        margin-top: 4px;
    }
    
    .message-status {
        display: flex;
        align-items: center;
        gap: 4px;
        justify-content: flex-end;
    }
    
    .chat-input-container {
        padding: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        display: flex;
        gap: 12px;
        align-items: center;
    }
    
    .chat-input {
        flex: 1;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        padding: 12px 20px;
        color: white;
        font-size: 14px;
        outline: none;
        transition: all 0.3s ease;
    }
    
    .chat-input:focus {
        border-color: rgba(245, 158, 11, 0.5);
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
    }
    
    .send-btn {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .send-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 20px rgba(245, 158, 11, 0.4);
    }
    
    .send-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
    
    .unread-badge {
        background: #ef4444;
        color: white;
        font-size: 11px;
        font-weight: bold;
        padding: 2px 8px;
        border-radius: 12px;
        min-width: 20px;
        text-align: center;
    }
    
    .online-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #22c55e;
        border: 2px solid #0a0a0a;
        position: absolute;
        bottom: 2px;
        right: 2px;
    }
    
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        text-align: center;
        padding: 40px;
    }
    
    .empty-state-icon {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(245, 158, 11, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
    }
    
    .typing-indicator {
        display: flex;
        gap: 4px;
        padding: 12px 16px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        width: fit-content;
    }
    
    .typing-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        animation: typingBounce 1.4s infinite ease-in-out;
    }
    
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }
    
    @keyframes typingBounce {
        0%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-8px); }
    }

    /* Scrollbar styling */
    .messages-container::-webkit-scrollbar {
        width: 6px;
    }
    .messages-container::-webkit-scrollbar-track {
        background: transparent;
    }
    .messages-container::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
    }
</style>
@endpush

@section('content')
<div class="chat-container">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">
        <!-- Conversations List -->
        <div class="lg:col-span-1">
            <div class="conversations-list h-full">
                <div class="p-4 border-b border-white/5">
                    <h2 class="text-xl font-bold text-white">{{ __('messages.chat.title') }}</h2>
                    <p class="text-gray-400 text-sm mt-1">{{ __('messages.chat.conversations') ?? 'Your conversations' }}</p>
                </div>
                
                <div class="overflow-y-auto" style="max-height: calc(100vh - 300px);">
                    @forelse($conversations ?? [] as $conversation)
                        <a href="{{ route('customer.chat.show', $conversation['line_request_id']) }}" 
                           class="conversation-item flex items-center gap-4 {{ request()->route('lineRequest')?->id == $conversation['line_request_id'] ? 'active' : '' }}">
                            <div class="relative">
                                <div class="conversation-avatar">
                                    {{ substr($conversation['participant']['name'] ?? 'A', 0, 1) }}
                                </div>
                                @if($conversation['is_online'] ?? false)
                                    <div class="online-dot"></div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-white truncate">{{ $conversation['participant']['name'] ?? 'Unknown' }}</span>
                                    <span class="text-xs text-gray-500">{{ $conversation['last_message_at'] ? \Carbon\Carbon::parse($conversation['last_message_at'])->diffForHumans(null, true, true) : '' }}</span>
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="text-sm text-gray-400 truncate">{{ Str::limit($conversation['last_message'], 30) }}</span>
                                    @if(($conversation['unread_count'] ?? 0) > 0)
                                        <span class="unread-badge">{{ $conversation['unread_count'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-white/5 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 text-sm">{{ __('messages.chat.no_messages') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        <!-- Chat Window -->
        <div class="lg:col-span-2">
            <div class="chat-window">
                @if(isset($selectedRequest))
                    <!-- Chat Header -->
                    <div class="chat-header">
                        <div class="relative">
                            <div class="conversation-avatar">
                                {{ substr($participant['name'] ?? 'A', 0, 1) }}
                            </div>
                            @if($participant['is_online'] ?? false)
                                <div class="online-dot"></div>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-bold text-white">{{ $participant['name'] ?? 'Unknown' }}</h3>
                            <p class="text-sm text-gray-400">
                                {{ $participant['is_online'] ? __('messages.chat.online') : __('messages.chat.offline') }}
                            </p>
                        </div>
                        <div class="ml-auto flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-500">
                                #{{ $selectedRequest->request_number }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Messages -->
                    <div class="messages-container" id="messagesContainer">
                        @forelse($messages ?? [] as $message)
                            <div class="message-bubble {{ $message->sender_id === auth()->id() ? 'message-sent' : 'message-received' }}">
                                <p>{{ $message->message }}</p>
                                @if($message->attachment)
                                    <div class="mt-2">
                                        @if($message->attachment_type === 'image')
                                            <img src="{{ Storage::url($message->attachment) }}" alt="Image" class="rounded-lg max-w-full">
                                        @else
                                            <a href="{{ Storage::url($message->attachment) }}" class="text-amber-500 underline" target="_blank">
                                                📎 {{ __('messages.chat.attachment') }}
                                            </a>
                                        @endif
                                    </div>
                                @endif
                                <div class="message-time message-status">
                                    <span>{{ $message->created_at->format('H:i') }}</span>
                                    @if($message->sender_id === auth()->id())
                                        @if($message->is_read)
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="flex-1 flex items-center justify-center">
                                <div class="text-center">
                                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-white/5 flex items-center justify-center">
                                        <svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-500">{{ __('messages.chat.no_messages') }}</p>
                                    <p class="text-gray-600 text-sm mt-1">Start the conversation!</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    
                    <!-- Chat Input -->
                    <form action="{{ route('customer.chat.store', $selectedRequest) }}" method="POST" class="chat-input-container" id="chatForm">
                        @csrf
                        <button type="button" class="text-gray-400 hover:text-white transition p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                            </svg>
                        </button>
                        <input type="text" name="message" class="chat-input" placeholder="{{ __('messages.chat.type_message') }}" autocomplete="off" required>
                        <button type="submit" class="send-btn">
                            <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                        </button>
                    </form>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <svg class="w-16 h-16 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">{{ __('messages.chat.title') }}</h3>
                        <p class="text-gray-400 max-w-sm">Select a conversation from the list to start chatting with your agent.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-scroll to bottom
    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Form submission with AJAX
    const chatForm = document.getElementById('chatForm');
    if (chatForm) {
        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const input = this.querySelector('input[name="message"]');
            const message = input.value.trim();
            
            if (!message) return;
            
            // Optimistic UI update
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message-bubble message-sent';
            messageDiv.innerHTML = `
                <p>${message}</p>
                <div class="message-time message-status">
                    <span>${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false })}</span>
                    <svg class="w-4 h-4 opacity-50 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            `;
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            input.value = '';
            
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                if (!response.ok) throw new Error('Failed to send');
                
                // Update status icon
                messageDiv.querySelector('svg').outerHTML = `
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                `;
            } catch (error) {
                messageDiv.style.opacity = '0.5';
                console.error('Failed to send message:', error);
            }
        });
    }
    
    // Polling for new messages
    @if(isset($selectedRequest))
    setInterval(async () => {
        try {
            const response = await fetch('{{ route("customer.chat.show", $selectedRequest) }}', {
                headers: { 'Accept': 'application/json' }
            });
            // Handle new messages...
        } catch (error) {
            console.error('Error fetching messages:', error);
        }
    }, 5000);
    @endif
</script>
@endpush
