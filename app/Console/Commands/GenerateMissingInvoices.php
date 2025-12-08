<?php

namespace App\Console\Commands;

use App\Models\LineRequest;
use App\Services\InvoiceService;
use Illuminate\Console\Command;

class GenerateMissingInvoices extends Command
{
    protected $signature = 'invoices:generate-missing';
    protected $description = 'Generate invoices for paid line requests that do not have invoices';

    public function handle(InvoiceService $invoiceService)
    {
        $paidRequestsWithoutInvoice = LineRequest::where('payment_status', 'paid')
            ->whereDoesntHave('invoice')
            ->get();

        $this->info("Found {$paidRequestsWithoutInvoice->count()} paid requests without invoices.");

        $count = 0;
        foreach ($paidRequestsWithoutInvoice as $request) {
            try {
                $invoice = $invoiceService->generateInvoice($request);
                $this->info("Generated invoice {$invoice->invoice_number} for request #{$request->request_number}");
                $count++;
            } catch (\Exception $e) {
                $this->error("Failed to generate invoice for request #{$request->id}: {$e->getMessage()}");
            }
        }

        $this->info("Generated {$count} invoices.");

        return Command::SUCCESS;
    }
}
