<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class TransactionProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $transaction;
    public $action;
    public $socket;

    public function __construct($transaction, $action)
    {
        $this->transaction = $transaction;
        $this->action = $action;
        $this->socket = request()->header('X-Socket-ID'); 
    }

    public function broadcastOn()
    {
        $channels = [
            new Channel('transactions'),
            new Channel('sucursal.'.$this->transaction->origen_sucursal_id),
            new Channel('sucursal.'.$this->transaction->destino_sucursal_id)
        ];

        if ($this->transaction->user_id) {
            $channels[] = new Channel('user.'.$this->transaction->user_id);
        }

        return $channels;
    }

    public function broadcastAs()
    {
        return 'transaction.'.$this->action;
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->transaction->id,
            'codigo' => $this->transaction->codigo,
            'origen_sucursal_id' => $this->transaction->origen_sucursal_id,
            'destino_sucursal_id' => $this->transaction->destino_sucursal_id,
            'estado' => $this->transaction->estado,
            'action' => $this->action,
            'timestamp' => now()->toDateTimeString()
        ];
    }
}