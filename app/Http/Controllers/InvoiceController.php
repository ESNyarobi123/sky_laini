<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\LineRequest;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {}

    /**
     * List customer invoices.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();
        
        $invoices = Invoice::where('customer_id', $user->customer?->id)
            ->with('lineRequest')
            ->latest()
            ->paginate(15);

        if ($request->expectsJson()) {
            return response()->json($invoices);
        }

        return view('customer.invoices.index', compact('invoices'));
    }

    /**
     * Show single invoice.
     */
    public function show(Request $request, Invoice $invoice): View|JsonResponse
    {
        // Ensure user owns this invoice
        if ($request->user()->customer?->id !== $invoice->customer_id) {
            abort(403, 'Unauthorized');
        }

        $invoice->load(['lineRequest', 'customer.user']);

        if ($request->expectsJson()) {
            return response()->json($invoice);
        }

        return view('customer.invoices.show', compact('invoice'));
    }

    /**
     * Download invoice as PDF/HTML.
     */
    public function download(Request $request, Invoice $invoice): Response
    {
        // Ensure user owns this invoice
        if ($request->user()->customer?->id !== $invoice->customer_id) {
            abort(403, 'Unauthorized');
        }

        $html = $this->invoiceService->renderInvoiceHtml($invoice);

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', "attachment; filename=invoice-{$invoice->invoice_number}.html");
    }

    /**
     * View invoice as printable page.
     */
    public function print(Request $request, Invoice $invoice): Response
    {
        // Ensure user owns this invoice
        if ($request->user()->customer?->id !== $invoice->customer_id) {
            abort(403, 'Unauthorized');
        }

        $html = $this->invoiceService->renderInvoiceHtml($invoice);

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Generate invoice for line request.
     */
    public function generate(Request $request, LineRequest $lineRequest): JsonResponse
    {
        // Ensure user is customer of this request
        if ($request->user()->customer?->id !== $lineRequest->customer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Only generate for paid requests
        if ($lineRequest->payment_status !== 'paid') {
            return response()->json(['message' => 'Invoice available only for paid requests'], 400);
        }

        $invoice = $this->invoiceService->generateInvoice($lineRequest);

        return response()->json([
            'invoice' => $invoice,
            'message' => 'Invoice generated successfully',
        ]);
    }
}
