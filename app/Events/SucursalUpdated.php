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
    public $socket;

    public function __construct($sucursalId, $data)
    {
        $this->sucursalId = $sucursalId;
        $this->data = $data;
        $this->socket = request()->header('X-Socket-ID'); // Obtener desde header
    }

    public function broadcastOn()
    {
        return [
            new Channel('sucursales'),
            new Channel('sucursal.'.$this->sucursalId)
        ];
    }

    public function broadcastAs()
    {
        return 'sucursal.updated';
    }

    public function broadcastWith()
    {
        return [
            'sucursal_id' => $this->sucursalId,
            'data' => $this->data,
            'timestamp' => now()->toDateTimeString()
        ];
    }
}