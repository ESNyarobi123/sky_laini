@extends('layouts.dashboard')

@section('title', 'Notification History - Admin')

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
        transform: translateY(-3px);
        border-color: rgba(99, 102, 241, 0.3);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }
    .notification-row {
        transition: all 0.2s ease;
    }
    .notification-row:hover {
        background: rgba(255, 255, 255, 0.05);
    }
    .notification-row.selected {
        background: rgba(99, 102, 241, 0.1);
        border-color: rgba(99, 102, 241, 0.3);
    }
    .type-badge {
        font-size: 0.65rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .type-admin_broadcast { background: rgba(99, 102, 241, 0.2); color: #818CF8; }
    .type-admin_message { background: rgba(139, 92, 246, 0.2); color: #A78BFA; }
    .type-new_request { background: rgba(79, 70, 229, 0.2); color: #818CF8; }
    .type-order_created { background: rgba(16, 185, 129, 0.2); color: #34D399; }
    .type-agent_accepted { background: rgba(59, 130, 246, 0.2); color: #60A5FA; }
    .type-payment_received { background: rgba(34, 197, 94, 0.2); color: #4ADE80; }
    .type-payment_pending { background: rgba(245, 158, 11, 0.2); color: #FBBF24; }
    .type-job_completed { background: rgba(16, 185, 129, 0.2); color: #34D399; }
    .type-job_cancelled { background: rgba(239, 68, 68, 0.2); color: #F87171; }
    .type-request_released { background: rgba(139, 92, 246, 0.2); color: #A78BFA; }
    .type-agent_arriving { background: rgba(6, 182, 212, 0.2); color: #22D3EE; }
    .type-rating_received { background: rgba(245, 158, 11, 0.2); color: #FBBF24; }
    
    .btn-action {
        transition: all 0.2s ease;
        padding: 0.375rem;
        border-radius: 0.5rem;
    }
    .btn-action:hover {
        transform: scale(1.1);
    }
    .btn-view { color: #60A5FA; }
    .btn-view:hover { background: rgba(59, 130, 246, 0.2); }
    .btn-edit { color: #34D399; }
    .btn-edit:hover { background: rgba(16, 185, 129, 0.2); }
    .btn-delete { color: #F87171; }
    .btn-delete:hover { background: rgba(239, 68, 68, 0.2); }
    .btn-resend { color: #A78BFA; }
    .btn-resend:hover { background: rgba(139, 92, 246, 0.2); }

    /* Modal styles */
    .modal-overlay {
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(5px);
    }
    .modal-content {
        animation: slideIn 0.3s ease;
    }
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .checkbox-custom {
        appearance: none;
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 0.375rem;
        background: rgba(255, 255, 255, 0.05);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .checkbox-custom:checked {
        background: linear-gradient(135deg, #6366F1, #8B5CF6);
        border-color: transparent;
    }
    .checkbox-custom:checked::after {
        content: '✓';
        display: flex;
        justify-content: center;
        align-items: center;
        color: white;
        font-size: 0.75rem;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-white mb-2 flex items-center gap-3">
                <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Notification History
            </h1>
            <p class="text-gray-400 font-medium">View and manage all notifications sent to users</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.notifications.push') }}" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-500 hover:to-purple-500 transition flex items-center gap-2 shadow-lg shadow-indigo-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                Send New
            </a>
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-white/5 text-white font-bold rounded-xl border border-white/10 hover:bg-white/10 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Notifications -->
        <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-3 opacity-10">
                <svg class="w-16 h-16 text-indigo-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4a2 2 0 002 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"></path></svg>
            </div>
            <div class="text-gray-400 font-bold mb-1 text-xs uppercase tracking-wider">Total Notifications</div>
            <div class="text-3xl font-black text-white">{{ number_format($stats['total']) }}</div>
            <div class="text-indigo-400 text-sm font-bold mt-1">All time</div>
        </div>

        <!-- Admin Notifications -->
        <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-3 opacity-10">
                <svg class="w-16 h-16 text-purple-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4a2 2 0 002 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"></path></svg>
            </div>
            <div class="text-gray-400 font-bold mb-1 text-xs uppercase tracking-wider">Admin Broadcasts</div>
            <div class="text-3xl font-black text-white">{{ number_format($stats['admin']) }}</div>
            <div class="text-purple-400 text-sm font-bold mt-1">Manual sends</div>
        </div>

        <!-- System Notifications -->
        <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-3 opacity-10">
                <svg class="w-16 h-16 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"></path></svg>
            </div>
            <div class="text-gray-400 font-bold mb-1 text-xs uppercase tracking-wider">System Auto</div>
            <div class="text-3xl font-black text-white">{{ number_format($stats['system']) }}</div>
            <div class="text-green-400 text-sm font-bold mt-1">Automatic triggers</div>
        </div>

        <!-- Today's Notifications -->
        <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-3 opacity-10">
                <svg class="w-16 h-16 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm4.24 16L12 15.45 7.77 18l1.12-4.81-3.73-3.23 4.92-.42L12 5l1.92 4.53 4.92.42-3.73 3.23L16.23 18z"></path></svg>
            </div>
            <div class="text-gray-400 font-bold mb-1 text-xs uppercase tracking-wider">Today</div>
            <div class="text-3xl font-black text-white">{{ number_format($stats['today']) }}</div>
            <div class="text-amber-400 text-sm font-bold mt-1">Sent today</div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="glass-card rounded-3xl p-6">
        <form method="GET" action="{{ route('admin.notifications.history') }}" class="flex flex-col lg:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <div class="relative">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                        class="w-full px-4 py-3 pl-12 bg-white/5 border border-white/10 rounded-xl text-white font-medium placeholder-gray-500 focus:outline-none focus:border-indigo-500 transition"
                        placeholder="Search by title or message...">
                    <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Type Filter -->
            <select name="type" class="px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white font-medium focus:outline-none focus:border-indigo-500 transition min-w-[180px]">
                <option value="">All Types</option>
                @foreach($notificationTypes as $type)
                    <option value="{{ $type }}" {{ ($filters['type'] ?? '') === $type ? 'selected' : '' }}>
                        {{ ucwords(str_replace('_', ' ', $type)) }}
                    </option>
                @endforeach
            </select>

            <!-- Source Filter -->
            <select name="source" class="px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white font-medium focus:outline-none focus:border-indigo-500 transition min-w-[150px]">
                <option value="">All Sources</option>
                <option value="admin" {{ ($filters['source'] ?? '') === 'admin' ? 'selected' : '' }}>Admin Only</option>
                <option value="system" {{ ($filters['source'] ?? '') === 'system' ? 'selected' : '' }}>System Auto</option>
            </select>

            <!-- Date From -->
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                class="px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white font-medium focus:outline-none focus:border-indigo-500 transition">

            <!-- Date To -->
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                class="px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white font-medium focus:outline-none focus:border-indigo-500 transition">

            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-500 hover:to-purple-500 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Filter
            </button>

            @if(array_filter($filters))
                <a href="{{ route('admin.notifications.history') }}" class="px-6 py-3 bg-white/5 text-white font-bold rounded-xl border border-white/10 hover:bg-white/10 transition flex items-center gap-2">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Bulk Actions Bar -->
    <div id="bulkActionsBar" class="glass-card rounded-2xl p-4 hidden">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="text-white font-bold"><span id="selectedCount">0</span> selected</span>
                <button onclick="bulkDelete()" class="px-4 py-2 bg-red-500/20 text-red-400 font-bold rounded-xl hover:bg-red-500/30 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Delete Selected
                </button>
            </div>
            <button onclick="clearSelection()" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>

    <!-- Notifications Table -->
    <div class="glass-card rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="px-6 py-4 text-left">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="checkbox-custom">
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Title & Message</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($notifications as $notification)
                        <tr class="notification-row" data-id="{{ $notification->id }}">
                            <td class="px-6 py-4">
                                <input type="checkbox" class="notification-checkbox checkbox-custom" value="{{ $notification->id }}" onchange="updateSelection()">
                            </td>
                            <td class="px-6 py-4">
                                <span class="type-badge type-{{ $notification->type }}">
                                    {{ str_replace('_', ' ', $notification->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="max-w-xs">
                                    <div class="text-white font-bold text-sm truncate">{{ $notification->title }}</div>
                                    <div class="text-gray-500 text-xs truncate">{{ Str::limit($notification->message, 60) }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($notification->user)
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-xs">
                                            {{ strtoupper(substr($notification->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="text-white text-sm font-medium">{{ $notification->user->name }}</div>
                                            <div class="text-gray-500 text-xs">{{ $notification->user->role }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-500 text-sm">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($notification->read_at)
                                    <span class="inline-flex items-center gap-1 text-green-400 text-xs font-bold">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"></path></svg>
                                        Read
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-amber-400 text-xs font-bold">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"></path></svg>
                                        Unread
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-white text-sm">{{ $notification->created_at->format('M d, Y') }}</div>
                                <div class="text-gray-500 text-xs">{{ $notification->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="viewNotification({{ $notification->id }})" class="btn-action btn-view" title="View Details">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                    <button onclick="editNotification({{ $notification->id }}, '{{ addslashes($notification->title) }}', '{{ addslashes($notification->message) }}')" class="btn-action btn-edit" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button onclick="resendNotification({{ $notification->id }})" class="btn-action btn-resend" title="Resend">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    </button>
                                    <button onclick="deleteNotification({{ $notification->id }})" class="btn-action btn-delete" title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5V7a6 6 0 10-12 0v5l-5 5h5m7 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                <p class="text-gray-400 font-medium">No notifications found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($notifications->hasPages())
            <div class="px-6 py-4 border-t border-white/10">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="fixed inset-0 modal-overlay hidden items-center justify-center z-50">
    <div class="glass-card modal-content rounded-3xl p-8 max-w-lg mx-4 w-full">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-white">Notification Details</h3>
            <button onclick="closeModal('viewModal')" class="text-gray-400 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div id="viewModalContent" class="space-y-4">
            <!-- Content loaded via JS -->
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 modal-overlay hidden items-center justify-center z-50">
    <div class="glass-card modal-content rounded-3xl p-8 max-w-lg mx-4 w-full">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-white">Edit Notification</h3>
            <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="editForm" class="space-y-4">
            <input type="hidden" id="editId">
            <div>
                <label class="block text-gray-400 font-bold mb-2 text-sm">Title</label>
                <input type="text" id="editTitle" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white font-medium focus:outline-none focus:border-indigo-500 transition" required>
            </div>
            <div>
                <label class="block text-gray-400 font-bold mb-2 text-sm">Message</label>
                <textarea id="editMessage" rows="4" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white font-medium focus:outline-none focus:border-indigo-500 transition resize-none" required></textarea>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeModal('editModal')" class="flex-1 px-4 py-3 bg-white/5 text-white font-bold rounded-xl border border-white/10 hover:bg-white/10 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-500 hover:to-purple-500 transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 modal-overlay hidden items-center justify-center z-50">
    <div class="glass-card modal-content rounded-3xl p-8 max-w-md mx-4 text-center">
        <div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
        </div>
        <h3 class="text-xl font-bold text-white mb-2">Delete Notification?</h3>
        <p class="text-gray-400 mb-6">This action cannot be undone.</p>
        <input type="hidden" id="deleteId">
        <div class="flex gap-3">
            <button onclick="closeModal('deleteModal')" class="flex-1 px-4 py-3 bg-white/5 text-white font-bold rounded-xl border border-white/10 hover:bg-white/10 transition">
                Cancel
            </button>
            <button onclick="confirmDelete()" class="flex-1 px-4 py-3 bg-red-500 text-white font-bold rounded-xl hover:bg-red-600 transition">
                Delete
            </button>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div id="successToast" class="fixed bottom-6 right-6 hidden transform transition-all duration-300 translate-y-full opacity-0">
    <div class="glass-card rounded-2xl p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-green-500/20 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <span id="successToastMessage" class="text-white font-bold">Success!</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    // Selection handling
    let selectedIds = new Set();

    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.notification-checkbox');
        
        checkboxes.forEach(cb => {
            cb.checked = selectAll.checked;
            if (selectAll.checked) {
                selectedIds.add(parseInt(cb.value));
            } else {
                selectedIds.delete(parseInt(cb.value));
            }
        });
        
        updateBulkActionsBar();
    }

    function updateSelection() {
        const checkboxes = document.querySelectorAll('.notification-checkbox');
        selectedIds = new Set();
        
        checkboxes.forEach(cb => {
            if (cb.checked) {
                selectedIds.add(parseInt(cb.value));
            }
        });
        
        updateBulkActionsBar();
    }

    function updateBulkActionsBar() {
        const bar = document.getElementById('bulkActionsBar');
        const count = document.getElementById('selectedCount');
        
        if (selectedIds.size > 0) {
            bar.classList.remove('hidden');
            count.textContent = selectedIds.size;
        } else {
            bar.classList.add('hidden');
        }
    }

    function clearSelection() {
        selectedIds.clear();
        document.getElementById('selectAll').checked = false;
        document.querySelectorAll('.notification-checkbox').forEach(cb => cb.checked = false);
        updateBulkActionsBar();
    }

    // Modal functions
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // View notification
    async function viewNotification(id) {
        try {
            const response = await fetch(`/admin/notifications/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            
            if (data.success) {
                const n = data.notification;
                document.getElementById('viewModalContent').innerHTML = `
                    <div class="space-y-4">
                        <div>
                            <span class="type-badge type-${n.type}">${n.type.replace(/_/g, ' ')}</span>
                        </div>
                        <div>
                            <label class="text-gray-400 text-xs font-bold uppercase">Title</label>
                            <p class="text-white font-bold">${n.title}</p>
                        </div>
                        <div>
                            <label class="text-gray-400 text-xs font-bold uppercase">Message</label>
                            <p class="text-white">${n.message}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-gray-400 text-xs font-bold uppercase">User</label>
                                <p class="text-white">${n.user ? n.user.name : '-'}</p>
                            </div>
                            <div>
                                <label class="text-gray-400 text-xs font-bold uppercase">Status</label>
                                <p class="${n.read_at ? 'text-green-400' : 'text-amber-400'}">${n.read_at ? 'Read' : 'Unread'}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-gray-400 text-xs font-bold uppercase">Created</label>
                                <p class="text-white">${n.created_at}</p>
                            </div>
                            <div>
                                <label class="text-gray-400 text-xs font-bold uppercase">Read At</label>
                                <p class="text-white">${n.read_at || '-'}</p>
                            </div>
                        </div>
                        ${n.data ? `
                        <div>
                            <label class="text-gray-400 text-xs font-bold uppercase">Additional Data</label>
                            <pre class="text-gray-300 text-xs mt-1 p-3 bg-white/5 rounded-lg overflow-auto">${JSON.stringify(n.data, null, 2)}</pre>
                        </div>
                        ` : ''}
                    </div>
                `;
                openModal('viewModal');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Failed to load notification details', true);
        }
    }

    // Edit notification
    function editNotification(id, title, message) {
        document.getElementById('editId').value = id;
        document.getElementById('editTitle').value = title;
        document.getElementById('editMessage').value = message;
        openModal('editModal');
    }

    // Handle edit form submission
    document.getElementById('editForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const id = document.getElementById('editId').value;
        const title = document.getElementById('editTitle').value;
        const message = document.getElementById('editMessage').value;
        
        try {
            const response = await fetch(`/admin/notifications/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ title, message }),
            });
            
            const data = await response.json();
            
            if (data.success) {
                closeModal('editModal');
                showToast('Notification updated successfully');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to update notification', true);
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('An error occurred', true);
        }
    });

    // Delete notification
    function deleteNotification(id) {
        document.getElementById('deleteId').value = id;
        openModal('deleteModal');
    }

    async function confirmDelete() {
        const id = document.getElementById('deleteId').value;
        
        try {
            const response = await fetch(`/admin/notifications/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            
            const data = await response.json();
            
            if (data.success) {
                closeModal('deleteModal');
                showToast('Notification deleted successfully');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to delete notification', true);
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('An error occurred', true);
        }
    }

    // Bulk delete
    async function bulkDelete() {
        if (selectedIds.size === 0) return;
        
        if (!confirm(`Are you sure you want to delete ${selectedIds.size} notification(s)?`)) return;
        
        try {
            const response = await fetch('/admin/notifications/bulk-delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ ids: Array.from(selectedIds) }),
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to delete notifications', true);
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('An error occurred', true);
        }
    }

    // Resend notification
    async function resendNotification(id) {
        if (!confirm('Resend this notification to the user?')) return;
        
        try {
            const response = await fetch(`/admin/notifications/${id}/resend`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast('Notification resent successfully');
            } else {
                showToast(data.message || 'Failed to resend notification', true);
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('An error occurred', true);
        }
    }

    // Toast notification
    function showToast(message, isError = false) {
        const toast = document.getElementById('successToast');
        const toastMessage = document.getElementById('successToastMessage');
        
        toastMessage.textContent = message;
        toast.classList.remove('hidden', 'translate-y-full', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
        
        if (isError) {
            toast.querySelector('svg').parentElement.className = 'w-10 h-10 bg-red-500/20 rounded-full flex items-center justify-center';
            toast.querySelector('svg').className = 'w-5 h-5 text-red-500';
        }
        
        setTimeout(() => {
            toast.classList.add('translate-y-full', 'opacity-0');
            toast.classList.remove('translate-y-0', 'opacity-100');
            setTimeout(() => toast.classList.add('hidden'), 300);
        }, 3000);
    }

    // Close modals on backdrop click
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal(modal.id);
            }
        });
    });
</script>
@endpush
