<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InAppNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'line_request_id',
        'type',
        'title',
        'message',
        'icon',
        'color',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    /**
     * Notification types/constants
     */
    const TYPE_NEW_REQUEST = 'new_request';
    const TYPE_ORDER_CREATED = 'order_created';
    const TYPE_AGENT_ACCEPTED = 'agent_accepted';
    const TYPE_PAYMENT_RECEIVED = 'payment_received';
    const TYPE_PAYMENT_PENDING = 'payment_pending';
    const TYPE_JOB_COMPLETED = 'job_completed';
    const TYPE_JOB_CANCELLED = 'job_cancelled';
    const TYPE_REQUEST_RELEASED = 'request_released';
    const TYPE_AGENT_ARRIVING = 'agent_arriving';
    const TYPE_RATING_RECEIVED = 'rating_received';
    const TYPE_ADMIN_BROADCAST = 'admin_broadcast';
    const TYPE_ADMIN_MESSAGE = 'admin_message';

    /**
     * Icon and color mappings for each notification type
     */
    public static function getTypeConfig(string $type): array
    {
        return match ($type) {
            self::TYPE_NEW_REQUEST => [
                'icon' => 'bell_ring',
                'color' => '#4F46E5', // Indigo
            ],
            self::TYPE_ORDER_CREATED => [
                'icon' => 'check_circle',
                'color' => '#10B981', // Green
            ],
            self::TYPE_AGENT_ACCEPTED => [
                'icon' => 'person_check',
                'color' => '#3B82F6', // Blue
            ],
            self::TYPE_PAYMENT_RECEIVED => [
                'icon' => 'payments',
                'color' => '#22C55E', // Emerald
            ],
            self::TYPE_PAYMENT_PENDING => [
                'icon' => 'hourglass',
                'color' => '#F59E0B', // Amber
            ],
            self::TYPE_JOB_COMPLETED => [
                'icon' => 'task_alt',
                'color' => '#10B981', // Green
            ],
            self::TYPE_JOB_CANCELLED => [
                'icon' => 'cancel',
                'color' => '#EF4444', // Red
            ],
            self::TYPE_REQUEST_RELEASED => [
                'icon' => 'refresh',
                'color' => '#8B5CF6', // Purple
            ],
            self::TYPE_AGENT_ARRIVING => [
                'icon' => 'directions_run',
                'color' => '#06B6D4', // Cyan
            ],
            self::TYPE_RATING_RECEIVED => [
                'icon' => 'star',
                'color' => '#F59E0B', // Amber
            ],
            self::TYPE_ADMIN_BROADCAST => [
                'icon' => 'campaign',
                'color' => '#6366F1', // Indigo
            ],
            self::TYPE_ADMIN_MESSAGE => [
                'icon' => 'admin_panel_settings',
                'color' => '#8B5CF6', // Purple
            ],
            default => [
                'icon' => 'notifications',
                'color' => '#6B7280', // Gray
            ],
        };
    }

    /**
     * Get user relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get line request relationship
     */
    public function lineRequest(): BelongsTo
    {
        return $this->belongsTo(LineRequest::class);
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Get formatted time ago
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
}
