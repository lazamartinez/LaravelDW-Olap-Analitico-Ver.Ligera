<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Services\SucursalService;
use Illuminate\Http\Request;
use App\Http\Resources\SucursalResource;
use App\Events\SucursalUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SucursalController extends Controller
{
    protected $sucursalService;

    public function __construct(SucursalService $sucursalService)
    {
        $this->sucursalService = $sucursalService;
    }

    /**
     * Display a listing of the resource for web.
     */
    public function index()
    {
        try {
            $sucursales = Sucursal::withCount(['ventas', 'productos', 'inventarios'])->get();

            return response()->json([
                'success' => true,
                'data' => $sucursales,
                'message' => 'Sucursales obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las sucursales',
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
            $query = Sucursal::withCount(['ventas', 'productos', 'inventarios']);

            if ($request->has('activa')) {
                $query->where('activa', $request->boolean('activa'));
            }

            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('nombre', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('ciudad', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('direccion', 'LIKE', "%{$searchTerm}%");
                });
            }

            $sucursales = $query->get();

            return response()->json([
                'success' => true,
                'data' => $sucursales,
                'message' => 'Sucursales obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las sucursales',
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
            $validated = $this->validateRequest($request);

            $sucursal = DB::transaction(function () use ($validated) {
                $sucursal = Sucursal::create($validated);

                // Configuración inicial del contenedor Docker
                $dockerConfig = $this->sucursalService->setupDockerContainer($sucursal);

                $sucursal->update([
                    'docker_config' => $dockerConfig,
                    'api_secret' => Str::random(64)
                ]);

                event(new SucursalUpdated($sucursal->id, [
                    'action' => 'created',
                    'data' => $sucursal
                ]));

                return $sucursal;
            });

            return response()->json([
                'success' => true,
                'data' => new SucursalResource($sucursal),
                'message' => 'Sucursal creada exitosamente'
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
                'message' => 'Error al crear la sucursal',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Display the specified resource for API.
     */
    public function apiShow(Sucursal $sucursal)
    {
        try {
            $sucursal->load([
                'productos' => fn($q) => $q->withSum('hechoVentas as ventas_total', 'monto_total'),
                'ventas' => fn($q) => $q->latest()->limit(5)
            ]);

            return response()->json([
                'success' => true,
                'data' => new SucursalResource($sucursal),
                'message' => 'Sucursal obtenida exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la sucursal',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage for API.
     */
    public function apiUpdate(Request $request, Sucursal $sucursal)
    {
        try {
            $validated = $this->validateRequest($request, $sucursal);

            $sucursal->update($validated);

            event(new SucursalUpdated($sucursal->id, [
                'action' => 'updated',
                'data' => $sucursal
            ]));

            return response()->json([
                'success' => true,
                'data' => new SucursalResource($sucursal),
                'message' => 'Sucursal actualizada exitosamente'
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
                'message' => 'Error al actualizar la sucursal',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage for API.
     */
    public function apiDestroy(Sucursal $sucursal)
    {
        try {
            $this->sucursalService->removeDockerContainer($sucursal);
            $sucursal->delete();

            event(new SucursalUpdated($sucursal->id, [
                'action' => 'deleted'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Sucursal eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la sucursal',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get metrics for a specific branch for API.
     */
    public function apiMetrics(Sucursal $sucursal)
    {
        try {
            $metrics = $this->sucursalService->calculateMetrics($sucursal);

            return response()->json([
                'success' => true,
                'data' => $metrics,
                'message' => 'Métricas de la sucursal obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las métricas de la sucursal',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get realtime transactions for a specific branch for API.
     */
    public function apiRealtimeTransactions(Sucursal $sucursal)
    {
        try {
            $transactions = $this->sucursalService->getRealtimeTransactions($sucursal);

            return response()->json([
                'success' => true,
                'data' => $transactions,
                'message' => 'Transacciones en tiempo real obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las transacciones en tiempo real',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    protected function validateRequest(Request $request, $sucursal = null)
    {
        return $request->validate([
            'nombre' => 'required|string|max:255|unique:sucursals,nombre,' . ($sucursal?->id ?: 'NULL'),
            'direccion' => 'required|string',
            'ciudad' => 'required|string',
            'pais' => 'required|string',
            'codigo_postal' => 'nullable|string',
            'telefono' => 'nullable|string',
            'email' => 'nullable|email',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'configuracion' => 'nullable|array',
            'activa' => 'sometimes|boolean'
        ]);
    }
}
