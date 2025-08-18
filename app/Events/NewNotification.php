<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class NewNotification implements ShouldBroadcastNow
{
    public $notification;

    public function __construct(array $notification) { $this->notification = $notification; }

    public function broadcastOn() {
        logger($this->notification['user_id']);
        return new Channel("notifications.{$this->notification['user_id']}");
    }

    public function broadcastAs() { return 'NewNotification'; }

    public function broadcastWith() {
        return $this->notification;
    }
}

