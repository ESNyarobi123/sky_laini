@extends('layouts.dashboard')

@section('title', 'Payments - SKY LAINI')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-white mb-2">Payments</h1>
            <p class="text-gray-400 font-medium">Track all system transactions</p>
        </div>
    </div>

    <div class="glass-card rounded-3xl p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-400 border-b border-white/10 text-sm uppercase tracking-wider">
                        <th class="pb-4 font-bold">Transaction ID</th>
                        <th class="pb-4 font-bold">Order ID</th>
                        <th class="pb-4 font-bold">Payer</th>
                        <th class="pb-4 font-bold">Amount</th>
                        <th class="pb-4 font-bold">Method</th>
                        <th class="pb-4 font-bold">Status</th>
                        <th class="pb-4 font-bold">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($payments as $payment)
                    <tr class="group hover:bg-white/5 transition">
                        <td class="py-4 text-white font-bold font-mono text-sm">{{ $payment->transaction_id ?? $payment->id }}</td>
                        <td class="py-4 text-amber-500 font-bold">#{{ $payment->line_request_id }}</td>
                        <td class="py-4 text-gray-300">{{ $payment->lineRequest->customer->user->name ?? 'Unknown' }}</td>
                        <td class="py-4 text-white font-bold">TSh {{ number_format($payment->amount) }}</td>
                        <td class="py-4 text-gray-400">{{ $payment->payment_method ?? 'N/A' }}</td>
                        <td class="py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $payment->status == 'completed' ? 'bg-green-500/20 text-green-500' : 'bg-yellow-500/20 text-yellow-500' }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td class="py-4 text-gray-400 text-sm">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-500">No payments found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
