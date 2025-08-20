<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Services\SucursalService;
use Illuminate\Http\Request;
use App\Http\Resources\SucursalResource;
use App\Events\SucursalUpdated;
use Illuminate\Support\Facades\DB;
use Str;

class SucursalController extends Controller
{
    protected $sucursalService;

    public function __construct(SucursalService $sucursalService)
    {
        $this->sucursalService = $sucursalService;
    }

    public function index(Request $request)
    {
        $query = Sucursal::withCount(['ventas', 'productos', 'inventarios']);
        
        if ($request->has('activa')) {
            $query->where('activa', $request->boolean('activa'));
        }

        $sucursales = $query->paginate(15);
        return SucursalResource::collection($sucursales);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        
        return DB::transaction(function () use ($validated) {
            $sucursal = Sucursal::create($validated);
            
            // Configuración inicial del contenedor Docker
            $dockerConfig = $this->sucursalService->setupDockerContainer($sucursal);
            
            $sucursal->update([
                'docker_config' => $dockerConfig,
                'api_secret' => \Illuminate\Support\Str::random(64) // Usar Str correctamente
            ]);
            
            event(new SucursalUpdated($sucursal->id, [
                'action' => 'created',
                'data' => $sucursal
            ]));
            
            return new SucursalResource($sucursal);
        });
    }


    public function show(Sucursal $sucursal)
    {
        $sucursal->load([
            'productos' => fn($q) => $q->withSum('hechoVentas as ventas_total', 'monto_total'),
            'ventas' => fn($q) => $q->latest()->limit(5)
        ]);
        
        return new SucursalResource($sucursal);
    }

    public function update(Request $request, Sucursal $sucursal)
    {
        $validated = $this->validateRequest($request, $sucursal);
        
        $sucursal->update($validated);
        
        event(new SucursalUpdated($sucursal->id, [
            'action' => 'updated',
            'data' => $sucursal
        ]));
        
        return new SucursalResource($sucursal);
    }

    public function destroy(Sucursal $sucursal)
    {
        $this->sucursalService->removeDockerContainer($sucursal);
        
        $sucursal->delete();
        
        event(new SucursalUpdated($sucursal->id, [
            'action' => 'deleted'
        ]));
        
        return response()->noContent();
    }

    public function metrics(Sucursal $sucursal)
    {
        $metrics = $this->sucursalService->calculateMetrics($sucursal);
        return response()->json($metrics);
    }

    public function realtimeTransactions(Sucursal $sucursal)
    {
        $transactions = $this->sucursalService->getRealtimeTransactions($sucursal);
        return response()->json($transactions);
    }

    protected function validateRequest(Request $request, $sucursal = null)
    {
        return $request->validate([
            'nombre' => 'required|string|max:255|unique:sucursals,nombre,'.($sucursal?->id ?: 'NULL'),
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