<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class NewNotification implements ShouldBroadcastNow
{
    public $notification;

    public function __construct($notification) { $this->notification = $notification; }

    public function broadcastOn() {
        logger($this->notification->recipient_id);
        return new Channel("notifications.{$this->notification->recipient_id}");
    }

    public function broadcastAs() { return 'NewNotification'; }

    public function broadcastWith() {
        // Convertir l'objet Notification en un tableau
        return $this->notification->toArray(); // S'assurer que la méthode toArray() existe
    }
}

