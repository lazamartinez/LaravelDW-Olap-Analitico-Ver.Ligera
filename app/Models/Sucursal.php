<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sucursal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'direccion',
        'ciudad',
        'pais',
        'codigo_postal',
        'telefono',
        'email',
        'docker_container_id',
        'docker_image',
        'configuracion',
        'activa'
    ];

    protected $casts = [
        'configuracion' => 'array',
        'activa' => 'boolean'
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }

    public function hechoVentas()
    {
        return $this->hasMany(HechoVenta::class);
    }

    public function transaccionesOrigen()
    {
        return $this->hasMany(Transaction::class, 'origen_sucursal_id');
    }

    public function transaccionesDestino()
    {
        return $this->hasMany(Transaction::class, 'destino_sucursal_id');
    }
}
