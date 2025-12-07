<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'line_request_id',
        'customer_id',
        'invoice_number',
        'amount',
        'tax',
        'total',
        'payment_method',
        'transaction_id',
        'status',
        'paid_at',
        'pdf_path',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function lineRequest(): BelongsTo
    {
        return $this->belongsTo(LineRequest::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "{$prefix}-{$date}-{$random}";
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function markAsPaid(string $transactionId = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'transaction_id' => $transactionId,
        ]);
    }
}
