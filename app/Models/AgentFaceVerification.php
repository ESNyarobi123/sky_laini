<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentFaceVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'face_center',
        'face_left',
        'face_right',
        'face_up',
        'face_down',
        'status',
        'rejection_reason',
        'verified_by',
        'verified_at',
        'device_info',
        'ip_address',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the agent that owns the face verification.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the admin who verified this face verification.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if all required face images are uploaded.
     */
    public function isComplete(): bool
    {
        return $this->face_center 
            && $this->face_left 
            && $this->face_right 
            && $this->face_up 
            && $this->face_down;
    }

    /**
     * Get completion percentage.
     */
    public function getCompletionPercentage(): int
    {
        $total = 5;
        $uploaded = 0;
        
        if ($this->face_center) $uploaded++;
        if ($this->face_left) $uploaded++;
        if ($this->face_right) $uploaded++;
        if ($this->face_up) $uploaded++;
        if ($this->face_down) $uploaded++;
        
        return ($uploaded / $total) * 100;
    }

    /**
     * Get list of missing face images.
     */
    public function getMissingImages(): array
    {
        $missing = [];
        
        if (!$this->face_center) $missing[] = 'center';
        if (!$this->face_left) $missing[] = 'left';
        if (!$this->face_right) $missing[] = 'right';
        if (!$this->face_up) $missing[] = 'up';
        if (!$this->face_down) $missing[] = 'down';
        
        return $missing;
    }

    /**
     * Get all face images as array.
     */
    public function getAllFaceImages(): array
    {
        return [
            'center' => $this->face_center,
            'left' => $this->face_left,
            'right' => $this->face_right,
            'up' => $this->face_up,
            'down' => $this->face_down,
        ];
    }

    /**
     * Check if verification is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if verification is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if verification is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
