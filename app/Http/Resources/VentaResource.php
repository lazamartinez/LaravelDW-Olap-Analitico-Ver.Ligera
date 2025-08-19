<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VentaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sucursal_id' => $this->sucursal_id,
            'sucursal' => $this->whenLoaded('sucursal', function () {
                return $this->sucursal->nombre;
            }),
            'codigo_transaccion' => $this->codigo_transaccion,
            'fecha_venta' => $this->fecha_venta,
            'total' => $this->total,
            'impuestos' => $this->impuestos,
            'descuentos' => $this->descuentos,
            'metodo_pago' => $this->metodo_pago,
            'estado' => $this->estado,
            'empleado_id' => $this->empleado_id,
            'empleado' => $this->whenLoaded('empleado', function () {
                return $this->empleado->name;
            }),
            'productos' => $this->whenLoaded('productos', function () {
                return $this->productos->map(function ($producto) {
                    return [
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'cantidad' => $producto->pivot->cantidad,
                        'precio_unitario' => $producto->pivot->precio_unitario,
                        'descuento' => $producto->pivot->descuento,
                        'subtotal' => ($producto->pivot->cantidad * $producto->pivot->precio_unitario) - $producto->pivot->descuento
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}