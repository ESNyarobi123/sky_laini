@extends('layouts.dashboard')

@section('title', 'Admin Dashboard - SKY LAINI')

@push('styles')
<style>
    .stat-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        border-color: rgba(245, 158, 11, 0.3);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }
    .glass-card {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .btn-gold {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2);
        transition: all 0.3s ease;
        color: black;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-white mb-2">Admin Dashboard</h1>
            <p class="text-gray-400 font-medium">System Overview & Management</p>
        </div>
        <div class="flex gap-3">
            <button class="px-4 py-2 bg-white/5 text-white font-bold rounded-xl border border-white/10 hover:bg-white/10 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export Report
            </button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Users -->
        <div class="stat-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div class="text-gray-400 font-bold mb-2 text-sm uppercase tracking-wider">Total Users</div>
            <div class="text-4xl font-black text-white mb-1">{{ number_format($totalUsers) }}</div>
            <div class="text-green-500 text-sm font-bold flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                <span>Active System</span>
            </div>
        </div>

        <!-- Active Agents -->
        <div class="stat-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <div class="text-gray-400 font-bold mb-2 text-sm uppercase tracking-wider">Total Agents</div>
            <div class="text-4xl font-black text-white mb-1">{{ number_format($totalAgents) }}</div>
            <div class="text-amber-500 text-sm font-bold">Online Now: {{ $activeAgents }}</div>
        </div>

        <!-- Total Revenue -->
        <div class="stat-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="text-gray-400 font-bold mb-2 text-sm uppercase tracking-wider">Total Revenue</div>
            <div class="text-4xl font-black text-white mb-1">TSh {{ number_format($totalRevenue) }}</div>
            <div class="text-green-500 text-sm font-bold flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                <span>Lifetime</span>
            </div>
        </div>

        <!-- Pending Verifications -->
        <div class="stat-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 text-yellow-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <div class="text-gray-400 font-bold mb-2 text-sm uppercase tracking-wider">Pending Agents</div>
            <div class="text-4xl font-black text-white mb-1">{{ $pendingVerifications }}</div>
            <div class="text-yellow-500 text-sm font-bold">Action Required</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Activity -->
        <div class="lg:col-span-2 glass-card rounded-3xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-white">Recent Activity</h2>
                <a href="{{ route('admin.activity.index') }}" class="text-amber-500 font-bold hover:text-amber-400 text-sm">View All →</a>
            </div>
            
            <div class="space-y-4">
                @forelse($recentActivities as $activity)
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-white/5 hover:bg-white/10 transition border border-white/5">
                        <div class="w-10 h-10 rounded-full {{ $activity->status->value == 'completed' ? 'bg-green-500/20 text-green-500' : 'bg-blue-500/20 text-blue-500' }} flex items-center justify-center">
                            @if($activity->status->value == 'completed')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="text-white font-bold">
                                {{ ucfirst($activity->line_type->value) }} Request 
                                <span class="text-xs px-2 py-0.5 rounded-full bg-white/10 ml-2">{{ $activity->status->value }}</span>
                            </p>
                            <p class="text-gray-400 text-sm">
                                Customer: {{ $activity->customer->user->name ?? 'Unknown' }} 
                                @if($activity->agent)
                                    | Agent: {{ $activity->agent->user->name }}
                                @endif
                            </p>
                        </div>
                        <span class="text-gray-500 text-xs font-bold">{{ $activity->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">No recent activity</div>
                @endforelse
            </div>
        </div>

        <!-- System Health -->
        <div class="glass-card rounded-3xl p-6">
            <h2 class="text-xl font-bold text-white mb-6">System Health</h2>
            
            <div class="space-y-6">
                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-400 font-bold text-sm">Server Load</span>
                        <span class="text-white font-bold text-sm">{{ $systemHealth['server_load'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-800 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $systemHealth['server_load'] }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-400 font-bold text-sm">Database Connections</span>
                        <span class="text-white font-bold text-sm">{{ $systemHealth['db_connections'] }}/100</span>
                    </div>
                    <div class="w-full bg-gray-800 rounded-full h-2">
                        <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $systemHealth['db_connections'] }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-400 font-bold text-sm">API Latency</span>
                        <span class="text-white font-bold text-sm">{{ $systemHealth['api_latency'] }}ms</span>
                    </div>
                    <div class="w-full bg-gray-800 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ min(100, $systemHealth['api_latency']/5) }}%"></div>
                    </div>
                </div>
            </div>

                <h3 class="font-bold text-white mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('admin.users.index') }}" class="flex items-center justify-center p-3 rounded-xl bg-white/5 text-gray-300 font-bold text-sm hover:bg-white/10 transition border border-white/5">Manage Users</a>
                    <a href="{{ route('admin.agents.index') }}" class="flex items-center justify-center p-3 rounded-xl bg-white/5 text-gray-300 font-bold text-sm hover:bg-white/10 transition border border-white/5">Manage Agents</a>
                    <a href="{{ route('admin.agents.verification') }}" class="flex items-center justify-center p-3 rounded-xl bg-white/5 text-gray-300 font-bold text-sm hover:bg-white/10 transition border border-white/5">Verify Agents</a>
                    <a href="{{ route('admin.support.index') }}" class="flex items-center justify-center p-3 rounded-xl bg-white/5 text-gray-300 font-bold text-sm hover:bg-white/10 transition border border-white/5">Support Inbox</a>
                    <a href="{{ route('admin.notifications.push') }}" class="flex items-center justify-center p-3 rounded-xl bg-indigo-500/20 text-indigo-400 font-bold text-sm hover:bg-indigo-500/30 transition border border-indigo-500/30">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        Push Notifications
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="flex items-center justify-center p-3 rounded-xl bg-white/5 text-gray-300 font-bold text-sm hover:bg-white/10 transition border border-white/5">Settings</a>
                </div>
        </div>
    </div>
</div>
@endsection
