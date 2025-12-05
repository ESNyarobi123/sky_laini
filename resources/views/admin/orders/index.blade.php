@extends('layouts.dashboard')

@section('title', 'All Orders - SKY LAINI')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-white mb-2">All Orders</h1>
            <p class="text-gray-400 font-medium">Manage and track all customer requests</p>
        </div>
    </div>

    <div class="glass-card rounded-3xl p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-400 border-b border-white/10 text-sm uppercase tracking-wider">
                        <th class="pb-4 font-bold">ID</th>
                        <th class="pb-4 font-bold">Customer</th>
                        <th class="pb-4 font-bold">Type</th>
                        <th class="pb-4 font-bold">Agent</th>
                        <th class="pb-4 font-bold">Status</th>
                        <th class="pb-4 font-bold">Date</th>
                        <th class="pb-4 font-bold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($orders as $order)
                    <tr class="group hover:bg-white/5 transition">
                        <td class="py-4 text-white font-bold">#{{ $order->id }}</td>
                        <td class="py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-xs">
                                    {{ substr($order->customer->user->name ?? 'U', 0, 1) }}
                                </div>
                                <span class="text-gray-300 font-medium">{{ $order->customer->user->name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td class="py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-white border border-white/10">
                                {{ ucfirst($order->line_type->value ?? $order->line_type) }}
                            </span>
                        </td>
                        <td class="py-4">
                            @if($order->agent)
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-black font-bold text-xs">
                                        {{ substr($order->agent->user->name, 0, 1) }}
                                    </div>
                                    <span class="text-gray-300 font-medium">{{ $order->agent->user->name }}</span>
                                </div>
                            @else
                                <span class="text-gray-500 italic">Pending Assignment</span>
                            @endif
                        </td>
                        <td class="py-4">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-500/20 text-yellow-500',
                                    'accepted' => 'bg-blue-500/20 text-blue-500',
                                    'completed' => 'bg-green-500/20 text-green-500',
                                    'cancelled' => 'bg-red-500/20 text-red-500',
                                ];
                                $color = $statusColors[$order->status->value ?? $order->status] ?? 'bg-gray-500/20 text-gray-500';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $color }}">
                                {{ ucfirst($order->status->value ?? $order->status) }}
                            </span>
                        </td>
                        <td class="py-4 text-gray-400 text-sm">{{ $order->created_at->format('M d, Y H:i') }}</td>
                        <td class="py-4 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-amber-500 hover:text-amber-400 font-bold text-sm">View Details</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-500">No orders found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
