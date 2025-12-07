<?php

namespace App\Models;

use App\LineType;
use App\RequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LineRequest extends Model
{
    /** @use HasFactory<\Database\Factories\LineRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'agent_id',
        'request_number',
        'line_type',
        'status',
        'customer_latitude',
        'customer_longitude',
        'customer_address',
        'customer_phone',
        'confirmation_code',
        'ussd_instructions',
        'payment_link',
        'service_fee',
        'commission',
        'accepted_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
        'payment_order_id',
        'payment_status',
        'payment_attempts',
    ];

    protected function casts(): array
    {
        return [
            'line_type' => LineType::class,
            'status' => RequestStatus::class,
            'customer_latitude' => 'decimal:8',
            'customer_longitude' => 'decimal:8',
            'service_fee' => 'decimal:2',
            'commission' => 'decimal:2',
            'accepted_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function rating(): HasOne
    {
        return $this->hasOne(Rating::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(SystemNotification::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
