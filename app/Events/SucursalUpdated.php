<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class SucursalUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $sucursalId;
    public $data;

    public function __construct($sucursalId, $data)
    {
        $this->sucursalId = $sucursalId;
        $this->data = $data;
    }

    public function broadcastOn()
    {
        return new Channel('sucursal.'.$this->sucursalId);
    }
}