<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'line_request_id',
        'customer_id',
        'payment_method',
        'amount',
        'status',
        'transaction_id',
        'reference',
        'payment_link',
        'ussd_code',
        'zenopay_response',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'zenopay_response' => 'array',
            'paid_at' => 'datetime',
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
}
