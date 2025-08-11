<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewNotification implements ShouldBroadcastNow
{
    public $notification;

    public function __construct(array $notification) { $this->notification = $notification; }

    public function broadcastOn() {
        logger('init brocast');
        return new Channel("notifications.{$this->notification['user_id']}");
    }

    public function broadcastAs() { return 'NewNotification'; }

    public function broadcastWith() {
        return $this->notification;
    }
}

