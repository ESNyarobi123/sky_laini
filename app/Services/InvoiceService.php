<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\LineRequest;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Generate invoice for a completed line request.
     */
    public function generateInvoice(LineRequest $lineRequest): Invoice
    {
        // Get or create invoice
        $invoice = Invoice::firstOrCreate(
            ['line_request_id' => $lineRequest->id],
            [
                'customer_id' => $lineRequest->customer_id,
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'amount' => $lineRequest->service_fee ?? 1000,
                'tax' => 0, // No VAT for now
                'total' => $lineRequest->service_fee ?? 1000,
                'payment_method' => 'mobile_money',
                'status' => $lineRequest->payment_status === 'paid' ? 'paid' : 'pending',
                'transaction_id' => $lineRequest->payment_order_id,
                'paid_at' => $lineRequest->payment_status === 'paid' ? now() : null,
                'metadata' => [
                    'line_type' => $lineRequest->line_type?->value,
                    'request_number' => $lineRequest->request_number,
                    'agent_name' => $lineRequest->agent?->user?->name,
                    'customer_phone' => $lineRequest->customer_phone,
                ],
            ]
        );

        return $invoice;
    }

    /**
     * Generate PDF for invoice.
     */
    public function generatePdf(Invoice $invoice): string
    {
        $html = $this->renderInvoiceHtml($invoice);
        
        // Store PDF (using TCPDF or similar library if available)
        $filename = "invoices/{$invoice->invoice_number}.pdf";
        
        // For now, we'll store HTML as a fallback
        // In production, use barryvdh/laravel-dompdf or similar
        Storage::disk('public')->put(
            str_replace('.pdf', '.html', $filename), 
            $html
        );

        $invoice->update(['pdf_path' => $filename]);

        return $filename;
    }

    /**
     * Render invoice as HTML.
     */
    public function renderInvoiceHtml(Invoice $invoice): string
    {
        $lineRequest = $invoice->lineRequest;
        $customer = $invoice->customer;

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice #{$invoice->invoice_number}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f5f5; padding: 40px; }
        .invoice-container { max-width: 800px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #f59e0b, #d97706); padding: 40px; color: white; }
        .logo { font-size: 28px; font-weight: bold; letter-spacing: 2px; }
        .invoice-title { font-size: 36px; font-weight: 300; margin-top: 10px; }
        .invoice-number { opacity: 0.9; margin-top: 5px; }
        .content { padding: 40px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; }
        .info-section h3 { color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .info-section p { color: #111827; font-size: 14px; line-height: 1.6; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        .items-table th { background: #f3f4f6; padding: 15px; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; }
        .items-table td { padding: 20px 15px; border-bottom: 1px solid #e5e7eb; }
        .items-table .amount { text-align: right; font-weight: 600; }
        .totals { text-align: right; }
        .totals .row { display: flex; justify-content: flex-end; gap: 40px; padding: 10px 0; }
        .totals .label { color: #6b7280; }
        .totals .value { min-width: 120px; text-align: right; }
        .totals .total { font-size: 24px; font-weight: bold; color: #f59e0b; border-top: 2px solid #e5e7eb; padding-top: 15px; }
        .footer { background: #f9fafb; padding: 30px 40px; text-align: center; }
        .footer p { color: #6b7280; font-size: 14px; }
        .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .status-paid { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <div class="logo">SKY LAINI</div>
            <div class="invoice-title">RISITI / INVOICE</div>
            <div class="invoice-number">{$invoice->invoice_number}</div>
        </div>
        
        <div class="content">
            <div class="info-grid">
                <div class="info-section">
                    <h3>Bill To / Mteja</h3>
                    <p>
                        <strong>{$customer?->user?->name}</strong><br>
                        {$customer?->user?->email}<br>
                        {$lineRequest?->customer_phone}
                    </p>
                </div>
                <div class="info-section" style="text-align: right;">
                    <h3>Invoice Details / Maelezo</h3>
                    <p>
                        <strong>Date / Tarehe:</strong> {$invoice->created_at->format('d M Y')}<br>
                        <strong>Status / Hali:</strong> 
                        <span class="status-badge status-{$invoice->status}">
                            {$invoice->status}
                        </span>
                    </p>
                </div>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description / Maelezo</th>
                        <th>Line Type / Aina</th>
                        <th class="amount">Amount / Kiasi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>SIM Registration Service</strong><br>
                            <span style="color: #6b7280; font-size: 13px;">Request #{$lineRequest?->request_number}</span>
                        </td>
                        <td style="text-transform: capitalize;">{$lineRequest?->line_type?->value}</td>
                        <td class="amount">TZS {$this->formatNumber($invoice->amount)}</td>
                    </tr>
                </tbody>
            </table>

            <div class="totals">
                <div class="row">
                    <span class="label">Subtotal / Jumla ndogo:</span>
                    <span class="value">TZS {$this->formatNumber($invoice->amount)}</span>
                </div>
                <div class="row">
                    <span class="label">Tax / Kodi:</span>
                    <span class="value">TZS {$this->formatNumber($invoice->tax)}</span>
                </div>
                <div class="row total">
                    <span class="label">Total / Jumla:</span>
                    <span class="value">TZS {$this->formatNumber($invoice->total)}</span>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Asante kwa biashara yako! / Thank you for your business!</p>
            <p style="margin-top: 10px; font-size: 12px;">
                Sky Laini | support@skylaini.co.tz | +255 123 456 789
            </p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Format number with commas.
     */
    private function formatNumber($number): string
    {
        return number_format($number, 0, '.', ',');
    }
}
