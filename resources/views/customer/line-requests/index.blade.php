@extends('layouts.dashboard')

@section('title', 'My Line Requests - SKY LAINI')
@section('page-title', 'My Line Requests')

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        box-shadow: 0 10px 30px rgba(14, 165, 233, 0.1);
    }
    
    .request-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        transition: all 0.3s ease;
    }
    
    .request-card:hover {
        transform: translateX(5px);
        box-shadow: 0 10px 25px rgba(14, 165, 233, 0.15);
    }
    
    .status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .btn-sky {
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
        box-shadow: 0 10px 25px rgba(14, 165, 233, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-sky:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(14, 165, 233, 0.4);
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-sky-900 mb-2">My Line Requests</h1>
            <p class="text-sky-600">View and manage all your line registration requests</p>
        </div>
        <a href="{{ route('customer.line-requests.create') }}" class="btn-sky px-6 py-3 rounded-xl text-white font-bold transition-all flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            New Request
        </a>
    </div>

    <!-- Requests List -->
    <div class="glass-card rounded-2xl p-6">
        @if($requests->count() > 0)
            <div class="space-y-4">
                @foreach($requests as $request)
                    <div class="request-card rounded-xl p-5">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="font-bold text-sky-900 text-lg">{{ $request->request_number }}</h3>
                                    @php
                                        $statusClass = match($request->status->value) {
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'in_progress' => 'bg-blue-100 text-blue-800',
                                            'accepted' => 'bg-green-100 text-green-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="status-badge {{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $request->status->value)) }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-4 text-sm text-sky-700">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ ucfirst($request->line_type->value) }}
                                    </span>
                                    @if($request->agent)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            {{ $request->agent->user->name }}
                                        </span>
                                    @endif
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ $request->created_at->format('M d, Y') }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('customer.line-requests.show', $request) }}" class="px-4 py-2 bg-sky-50 text-sky-700 rounded-lg hover:bg-sky-100 font-medium text-sm transition">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $requests->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-sky-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <p class="text-sky-700 text-lg font-semibold mb-2">No requests yet</p>
                <p class="text-sky-600 mb-6">Create your first line request to get started!</p>
                <a href="{{ route('customer.line-requests.create') }}" class="btn-sky inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-semibold transition-all">
                    Create Request
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

