@extends('layouts.dashboard')

@section('title', 'Withdrawal Requests - SKY LAINI')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-white mb-2">Withdrawal Requests</h1>
            <p class="text-gray-400 font-medium">Manage agent payout requests</p>
        </div>
    </div>

    <div class="glass-card rounded-3xl p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-400 border-b border-white/10 text-sm uppercase tracking-wider">
                        <th class="pb-4 font-bold">Agent</th>
                        <th class="pb-4 font-bold">Amount</th>
                        <th class="pb-4 font-bold">Method</th>
                        <th class="pb-4 font-bold">Account Details</th>
                        <th class="pb-4 font-bold">Status</th>
                        <th class="pb-4 font-bold">Date</th>
                        <th class="pb-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($withdrawals as $withdrawal)
                    <tr class="group hover:bg-white/5 transition">
                        <td class="py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-black font-bold text-xs">
                                    {{ substr($withdrawal->agent->user->name ?? 'A', 0, 1) }}
                                </div>
                                <span class="text-gray-300 font-medium">{{ $withdrawal->agent->user->name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td class="py-4 text-white font-bold">TSh {{ number_format($withdrawal->amount) }}</td>
                        <td class="py-4 text-gray-400">{{ $withdrawal->payment_method ?? 'Mobile Money' }}</td>
                        <td class="py-4 text-gray-400 text-sm">
                            {{ $withdrawal->account_name }}<br>
                            <span class="text-xs text-gray-500">{{ $withdrawal->account_number }}</span>
                        </td>
                        <td class="py-4">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-500/20 text-yellow-500',
                                    'approved' => 'bg-green-500/20 text-green-500',
                                    'rejected' => 'bg-red-500/20 text-red-500',
                                ];
                                $color = $statusColors[$withdrawal->status] ?? 'bg-gray-500/20 text-gray-500';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $color }}">
                                {{ ucfirst($withdrawal->status) }}
                            </span>
                        </td>
                        <td class="py-4 text-gray-400 text-sm">{{ $withdrawal->created_at->format('M d, Y H:i') }}</td>
                        <td class="py-4 text-right">
                            @if($withdrawal->status == 'pending')
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('admin.withdrawals.approve', $withdrawal) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-green-500/10 text-green-500 hover:bg-green-500/20 font-bold text-xs transition">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.withdrawals.reject', $withdrawal) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-red-500/10 text-red-500 hover:bg-red-500/20 font-bold text-xs transition">Reject</button>
                                    </form>
                                </div>
                            @else
                                <span class="text-gray-600 text-xs font-bold">Processed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-500">No withdrawal requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $withdrawals->links() }}
        </div>
    </div>
</div>
@endsection
