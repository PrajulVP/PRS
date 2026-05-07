<?php
/**
 * Real-time Location Broadcast Event
 * Created: 2026-05-07
 */

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $latitude;
    public $longitude;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct($userId, $latitude, $longitude, $timestamp)
    {
        $this->userId = $userId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->timestamp = $timestamp;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Public channel for simplicity, or PrivateChannel('tracking.' . $this->userId) for security
        return [
            new Channel('tracking'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'location.updated';
    }
}
