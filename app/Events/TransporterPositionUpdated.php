<?php


namespace App\Events;


use Illuminate\Broadcasting\Channel;

// ou PresenceChannel si nécessaire
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class TransporterPositionUpdated implements ShouldBroadcastNow
{
    use SerializesModels;


    public $notification;

    public function __construct(array $notification) { $this->notification = $notification; }
    public function broadcastOn()
    {
        logger('init brocast'. $this->notification['transporterId']);
        return new Channel('transporter.' .  $this->notification['transporterId']);
    }

    public function broadcastAs()
    {
        return 'transporter.position.updated';
    }

    // Optionnel : on peut filtrer les données envoyées
    public function broadcastWith()
    {
        return $this->notification;
    }
}

