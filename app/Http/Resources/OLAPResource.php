<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OLAPResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sucursal' => $this->sucursal->nombre,
            'producto' => $this->producto->nombre,
            'fecha' => $this->tiempo->fecha,
            'cantidad' => $this->cantidad,
            'monto_total' => $this->monto_total,
            'costo_total' => $this->costo_total,
            'ganancia' => $this->ganancia,
            'margen_ganancia' => $this->monto_total > 0 
                ? ($this->ganancia / $this->monto_total) * 100 
                : 0,
            'metadatos' => [
                'categoria' => $this->producto->categoria,
                'ciudad' => $this->sucursal->ciudad,
                'mes' => $this->tiempo->mes,
                'anio' => $this->tiempo->anio
            ]
        ];
    }
}