<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SucursalResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'direccion' => $this->direccion,
            'ciudad' => $this->ciudad,
            'pais' => $this->pais,
            'codigo_postal' => $this->codigo_postal,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'docker_container_id' => $this->docker_container_id,
            'docker_image' => $this->docker_image,
            'configuracion' => $this->configuracion,
            'activa' => $this->activa,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'productos_count' => $this->whenCounted('productos'),
            'ventas_count' => $this->whenCounted('ventas'),
            'metrics' => $this->when($request->routeIs('sucursales.metrics'), function () {
                return [
                    'total_ventas' => $this->hechoVentas->sum('monto_total'),
                    'total_ganancia' => $this->hechoVentas->sum('ganancia'),
                    'productos_vendidos' => $this->hechoVentas->sum('cantidad')
                ];
            })
        ];
    }
}