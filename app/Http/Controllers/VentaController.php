<?php

namespace App\Http\Controllers;

use App\Http\Resources\VentaResource;
use App\Models\Venta;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource for web.
     */
    public function index()
    {
        $ventas = Venta::with(['sucursal', 'productos', 'empleado'])->paginate(10);
        return view('ventas.index', compact('ventas'));
    }

    /**
     * Display a listing of the resource for API.
     */
    public function apiIndex(Request $request)
    {
        try {
            $query = Venta::with(['sucursal', 'productos', 'empleado']);

            if ($request->has('sucursal_id')) {
                $query->where('sucursal_id', $request->sucursal_id);
            }

            if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
                $query->whereBetween('fecha_venta', [
                    $request->fecha_inicio,
                    $request->fecha_fin
                ]);
            }

            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            $ventas = $query->orderBy('fecha_venta', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $ventas,
                'message' => 'Ventas obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las ventas',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage for API.
     */
    public function apiStore(Request $request)
    {
        try {
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

            $venta = DB::transaction(function () use ($validated) {
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

                return $venta->load('productos', 'sucursal');
            });

            return response()->json([
                'success' => true,
                'data' => new VentaResource($venta),
                'message' => 'Venta creada exitosamente'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la venta',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Display the specified resource for API.
     */
    public function apiShow(Venta $venta)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => new VentaResource($venta->load('productos', 'sucursal', 'empleado')),
                'message' => 'Venta obtenida exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la venta',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor' // CORREGIDO
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage for API.
     */
    public function apiUpdate(Request $request, Venta $venta)
    {
        return response()->json([
            'success' => false,
            'message' => 'Actualización de ventas no permitida'
        ], 403);
    }

    /**
     * Remove the specified resource from storage for API.
     */
    public function apiDestroy(Venta $venta)
    {
        try {
            $venta->delete();

            return response()->json([
                'success' => true,
                'message' => 'Venta eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la venta',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get sales metrics for API.
     */
    public function apiMetrics(Request $request)
    {
        try {
            $query = Venta::query();

            if ($request->has('sucursal_id')) {
                $query->where('sucursal_id', $request->sucursal_id);
            }

            if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
                $query->whereBetween('fecha_venta', [
                    $request->fecha_inicio,
                    $request->fecha_fin
                ]);
            }

            $metrics = [
                'total_ventas' => $query->sum('total'),
                'total_impuestos' => $query->sum('impuestos'),
                'total_descuentos' => $query->sum('descuentos'),
                'cantidad_ventas' => $query->count(),
                'venta_promedio' => $query->count() > 0 ? $query->sum('total') / $query->count() : 0
            ];

            return response()->json([
                'success' => true,
                'data' => $metrics,
                'message' => 'Métricas de ventas obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las métricas de ventas',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }
}
