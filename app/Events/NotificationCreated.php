<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;      // ← imported
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;  // ← add Dispatchable

    public Notification $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function broadcastOn(): Channel
    {
        // global → public
        if ($this->notification->user_id === null) {
            return new Channel('notifications');
        }
        // personal → private
        return new PrivateChannel('notifications.' . $this->notification->user_id);
    }

    public function broadcastWith(): array
    {
        return [
            'id'        => $this->notification->id,
            'type'      => $this->notification->type,
            'title'     => $this->notification->title,
            'message'   => $this->notification->message,
            'level'     => $this->notification->level,
            'is_read'   => $this->notification->is_read,
            'timestamp' => $this->notification->created_at->diffForHumans(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'NotificationCreated';
    }
}
