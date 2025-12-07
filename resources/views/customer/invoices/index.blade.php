@extends('layouts.dashboard')

@section('title', __('messages.payments.invoice') . ' - SKY LAINI')

@push('styles')
<style>
    .invoices-container {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 24px;
        overflow: hidden;
    }
    
    .invoice-card {
        display: flex;
        align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }
    
    .invoice-card:hover {
        background: rgba(245, 158, 11, 0.05);
    }
    
    .invoice-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
    }
    
    .status-paid {
        background: rgba(34, 197, 94, 0.2);
        color: #22c55e;
    }
    
    .status-pending {
        background: rgba(245, 158, 11, 0.2);
        color: #f59e0b;
    }
    
    .status-cancelled {
        background: rgba(239, 68, 68, 0.2);
        color: #ef4444;
    }
    
    .download-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        font-weight: bold;
        font-size: 13px;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .download-btn:hover {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: black;
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-white mb-2">🧾 {{ __('messages.payments.invoice') }}</h1>
            <p class="text-gray-400 font-medium">Risiti zako za malipo</p>
        </div>
    </div>

    <!-- Invoices List -->
    <div class="invoices-container">
        <div class="p-6 border-b border-white/5">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-white">Risiti Zote</h2>
                <span class="text-gray-400 text-sm">{{ $invoices->total() ?? 0 }} risiti</span>
            </div>
        </div>
        
        @forelse($invoices ?? [] as $invoice)
            <div class="invoice-card">
                <div class="invoice-icon mr-5">
                    <svg class="w-7 h-7 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                
                <div class="flex-1">
                    <div class="font-bold text-white text-lg">{{ $invoice->invoice_number }}</div>
                    <div class="text-gray-400 text-sm">
                        Request #{{ $invoice->lineRequest?->request_number ?? 'N/A' }}
                        • {{ $invoice->created_at->format('d M Y') }}
                    </div>
                </div>
                
                <div class="text-right mr-6">
                    <div class="text-2xl font-bold text-amber-500">TZS {{ number_format($invoice->total) }}</div>
                    <span class="status-badge status-{{ $invoice->status }}">
                        {{ __('messages.status.' . $invoice->status) }}
                    </span>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="{{ route('customer.invoices.print', $invoice) }}" target="_blank" class="download-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View
                    </a>
                    <a href="{{ route('customer.invoices.download', $invoice) }}" class="download-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        {{ __('messages.payments.download_invoice') }}
                    </a>
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-white/5 flex items-center justify-center">
                    <svg class="w-12 h-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Hakuna Risiti</h3>
                <p class="text-gray-500">Bado huna risiti zozote. Risiti zitaonekana hapa baada ya kulipa.</p>
            </div>
        @endforelse
        
        @if($invoices && $invoices->hasPages())
            <div class="p-6 border-t border-white/5">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
