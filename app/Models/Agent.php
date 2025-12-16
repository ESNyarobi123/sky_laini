<?php

namespace App\Models;

use App\AgentTier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Agent extends Model
{
    /** @use HasFactory<\Database\Factories\AgentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nida_number',
        'phone',
        'tier',
        'rating',
        'total_ratings',
        'total_completed_requests',
        'total_earnings',
        'is_verified',
        'face_verified',
        'is_available',
        'is_online',
        'current_latitude',
        'current_longitude',
        'last_location_update',
        'specialization',
        'service_radius_km',
    ];

    protected function casts(): array
    {
        return [
            'tier' => AgentTier::class,
            'rating' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'current_latitude' => 'decimal:8',
            'current_longitude' => 'decimal:8',
            'is_verified' => 'boolean',
            'is_available' => 'boolean',
            'is_online' => 'boolean',
            'last_location_update' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function lineRequests(): HasMany
    {
        return $this->hasMany(LineRequest::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(AgentLocation::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AgentDocument::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function faceVerification(): HasOne
    {
        return $this->hasOne(AgentFaceVerification::class)->latest();
    }

    public function faceVerifications(): HasMany
    {
        return $this->hasMany(AgentFaceVerification::class);
    }
}

