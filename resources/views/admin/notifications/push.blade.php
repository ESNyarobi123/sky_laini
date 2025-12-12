@extends('layouts.dashboard')

@section('title', 'Push Notifications - Admin')

@push('styles')
<style>
    .glass-card {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .stat-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        border-color: rgba(99, 102, 241, 0.3);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }
    .btn-primary {
        background: linear-gradient(135deg, #6366F1, #8B5CF6);
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
    }
    .target-option {
        transition: all 0.2s ease;
    }
    .target-option.selected {
        border-color: #6366F1;
        background: rgba(99, 102, 241, 0.1);
    }
    .notification-preview {
        animation: slideIn 0.3s ease;
    }
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .emoji-btn {
        transition: all 0.2s ease;
    }
    .emoji-btn:hover {
        transform: scale(1.2);
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-white mb-2">Push Notifications</h1>
            <p class="text-gray-400 font-medium">Send announcements to all users, agents, or customers</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.notifications.history') }}" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-500 hover:to-purple-500 transition flex items-center gap-2 shadow-lg shadow-indigo-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                View All History
            </a>
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-white/5 text-white font-bold rounded-xl border border-white/10 hover:bg-white/10 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Users with Token -->
        <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-3 opacity-10">
                <svg class="w-16 h-16 text-indigo-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4a2 2 0 002 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"></path></svg>
            </div>
            <div class="text-gray-400 font-bold mb-1 text-xs uppercase tracking-wider">Push Enabled Users</div>
            <div class="text-3xl font-black text-white" id="stat-tokens">{{ $stats['users_with_token'] ?? 0 }}</div>
            <div class="text-indigo-400 text-sm font-bold mt-1">{{ $stats['token_coverage'] ?? '0%' }} coverage</div>
        </div>

        <!-- Android Users -->
        <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-3 opacity-10">
                <svg class="w-16 h-16 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.6 9.48l1.84-3.18c.16-.31.04-.69-.26-.85-.29-.15-.65-.06-.83.22l-1.88 3.24a11.463 11.463 0 00-8.94 0L5.65 5.67c-.19-.29-.58-.38-.87-.2-.28.18-.37.54-.22.83L6.4 9.48A10.78 10.78 0 003 18h18a10.78 10.78 0 00-3.4-8.52zM7 15.25a1.25 1.25 0 110-2.5 1.25 1.25 0 010 2.5zm10 0a1.25 1.25 0 110-2.5 1.25 1.25 0 010 2.5z"></path></svg>
            </div>
            <div class="text-gray-400 font-bold mb-1 text-xs uppercase tracking-wider">Android</div>
            <div class="text-3xl font-black text-white">{{ $stats['by_device']['android'] ?? 0 }}</div>
            <div class="text-green-400 text-sm font-bold mt-1">Active devices</div>
        </div>

        <!-- iOS Users -->
        <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-3 opacity-10">
                <svg class="w-16 h-16 text-gray-500" fill="currentColor" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"></path></svg>
            </div>
            <div class="text-gray-400 font-bold mb-1 text-xs uppercase tracking-wider">iOS</div>
            <div class="text-3xl font-black text-white">{{ $stats['by_device']['ios'] ?? 0 }}</div>
            <div class="text-gray-400 text-sm font-bold mt-1">Active devices</div>
        </div>

        <!-- Recent Registrations -->
        <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-3 opacity-10">
                <svg class="w-16 h-16 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm4.24 16L12 15.45 7.77 18l1.12-4.81-3.73-3.23 4.92-.42L12 5l1.92 4.53 4.92.42-3.73 3.23L16.23 18z"></path></svg>
            </div>
            <div class="text-gray-400 font-bold mb-1 text-xs uppercase tracking-wider">New This Week</div>
            <div class="text-3xl font-black text-white">{{ $stats['recent_registrations_7d'] ?? 0 }}</div>
            <div class="text-amber-400 text-sm font-bold mt-1">Token registrations</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Send Notification Form -->
        <div class="lg:col-span-2">
            <div class="glass-card rounded-3xl p-8">
                <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    Compose Notification
                </h2>

                <form id="broadcastForm" class="space-y-6">
                    @csrf
                    
                    <!-- Target Selection -->
                    <div>
                        <label class="block text-gray-400 font-bold mb-3 text-sm uppercase tracking-wider">Target Audience</label>
                        <div class="grid grid-cols-3 gap-4">
                            <button type="button" class="target-option selected p-4 rounded-xl border-2 border-white/10 text-center" data-target="all">
                                <svg class="w-8 h-8 mx-auto mb-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="text-white font-bold block">All Users</span>
                                <span class="text-gray-500 text-xs">{{ $stats['users_with_token'] ?? 0 }} devices</span>
                            </button>
                            <button type="button" class="target-option p-4 rounded-xl border-2 border-white/10 text-center" data-target="agents">
                                <svg class="w-8 h-8 mx-auto mb-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <span class="text-white font-bold block">Agents Only</span>
                                <span class="text-gray-500 text-xs">{{ $stats['by_role']['agents'] ?? 0 }} devices</span>
                            </button>
                            <button type="button" class="target-option p-4 rounded-xl border-2 border-white/10 text-center" data-target="customers">
                                <svg class="w-8 h-8 mx-auto mb-2 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <span class="text-white font-bold block">Customers Only</span>
                                <span class="text-gray-500 text-xs">{{ $stats['by_role']['customers'] ?? 0 }} devices</span>
                            </button>
                        </div>
                        <input type="hidden" name="target" id="targetInput" value="all">
                    </div>

                    <!-- Title -->
                    <div>
                        <label class="block text-gray-400 font-bold mb-3 text-sm uppercase tracking-wider">Notification Title</label>
                        <div class="relative">
                            <input type="text" name="title" id="titleInput" 
                                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white font-medium placeholder-gray-500 focus:outline-none focus:border-indigo-500 transition"
                                placeholder="Enter notification title..." maxlength="100" required>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm" id="titleCount">0/100</span>
                        </div>
                        <!-- Quick Emojis -->
                        <div class="flex gap-2 mt-2">
                            <button type="button" class="emoji-btn text-xl p-1" onclick="addEmoji('📢')">📢</button>
                            <button type="button" class="emoji-btn text-xl p-1" onclick="addEmoji('🎉')">🎉</button>
                            <button type="button" class="emoji-btn text-xl p-1" onclick="addEmoji('💰')">💰</button>
                            <button type="button" class="emoji-btn text-xl p-1" onclick="addEmoji('⚠️')">⚠️</button>
                            <button type="button" class="emoji-btn text-xl p-1" onclick="addEmoji('🚀')">🚀</button>
                            <button type="button" class="emoji-btn text-xl p-1" onclick="addEmoji('🔥')">🔥</button>
                            <button type="button" class="emoji-btn text-xl p-1" onclick="addEmoji('✅')">✅</button>
                        </div>
                    </div>

                    <!-- Message -->
                    <div>
                        <label class="block text-gray-400 font-bold mb-3 text-sm uppercase tracking-wider">Message Body</label>
                        <div class="relative">
                            <textarea name="body" id="bodyInput" rows="4"
                                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white font-medium placeholder-gray-500 focus:outline-none focus:border-indigo-500 transition resize-none"
                                placeholder="Enter your message..." maxlength="500" required></textarea>
                            <span class="absolute right-4 bottom-3 text-gray-500 text-sm" id="bodyCount">0/500</span>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-between pt-4">
                        <div class="text-gray-400 text-sm" id="previewTarget">
                            Sending to: <span class="text-white font-bold">All Users</span>
                        </div>
                        <button type="submit" id="submitBtn" 
                            class="btn-primary px-8 py-3 rounded-xl text-white font-bold flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            Send Notification
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview & History -->
        <div class="space-y-6">
            <!-- Live Preview -->
            <div class="glass-card rounded-3xl p-6">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    Live Preview
                </h3>
                
                <!-- Phone Mockup -->
                <div class="bg-gray-900 rounded-2xl p-3 border border-gray-700">
                    <!-- Status Bar -->
                    <div class="flex justify-between items-center px-2 mb-2 text-xs text-gray-400">
                        <span>9:41</span>
                        <div class="flex gap-1">
                            <span>📶</span>
                            <span>🔋</span>
                        </div>
                    </div>
                    
                    <!-- Notification Card -->
                    <div id="previewCard" class="notification-preview bg-white/10 rounded-xl p-3 border border-white/10">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4a2 2 0 002 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"></path></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center">
                                    <span class="text-amber-500 font-bold text-xs">SKY LAINI</span>
                                    <span class="text-gray-500 text-xs">now</span>
                                </div>
                                <div class="text-white font-bold text-sm truncate" id="previewTitle">Notification Title</div>
                                <div class="text-gray-400 text-xs line-clamp-2" id="previewBody">Your message will appear here...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Broadcasts -->
            <div class="glass-card rounded-3xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Recent Broadcasts</h3>
                
                <div class="space-y-3" id="historyList">
                    @forelse($history ?? [] as $broadcast)
                        <div class="p-3 rounded-xl bg-white/5 border border-white/5">
                            <div class="text-white font-bold text-sm truncate">{{ $broadcast['title'] }}</div>
                            <div class="text-gray-500 text-xs truncate">{{ $broadcast['message'] }}</div>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-indigo-400 text-xs font-bold">{{ $broadcast['recipients'] }} recipients</span>
                                <span class="text-gray-500 text-xs">{{ \Carbon\Carbon::parse($broadcast['sent_at'])->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-gray-500 text-sm">No broadcasts yet</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="glass-card rounded-3xl p-8 max-w-md mx-4 text-center">
        <div class="w-20 h-20 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <h3 class="text-2xl font-bold text-white mb-2">Notification Sent!</h3>
        <p class="text-gray-400 mb-4" id="successMessage">Successfully sent to 0 users</p>
        <button onclick="closeSuccessModal()" class="btn-primary px-6 py-2 rounded-xl text-white font-bold">
            Done
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const form = document.getElementById('broadcastForm');
    const titleInput = document.getElementById('titleInput');
    const bodyInput = document.getElementById('bodyInput');
    const targetInput = document.getElementById('targetInput');
    const submitBtn = document.getElementById('submitBtn');
    
    // Target selection
    document.querySelectorAll('.target-option').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.target-option').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            targetInput.value = btn.dataset.target;
            
            const labels = {
                'all': 'All Users',
                'agents': 'Agents Only',
                'customers': 'Customers Only'
            };
            document.getElementById('previewTarget').innerHTML = 
                `Sending to: <span class="text-white font-bold">${labels[btn.dataset.target]}</span>`;
        });
    });
    
    // Character counters
    titleInput.addEventListener('input', () => {
        document.getElementById('titleCount').textContent = `${titleInput.value.length}/100`;
        document.getElementById('previewTitle').textContent = titleInput.value || 'Notification Title';
    });
    
    bodyInput.addEventListener('input', () => {
        document.getElementById('bodyCount').textContent = `${bodyInput.value.length}/500`;
        document.getElementById('previewBody').textContent = bodyInput.value || 'Your message will appear here...';
    });
    
    // Add emoji
    function addEmoji(emoji) {
        titleInput.value += emoji;
        titleInput.dispatchEvent(new Event('input'));
        titleInput.focus();
    }
    
    // Form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Sending...
        `;
        
        try {
            const response = await fetch('{{ route("admin.notifications.broadcast") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
                },
                body: JSON.stringify({
                    title: titleInput.value,
                    body: bodyInput.value,
                    target: targetInput.value,
                }),
                credentials: 'same-origin',
            });
            
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('successMessage').textContent = 
                    `Successfully sent to ${data.result.push_success} devices. ${data.result.in_app_created} in-app notifications created.`;
                document.getElementById('successModal').classList.remove('hidden');
                document.getElementById('successModal').classList.add('flex');
                
                // Reset form
                form.reset();
                titleInput.dispatchEvent(new Event('input'));
                bodyInput.dispatchEvent(new Event('input'));
            } else {
                alert(data.message || 'Failed to send notification');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                Send Notification
            `;
        }
    });
    
    function closeSuccessModal() {
        document.getElementById('successModal').classList.add('hidden');
        document.getElementById('successModal').classList.remove('flex');
        // Reload to update history
        location.reload();
    }
</script>
@endpush
