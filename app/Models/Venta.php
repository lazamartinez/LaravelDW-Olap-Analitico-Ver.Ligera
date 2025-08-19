<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sucursal_id',
        'codigo_transaccion',
        'fecha_venta',
        'total',
        'impuestos',
        'descuentos',
        'metodo_pago',
        'estado',
        'empleado_id',
        'detalles'
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
        'detalles' => 'array'
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'venta_producto')
            ->withPivot('cantidad', 'precio_unitario', 'descuento')
            ->withTimestamps();
    }

    public function empleado()
    {
        return $this->belongsTo(User::class, 'empleado_id');
    }

    public function hechoVentas()
    {
        return $this->hasMany(HechoVenta::class);
    }
}