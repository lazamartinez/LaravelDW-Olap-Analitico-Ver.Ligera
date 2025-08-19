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
     * Display a listing of the inventory.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Puedes filtrar por sucursal o producto con parámetros de consulta
        $query = Inventario::query()
            ->with(['sucursal', 'producto'])
            ->orderBy('sucursal_id')
            ->orderBy('producto_id');

        if (request()->has('sucursal_id')) {
            $query->where('sucursal_id', request('sucursal_id'));
        }

        if (request()->has('producto_id')) {
            $query->where('producto_id', request('producto_id'));
        }

        // Mostrar alertas de stock bajo
        if (request()->has('alertas')) {
            $query->whereRaw('cantidad <= minimo_stock');
        }

        $inventarios = $query->paginate(15);
        return InventarioResource::collection($inventarios);
    }

    /**
     * Store a newly created inventory record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sucursal_id' => 'required|exists:sucursals,id',
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:0',
            'minimo_stock' => 'required|integer|min:0',
            'ubicacion' => 'nullable|string|max:100'
        ]);

        // Verificar que no exista ya un registro para esta sucursal y producto
        $exists = Inventario::where('sucursal_id', $validated['sucursal_id'])
            ->where('producto_id', $validated['producto_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Ya existe un registro de inventario para este producto en la sucursal seleccionada'
            ], 409);
        }

        $inventario = Inventario::create($validated);

        // Actualizar el stock general del producto
        $this->actualizarStockProducto($validated['producto_id']);

        return new InventarioResource($inventario->load(['sucursal', 'producto']));
    }

    /**
     * Display the specified inventory record.
     *
     * @param  \App\Models\Inventario  $inventario
     * @return \Illuminate\Http\Response
     */
    public function show(Inventario $inventario)
    {
        return new InventarioResource($inventario->load(['sucursal', 'producto']));
    }

    /**
     * Update the specified inventory record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Inventario  $inventario
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Inventario $inventario)
    {
        $validated = $request->validate([
            'cantidad' => 'sometimes|integer|min:0',
            'minimo_stock' => 'sometimes|integer|min:0',
            'ubicacion' => 'nullable|string|max:100'
        ]);

        $inventario->update($validated);

        // Si se actualizó la cantidad, actualizar el stock general del producto
        if ($request->has('cantidad')) {
            $this->actualizarStockProducto($inventario->producto_id);
        }

        return new InventarioResource($inventario->load(['sucursal', 'producto']));
    }

    /**
     * Remove the specified inventory record.
     *
     * @param  \App\Models\Inventario  $inventario
     * @return \Illuminate\Http\Response
     */
    public function destroy(Inventario $inventario)
    {
        $productoId = $inventario->producto_id;
        $inventario->delete();

        // Actualizar el stock general del producto
        $this->actualizarStockProducto($productoId);

        return response()->json(null, 204);
    }

    /**
     * Transfer inventory between branches.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function transferir(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'sucursal_origen_id' => 'required|exists:sucursals,id',
            'sucursal_destino_id' => 'required|exists:sucursals,id|different:sucursal_origen_id',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'nullable|string|max:255'
        ]);

        return DB::transaction(function () use ($validated) {
            // Verificar stock en sucursal origen
            $inventarioOrigen = Inventario::where('sucursal_id', $validated['sucursal_origen_id'])
                ->where('producto_id', $validated['producto_id'])
                ->firstOrFail();

            if ($inventarioOrigen->cantidad < $validated['cantidad']) {
                return response()->json([
                    'message' => 'No hay suficiente stock en la sucursal de origen'
                ], 400);
            }

            // Reducir stock en origen
            $inventarioOrigen->decrement('cantidad', $validated['cantidad']);

            // Aumentar stock en destino (o crear registro si no existe)
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

            // Actualizar stock general del producto
            $this->actualizarStockProducto($validated['producto_id']);

            // Registrar la transferencia (podrías crear un modelo para esto)
            DB::table('transferencias_inventario')->insert([
                'producto_id' => $validated['producto_id'],
                'sucursal_origen_id' => $validated['sucursal_origen_id'],
                'sucursal_destino_id' => $validated['sucursal_destino_id'],
                'cantidad' => $validated['cantidad'],
                'motivo' => $validated['motivo'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'message' => 'Transferencia completada exitosamente',
                'origen' => new InventarioResource($inventarioOrigen),
                'destino' => new InventarioResource($inventarioDestino)
            ]);
        });
    }

    /**
     * Update the general product stock based on inventory records.
     *
     * @param  int  $productoId
     * @return void
     */
    protected function actualizarStockProducto($productoId)
    {
        $totalStock = Inventario::where('producto_id', $productoId)
            ->sum('cantidad');

        Producto::where('id', $productoId)
            ->update(['stock' => $totalStock]);
    }
}