<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'referral_code',
        'referrer_type',
        'referred_type',
        'bonus_amount',
        'discount_amount',
        'status',
        'completed_at',
        'rewarded_at',
    ];

    protected $casts = [
        'bonus_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'rewarded_at' => 'datetime',
    ];

    /**
     * Get the user who made the referral
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the user who was referred
     */
    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    /**
     * Check if referral is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if referral is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if referral has been rewarded
     */
    public function isRewarded(): bool
    {
        return $this->status === 'rewarded';
    }

    /**
     * Mark referral as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark referral as rewarded
     */
    public function markAsRewarded(): void
    {
        $this->update([
            'status' => 'rewarded',
            'rewarded_at' => now(),
        ]);
    }

    /**
     * Generate a unique referral code
     */
    public static function generateCode(int $length = 8): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Removed confusing characters
        $code = 'SKY'; // Prefix
        
        for ($i = 0; $i < $length - 3; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        // Ensure uniqueness
        while (User::where('referral_code', $code)->exists()) {
            $code = 'SKY';
            for ($i = 0; $i < $length - 3; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
        }

        return $code;
    }

    /**
     * Get referral setting value
     */
    public static function getSetting(string $key, $default = null)
    {
        $setting = ReferralSetting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Calculate bonus for referrer
     */
    public static function getReferrerBonus(string $referrerType): float
    {
        if ($referrerType === 'customer') {
            return (float) self::getSetting('customer_referral_bonus', 500);
        }
        return (float) self::getSetting('agent_referral_bonus', 1000);
    }

    /**
     * Calculate discount/bonus for referred user
     */
    public static function getReferredBonus(string $referredType): float
    {
        if ($referredType === 'customer') {
            return (float) self::getSetting('customer_referred_discount', 300);
        }
        return (float) self::getSetting('agent_referred_bonus', 500);
    }
}
