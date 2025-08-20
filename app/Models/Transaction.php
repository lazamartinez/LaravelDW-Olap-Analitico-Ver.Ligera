<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'origen_sucursal_id',
        'destino_sucursal_id',
        'codigo',
        'estado',
        'notas',
        'prioridad',
        'user_id'
    ];

    public function origenSucursal()
    {
        return $this->belongsTo(Sucursal::class, 'origen_sucursal_id');
    }

    public function destinoSucursal()
    {
        return $this->belongsTo(Sucursal::class, 'destino_sucursal_id');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'transaction_producto')
                    ->withPivot('cantidad', 'estado')
                    ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}