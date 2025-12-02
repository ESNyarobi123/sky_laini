<?php

namespace App\Events;

use App\Models\LineRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LineRequestAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public LineRequest $lineRequest)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('agents.' . $this->lineRequest->agent_id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->lineRequest->id,
            'request_number' => $this->lineRequest->request_number,
            'line_type' => $this->lineRequest->line_type,
            'customer' => [
                'name' => $this->lineRequest->customer->user->name,
                'phone' => $this->lineRequest->customer->phone,
                'latitude' => $this->lineRequest->customer_latitude,
                'longitude' => $this->lineRequest->customer_longitude,
            ],
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
