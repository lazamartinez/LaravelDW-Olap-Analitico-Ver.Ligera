<?php

namespace App\Http\Controllers;

use App\Http\Resources\VentaResource;
use App\Models\Venta;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with(['sucursal', 'productos', 'empleado'])->paginate(10);
        return VentaResource::collection($ventas);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sucursal_id' => 'required|exists:sucursals,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'required|numeric|min:0',
            'productos.*.descuento' => 'nullable|numeric|min:0',
            'fecha_venta' => 'required|date',
            'impuestos' => 'required|numeric|min:0',
            'descuentos' => 'nullable|numeric|min:0',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia,credito',
            'empleado_id' => 'nullable|exists:users,id'
        ]);

        return DB::transaction(function () use ($validated) {
            $venta = Venta::create([
                'sucursal_id' => $validated['sucursal_id'],
                'codigo_transaccion' => 'V-' . time(),
                'fecha_venta' => $validated['fecha_venta'],
                'total' => 0,
                'impuestos' => $validated['impuestos'],
                'descuentos' => $validated['descuentos'] ?? 0,
                'metodo_pago' => $validated['metodo_pago'],
                'estado' => 'completada',
                'empleado_id' => $validated['empleado_id'] ?? null,
                'procesada_olap' => false
            ]);

            $total = 0;
            foreach ($validated['productos'] as $productoVenta) {
                $producto = Producto::find($productoVenta['id']);
                
                $venta->productos()->attach($producto->id, [
                    'cantidad' => $productoVenta['cantidad'],
                    'precio_unitario' => $productoVenta['precio_unitario'],
                    'descuento' => $productoVenta['descuento'] ?? 0
                ]);

                // Actualizar stock
                $producto->decrement('stock', $productoVenta['cantidad']);

                $subtotal = $productoVenta['cantidad'] * $productoVenta['precio_unitario'];
                $total += $subtotal - ($productoVenta['descuento'] ?? 0);
            }

            // Sumar impuestos
            $total += $validated['impuestos'];
            
            // Aplicar descuento general si existe
            $total -= $validated['descuentos'] ?? 0;

            $venta->update(['total' => $total]);

            return new VentaResource($venta->load('productos', 'sucursal'));
        });
    }

    public function show(Venta $venta)
    {
        return new VentaResource($venta->load('productos', 'sucursal', 'empleado'));
    }

    public function update(Request $request, Venta $venta)
    {
        // Implementar lógica de actualización si es necesario
        return response()->json(['message' => 'Actualización de ventas no permitida'], 403);
    }

    public function destroy(Venta $venta)
    {
        // Generalmente no se permiten eliminar ventas, pero si es necesario:
        $venta->delete();
        return response()->json(null, 204);
    }
}