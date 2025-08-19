<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InventarioResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sucursal_id' => $this->sucursal_id,
            'sucursal' => $this->whenLoaded('sucursal', function () {
                return $this->sucursal->nombre;
            }),
            'producto_id' => $this->producto_id,
            'producto' => $this->whenLoaded('producto', function () {
                return [
                    'nombre' => $this->producto->nombre,
                    'codigo' => $this->producto->codigo,
                    'categoria' => $this->producto->categoria
                ];
            }),
            'cantidad' => $this->cantidad,
            'minimo_stock' => $this->minimo_stock,
            'alerta_stock' => $this->cantidad <= $this->minimo_stock,
            'ubicacion' => $this->ubicacion,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}