<?php


namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class NearbyDriverUpdateEvent implements ShouldBroadcastNow
{
    use SerializesModels;

    public $driver;
    public $userId;

    public function __construct($driver, $userId)
    {
        $this->driver = $driver;
        $this->userId = $userId;
        logger($this->driver);
    }

    public function broadcastOn()
    {
        logger('******S******'.$this->userId);
        return new Channel('drivers-channel-' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'driver-update';
    }
    public function broadcastWith()
    {
        return [
            'id' => $this->driver['id'],
            'name' => $this->driver['name'],
            'latitude' => $this->driver['latitude'],
            'longitude' => $this->driver['longitude'],
        ];
    }

}
