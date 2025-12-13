<?php

namespace App\Models;

use App\LineType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'customer_id',
        'agent_id',
        'line_request_id',
        'line_type',
        'scheduled_date',
        'scheduled_time',
        'time_slot',
        'latitude',
        'longitude',
        'address',
        'phone',
        'status',
        'notes',
        'is_recurring',
        'recurrence_type',
        'agent_confirmed_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by',
        'reminder_sent',
        'reminder_sent_at',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'datetime:H:i',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_recurring' => 'boolean',
        'reminder_sent' => 'boolean',
        'agent_confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = self::generateBookingNumber();
            }
        });
    }

    /**
     * Generate unique booking number
     */
    public static function generateBookingNumber(): string
    {
        $prefix = 'BK';
        $date = now()->format('ymd');
        $random = strtoupper(substr(uniqid(), -4));
        
        $number = "{$prefix}{$date}{$random}";
        
        // Ensure uniqueness
        while (static::where('booking_number', $number)->exists()) {
            $random = strtoupper(substr(uniqid(), -4));
            $number = "{$prefix}{$date}{$random}";
        }
        
        return $number;
    }

    /**
     * Get the customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the agent
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the associated line request
     */
    public function lineRequest(): BelongsTo
    {
        return $this->belongsTo(LineRequest::class);
    }

    /**
     * Scope for pending bookings
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for confirmed bookings
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope for today's bookings
     */
    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', today());
    }

    /**
     * Scope for upcoming bookings
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_date', '>=', today())
                     ->whereIn('status', ['pending', 'confirmed']);
    }

    /**
     * Scope for a specific agent
     */
    public function scopeForAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    /**
     * Scope for a specific customer
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Check if booking is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if booking is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if booking can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Check if booking is for today
     */
    public function isToday(): bool
    {
        return $this->scheduled_date->isToday();
    }

    /**
     * Confirm the booking
     */
    public function confirm(int $agentId): void
    {
        $this->update([
            'agent_id' => $agentId,
            'status' => 'confirmed',
            'agent_confirmed_at' => now(),
        ]);
    }

    /**
     * Cancel the booking
     */
    public function cancel(string $reason, string $cancelledBy): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
            'cancelled_by' => $cancelledBy,
        ]);
    }

    /**
     * Mark booking as completed
     */
    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Get time slot label
     */
    public function getTimeSlotLabel(): string
    {
        return match ($this->time_slot) {
            'morning' => 'Asubuhi (8:00 - 12:00)',
            'afternoon' => 'Mchana (12:00 - 16:00)',
            'evening' => 'Jioni (16:00 - 19:00)',
            default => $this->scheduled_time?->format('H:i') ?? 'N/A',
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'confirmed' => 'blue',
            'in_progress' => 'purple',
            'completed' => 'green',
            'cancelled' => 'red',
            'expired' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get formatted scheduled datetime
     */
    public function getScheduledDateTime(): string
    {
        $date = $this->scheduled_date->format('d M Y');
        $time = $this->scheduled_time ? $this->scheduled_time->format('H:i') : $this->getTimeSlotLabel();
        return "{$date} - {$time}";
    }
}
