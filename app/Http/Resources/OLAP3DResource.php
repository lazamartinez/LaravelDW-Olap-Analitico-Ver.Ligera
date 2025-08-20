<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OLAP3DResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'x' => $this->dimension_x,
            'y' => $this->dimension_y,
            'z' => $this->dimension_z,
            'valor' => $this->valor,
            'color' => $this->color,
            'etiqueta' => $this->etiqueta,
            'metadatos' => $this->metadatos
        ];
    }
}