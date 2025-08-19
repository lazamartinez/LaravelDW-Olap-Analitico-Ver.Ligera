<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HechoVenta extends Model
{
    use HasFactory;

    protected $fillable = [
        'sucursal_id',
        'producto_id',
        'tiempo_id',
        'cantidad',
        'monto_total',
        'costo_total',
        'ganancia'
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function tiempo()
    {
        return $this->belongsTo(DimensionTiempo::class, 'tiempo_id');
    }
}