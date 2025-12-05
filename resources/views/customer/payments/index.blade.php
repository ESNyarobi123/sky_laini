@extends('layouts.dashboard')

@section('title', 'Malipo - SKY LAINI')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-black text-white mb-2">Historia ya Malipo 💳</h1>
        <p class="text-gray-400 font-medium">Angalia malipo yako yote hapa.</p>
    </div>

    <!-- Payments Table -->
    <div class="glass-card rounded-3xl p-6 border border-white/10 bg-white/5 backdrop-blur-xl">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left border-b border-white/10">
                        <th class="pb-4 font-bold text-gray-400 text-sm uppercase tracking-wider">Order ID</th>
                        <th class="pb-4 font-bold text-gray-400 text-sm uppercase tracking-wider">Laini</th>
                        <th class="pb-4 font-bold text-gray-400 text-sm uppercase tracking-wider">Kiasi</th>
                        <th class="pb-4 font-bold text-gray-400 text-sm uppercase tracking-wider">Tarehe</th>
                        <th class="pb-4 font-bold text-gray-400 text-sm uppercase tracking-wider">Hali</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($payments as $request)
                        <tr class="group hover:bg-white/5 transition">
                            <td class="py-4 text-white font-mono">{{ $request->payment_order_id ?? 'N/A' }}</td>
                            <td class="py-4">
                                <span class="font-bold text-white">{{ ucfirst($request->line_type->value ?? 'Unknown') }}</span>
                            </td>
                            <td class="py-4 text-amber-500 font-bold">Tsh {{ number_format($request->service_fee ?? 1000) }}</td>
                            <td class="py-4 text-gray-400 text-sm">{{ $request->created_at->format('d M, Y H:i') }}</td>
                            <td class="py-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-500/20 text-yellow-500',
                                        'paid' => 'bg-green-500/20 text-green-500',
                                        'failed' => 'bg-red-500/20 text-red-500',
                                        'cancelled' => 'bg-gray-500/20 text-gray-500',
                                    ];
                                    $statusClass = $statusColors[$request->payment_status] ?? 'bg-gray-500/20 text-gray-500';
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                                    {{ ucfirst($request->payment_status ?? 'Pending') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500">
                                Hakuna malipo yaliyofanyika bado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
