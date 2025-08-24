<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductoResource;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource for web.
     */
    public function index()
    {
        try {
            $productos = Producto::with('sucursal')->get();

            return response()->json([
                'success' => true,
                'data' => $productos,
                'message' => 'Productos obtenidos exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los productos',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }


    /**
     * Display a listing of the resource for API.
     */
    public function apiIndex(Request $request)
    {
        try {
            // Obtener parámetros de filtrado
            $query = Producto::with('sucursal');

            // Filtrar por categoría si se especifica
            if ($request->has('categoria') && $request->categoria) {
                $query->where('categoria', $request->categoria);
            }

            // Filtrar por sucursal si se especifica
            if ($request->has('sucursal_id') && $request->sucursal_id) {
                $query->where('sucursal_id', $request->sucursal_id);
            }

            // Filtrar por búsqueda de texto
            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('nombre', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('codigo', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('descripcion', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Ordenar
            $sortField = $request->get('sort_field', 'nombre');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortField, $sortDirection);

            // Paginar o devolver todos los resultados
            if ($request->has('per_page')) {
                $productos = $query->paginate($request->per_page);
            } else {
                $productos = $query->get();
            }

            return response()->json([
                'success' => true,
                'data' => $productos,
                'message' => 'Productos obtenidos exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los productos',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'sucursal_id' => 'required|exists:sucursals,id',
                'codigo' => 'required|unique:productos',
                'nombre' => 'required|max:255',
                'descripcion' => 'nullable',
                'precio' => 'required|numeric|min:0',
                'costo' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'categoria' => 'required|max:255',
                'marca' => 'nullable|max:255',
                'atributos' => 'nullable|json'
            ]);

            $producto = Producto::create($validated);

            return response()->json([
                'success' => true,
                'data' => new ProductoResource($producto),
                'message' => 'Producto creado exitosamente'
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
                'message' => 'Error al crear el producto',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        try {
            $producto->load('sucursal', 'ventas', 'inventarios');

            return response()->json([
                'success' => true,
                'data' => new ProductoResource($producto),
                'message' => 'Producto obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el producto',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Producto $producto)
    {
        try {
            $validated = $request->validate([
                'sucursal_id' => 'sometimes|exists:sucursals,id',
                'codigo' => 'sometimes|unique:productos,codigo,' . $producto->id,
                'nombre' => 'sometimes|max:255',
                'descripcion' => 'nullable',
                'precio' => 'sometimes|numeric|min:0',
                'costo' => 'sometimes|numeric|min:0',
                'stock' => 'sometimes|integer|min:0',
                'categoria' => 'sometimes|max:255',
                'marca' => 'nullable|max:255',
                'atributos' => 'nullable|json'
            ]);

            $producto->update($validated);

            return response()->json([
                'success' => true,
                'data' => new ProductoResource($producto),
                'message' => 'Producto actualizado exitosamente'
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
                'message' => 'Error al actualizar el producto',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        try {
            $producto->delete();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el producto',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get metrics for a specific product.
     */
    public function metrics(Producto $producto)
    {
        try {
            $ventas = DB::table('hecho_ventas')
                ->select(
                    DB::raw('SUM(cantidad) as total_vendido'),
                    DB::raw('SUM(monto_total) as total_ventas'),
                    DB::raw('SUM(ganancia) as total_ganancia')
                )
                ->where('producto_id', $producto->id)
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'producto' => new ProductoResource($producto),
                    'metrics' => $ventas
                ],
                'message' => 'Métricas del producto obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las métricas del producto',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get product categories.
     */
    public function categories()
    {
        try {
            $categorias = Producto::distinct()
                ->whereNotNull('categoria')
                ->orderBy('categoria')
                ->pluck('categoria');

            return response()->json([
                'success' => true,
                'data' => $categorias,
                'message' => 'Categorías obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las categorías',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }
}
