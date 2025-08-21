<?php

namespace Database\Seeders;

use App\Models\Venta;
use App\Models\Sucursal;
use App\Models\Producto;
use App\Models\DimensionTiempo;
use Illuminate\Database\Seeder;

class VentaSeeder extends Seeder
{
    public function run()
    {
        $sucursales = Sucursal::all();
        $productos = Producto::all();
        $fechas = DimensionTiempo::all();

        // Crear 100 ventas de prueba
        for ($i = 0; $i < 100; $i++) {
            $sucursal = $sucursales->random();
            $fecha = $fechas->random();
            $productosVenta = $productos->random(rand(1, 5));
            
            $venta = Venta::create([
                'sucursal_id' => $sucursal->id,
                'codigo_transaccion' => 'V-' . now()->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'fecha_venta' => $fecha->fecha,
                'total' => 0, // Se calculará después
                'impuestos' => 0.21, // 21%
                'descuentos' => rand(0, 50),
                'metodo_pago' => ['efectivo', 'tarjeta', 'transferencia'][rand(0, 2)],
                'estado' => 'completada',
                'empleado_id' => 1, // Asumiendo que el usuario 1 es admin
                'procesada_olap' => true,
            ]);
            
            $total = 0;
            foreach ($productosVenta as $producto) {
                $cantidad = rand(1, 5);
                $precioUnitario = $producto->precio * (1 - rand(0, 20) / 100); // Aplicar descuento aleatorio
                $descuento = rand(0, 15);
                
                $venta->productos()->attach($producto->id, [
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'descuento' => $descuento,
                ]);
                
                $subtotal = $cantidad * $precioUnitario;
                $total += $subtotal - $descuento;
                
                // Actualizar stock
                $producto->decrement('stock', $cantidad);
            }
            
            // Aplicar impuestos y descuentos generales
            $total = $total * (1 + $venta->impuestos) - $venta->descuentos;
            
            $venta->update(['total' => $total]);
        }
    }
}