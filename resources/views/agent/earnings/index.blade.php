@extends('layouts.dashboard')

@section('title', 'My Earnings - SKY LAINI')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-white mb-2">My Earnings</h1>
            <p class="text-gray-400 font-medium">Track your income and withdrawals</p>
        </div>
        <button class="px-4 py-2 bg-green-500 hover:bg-green-400 text-black font-bold rounded-xl transition shadow-lg shadow-green-500/20">
            Request Withdrawal
        </button>
    </div>

    <!-- Wallet Card -->
    <div class="glass-card rounded-3xl p-8 border border-white/10 relative overflow-hidden">
        <div class="absolute top-0 right-0 p-8 opacity-10">
            <svg class="w-64 h-64 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="relative z-10">
            <p class="text-gray-400 font-bold text-sm uppercase tracking-wider mb-2">Current Balance</p>
            <h2 class="text-5xl font-black text-white mb-6">TSh {{ number_format($wallet->balance) }}</h2>
            <div class="flex gap-4">
                <div class="px-4 py-2 rounded-lg bg-white/5 border border-white/10">
                    <p class="text-gray-500 text-xs font-bold uppercase">Pending</p>
                    <p class="text-white font-bold">TSh 0</p>
                </div>
                <div class="px-4 py-2 rounded-lg bg-white/5 border border-white/10">
                    <p class="text-gray-500 text-xs font-bold uppercase">Total Withdrawn</p>
                    <p class="text-white font-bold">TSh 0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions -->
    <div class="glass-card rounded-3xl p-6 border border-white/10">
        <h3 class="text-xl font-bold text-white mb-6">Transaction History</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-400 border-b border-white/10 text-sm uppercase tracking-wider">
                        <th class="pb-4 font-bold">Type</th>
                        <th class="pb-4 font-bold">Description</th>
                        <th class="pb-4 font-bold">Amount</th>
                        <th class="pb-4 font-bold">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($transactions as $transaction)
                    <tr class="group hover:bg-white/5 transition">
                        <td class="py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $transaction->transaction_type == 'credit' ? 'bg-green-500/20 text-green-500' : 'bg-red-500/20 text-red-500' }}">
                                {{ ucfirst($transaction->transaction_type) }}
                            </span>
                        </td>
                        <td class="py-4 text-gray-300">{{ $transaction->description }}</td>
                        <td class="py-4 font-bold {{ $transaction->transaction_type == 'credit' ? 'text-green-500' : 'text-red-500' }}">
                            {{ $transaction->transaction_type == 'credit' ? '+' : '-' }} TSh {{ number_format($transaction->amount) }}
                        </td>
                        <td class="py-4 text-gray-400 text-sm">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-gray-500">No transactions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
