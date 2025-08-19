<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sucursal_id' => $this->sucursal_id,
            'sucursal' => $this->whenLoaded('sucursal', function () {
                return $this->sucursal->nombre;
            }),
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'costo' => $this->costo,
            'stock' => $this->stock,
            'categoria' => $this->categoria,
            'marca' => $this->marca,
            'atributos' => $this->atributos,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'ventas_count' => $this->whenCounted('ventas'),
            'inventarios' => $this->whenLoaded('inventarios'),
            'metrics' => $this->when($request->routeIs('productos.metrics'), function () {
                return [
                    'total_vendido' => $this->hechoVentas->sum('cantidad'),
                    'total_ventas' => $this->hechoVentas->sum('monto_total'),
                    'total_ganancia' => $this->hechoVentas->sum('ganancia')
                ];
            })
        ];
    }
}