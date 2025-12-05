@extends('layouts.dashboard')

@section('title', 'Order Details #' . $order->id . ' - SKY LAINI')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="text-gray-400 hover:text-white mb-2 inline-block transition">← Back to Orders</a>
            <h1 class="text-3xl font-black text-white">Order #{{ $order->id }}</h1>
        </div>
        <div class="flex gap-3">
             <span class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white font-bold">
                Status: {{ ucfirst($order->status->value ?? $order->status) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="glass-card rounded-3xl p-6">
                <h2 class="text-xl font-bold text-white mb-4">Request Details</h2>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Service Type</p>
                        <p class="text-white font-bold text-lg">{{ ucfirst($order->line_type->value ?? $order->line_type) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Amount</p>
                        <p class="text-white font-bold text-lg">TSh {{ number_format($order->amount ?? 0) }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-gray-400 text-sm mb-1">Description/Notes</p>
                        <p class="text-gray-300">{{ $order->notes ?? 'No additional notes provided.' }}</p>
                    </div>
                </div>
            </div>

            <!-- Map/Location Placeholder -->
            <div class="glass-card rounded-3xl p-6 h-64 flex items-center justify-center bg-white/5">
                <p class="text-gray-500 font-bold">Map Location View (Coming Soon)</p>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <!-- Customer -->
            <div class="glass-card rounded-3xl p-6">
                <h3 class="text-gray-400 font-bold text-sm uppercase tracking-wider mb-4">Customer</h3>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-lg">
                        {{ substr($order->customer->user->name ?? 'U', 0, 1) }}
                    </div>
                    <div>
                        <p class="text-white font-bold">{{ $order->customer->user->name ?? 'Unknown' }}</p>
                        <p class="text-gray-400 text-sm">{{ $order->customer->user->email ?? '' }}</p>
                    </div>
                </div>
                <div class="pt-4 border-t border-white/10">
                    <p class="text-gray-400 text-sm">Phone: <span class="text-white">{{ $order->customer->phone_number ?? 'N/A' }}</span></p>
                </div>
            </div>

            <!-- Agent -->
            <div class="glass-card rounded-3xl p-6">
                <h3 class="text-gray-400 font-bold text-sm uppercase tracking-wider mb-4">Assigned Agent</h3>
                @if($order->agent)
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-black font-bold text-lg">
                            {{ substr($order->agent->user->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-white font-bold">{{ $order->agent->user->name }}</p>
                            <p class="text-gray-400 text-sm">{{ $order->agent->user->email }}</p>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-white/10">
                        <p class="text-gray-400 text-sm">Phone: <span class="text-white">{{ $order->agent->phone_number ?? 'N/A' }}</span></p>
                    </div>
                @else
                    <p class="text-gray-500 italic">No agent assigned yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
