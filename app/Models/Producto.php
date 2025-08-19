<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sucursal_id',
        'codigo',
        'nombre',
        'descripcion',
        'precio',
        'costo',
        'stock',
        'categoria',
        'marca',
        'atributos'
    ];

    protected $casts = [
        'atributos' => 'array'
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function ventas()
    {
        return $this->belongsToMany(Venta::class, 'venta_producto')
            ->withPivot('cantidad', 'precio_unitario', 'descuento')
            ->withTimestamps();
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }

    public function hechoVentas()
    {
        return $this->hasMany(HechoVenta::class);
    }
}