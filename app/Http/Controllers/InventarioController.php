<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\InventarioResource;

class InventarioController extends Controller
{
    /**
     * Display a listing of the inventory for web.
     */
    public function index()
    {
        $inventarios = Inventario::with(['sucursal', 'producto'])->paginate(15);
        return view('inventario.index', compact('inventarios'));
    }

    /**
     * Display a listing of the inventory for API.
     */
    public function apiIndex(Request $request)
    {
        try {
            $query = Inventario::with(['sucursal', 'producto'])
                ->orderBy('sucursal_id')
                ->orderBy('producto_id');

            if ($request->has('sucursal_id')) {
                $query->where('sucursal_id', $request->sucursal_id);
            }

            if ($request->has('producto_id')) {
                $query->where('producto_id', $request->producto_id);
            }

            if ($request->has('alertas')) {
                $query->whereRaw('cantidad <= minimo_stock');
            }

            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->whereHas('producto', function($q) use ($searchTerm) {
                    $q->where('nombre', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('codigo', 'LIKE', "%{$searchTerm}%");
                });
            }

            $inventarios = $query->get();

            return response()->json([
                'success' => true,
                'data' => $inventarios,
                'message' => 'Inventario obtenido exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el inventario',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Store a newly created inventory record.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'sucursal_id' => 'required|exists:sucursals,id',
                'producto_id' => 'required|exists:productos,id',
                'cantidad' => 'required|integer|min:0',
                'minimo_stock' => 'required|integer|min:0',
                'ubicacion' => 'nullable|string|max:100'
            ]);

            $exists = Inventario::where('sucursal_id', $validated['sucursal_id'])
                ->where('producto_id', $validated['producto_id'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un registro de inventario para este producto en la sucursal seleccionada'
                ], 409);
            }

            $inventario = Inventario::create($validated);
            $this->actualizarStockProducto($validated['producto_id']);

            return response()->json([
                'success' => true,
                'data' => new InventarioResource($inventario->load(['sucursal', 'producto'])),
                'message' => 'Registro de inventario creado exitosamente'
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
                'message' => 'Error al crear el registro de inventario',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Display the specified inventory record.
     */
    public function show(Inventario $inventario)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => new InventarioResource($inventario->load(['sucursal', 'producto'])),
                'message' => 'Registro de inventario obtenido exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el registro de inventario',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Update the specified inventory record.
     */
    public function update(Request $request, Inventario $inventario)
    {
        try {
            $validated = $request->validate([
                'cantidad' => 'sometimes|integer|min:0',
                'minimo_stock' => 'sometimes|integer|min:0',
                'ubicacion' => 'nullable|string|max:100'
            ]);

            $inventario->update($validated);

            if ($request->has('cantidad')) {
                $this->actualizarStockProducto($inventario->producto_id);
            }

            return response()->json([
                'success' => true,
                'data' => new InventarioResource($inventario->load(['sucursal', 'producto'])),
                'message' => 'Registro de inventario actualizado exitosamente'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el registro de inventario',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Remove the specified inventory record.
     */
    public function destroy(Inventario $inventario)
    {
        try {
            $productoId = $inventario->producto_id;
            $inventario->delete();
            $this->actualizarStockProducto($productoId);

            return response()->json([
                'success' => true,
                'message' => 'Registro de inventario eliminado exitosamente'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el registro de inventario',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Transfer inventory between branches.
     */
    public function transferir(Request $request)
    {
        try {
            $validated = $request->validate([
                'producto_id' => 'required|exists:productos,id',
                'sucursal_origen_id' => 'required|exists:sucursals,id',
                'sucursal_destino_id' => 'required|exists:sucursals,id|different:sucursal_origen_id',
                'cantidad' => 'required|integer|min:1',
                'motivo' => 'nullable|string|max:255'
            ]);

            $result = DB::transaction(function () use ($validated) {
                $inventarioOrigen = Inventario::where('sucursal_id', $validated['sucursal_origen_id'])
                    ->where('producto_id', $validated['producto_id'])
                    ->firstOrFail();

                if ($inventarioOrigen->cantidad < $validated['cantidad']) {
                    throw new \Exception('No hay suficiente stock en la sucursal de origen');
                }

                $inventarioOrigen->decrement('cantidad', $validated['cantidad']);

                $inventarioDestino = Inventario::firstOrNew([
                    'sucursal_id' => $validated['sucursal_destino_id'],
                    'producto_id' => $validated['producto_id']
                ]);

                if (!$inventarioDestino->exists) {
                    $inventarioDestino->cantidad = 0;
                    $inventarioDestino->minimo_stock = $inventarioOrigen->minimo_stock;
                    $inventarioDestino->ubicacion = 'N/A';
                }

                $inventarioDestino->increment('cantidad', $validated['cantidad']);
                $inventarioDestino->save();

                $this->actualizarStockProducto($validated['producto_id']);

                DB::table('transferencias_inventario')->insert([
                    'producto_id' => $validated['producto_id'],
                    'sucursal_origen_id' => $validated['sucursal_origen_id'],
                    'sucursal_destino_id' => $validated['sucursal_destino_id'],
                    'cantidad' => $validated['cantidad'],
                    'motivo' => $validated['motivo'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return [
                    'origen' => new InventarioResource($inventarioOrigen),
                    'destino' => new InventarioResource($inventarioDestino)
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Transferencia completada exitosamente'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al transferir inventario: ' . $e->getMessage(),
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Update the general product stock based on inventory records.
     */
    protected function actualizarStockProducto($productoId)
    {
        $totalStock = Inventario::where('producto_id', $productoId)
            ->sum('cantidad');

        Producto::where('id', $productoId)
            ->update(['stock' => $totalStock]);
    }

    /**
     * Get inventory alerts (low stock).
     */
    public function alertas(Request $request)
    {
        try {
            $alertas = Inventario::with(['sucursal', 'producto'])
                ->whereRaw('cantidad <= minimo_stock')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $alertas,
                'message' => 'Alertas de inventario obtenidas exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener alertas de inventario',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }
}