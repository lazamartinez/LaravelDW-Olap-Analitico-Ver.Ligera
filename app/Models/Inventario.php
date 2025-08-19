<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory;

    protected $fillable = [
        'sucursal_id',
        'producto_id',
        'cantidad',
        'minimo_stock',
        'ubicacion'
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function alertaStockBajo()
    {
        return $this->cantidad <= $this->minimo_stock;
    }
}