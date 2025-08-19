<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\OLAPController;
use App\Http\Controllers\ETLController;
use App\Http\Controllers\InventarioController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas CRUD básicas
Route::apiResource('sucursales', SucursalController::class);
Route::apiResource('productos', ProductoController::class);
Route::apiResource('ventas', VentaController::class);

// Rutas OLAP
Route::prefix('olap')->group(function () {
    Route::get('/cubo-ventas', [OLAPController::class, 'cuboVentas']);
    Route::get('/drill-down', [OLAPController::class, 'drillDown']);
    Route::get('/slice-dice', [OLAPController::class, 'sliceDice']);
    Route::get('/metricas-tiempo-real', [OLAPController::class, 'metricasTiempoReal']);
});

// Rutas ETL
Route::prefix('etl')->group(function () {
    Route::post('/procesar', [ETLController::class, 'procesarETL']);
    Route::post('/cargar-tiempo', [ETLController::class, 'cargarDimensionTiempo']);
});

// Rutas para gestión de sucursales en Docker
Route::prefix('sucursales')->group(function () {
    Route::post('/{sucursal}/docker/start', [SucursalController::class, 'startDockerContainer']);
    Route::post('/{sucursal}/docker/stop', [SucursalController::class, 'stopDockerContainer']);
    Route::get('/{sucursal}/docker/status', [SucursalController::class, 'dockerContainerStatus']);
});

// Rutas para métricas
Route::get('/productos/{producto}/metrics', [ProductoController::class, 'metrics']);
Route::get('/sucursales/{sucursal}/metrics', [SucursalController::class, 'metrics']);

// Rutas para inventario
Route::apiResource('inventarios', InventarioController::class)->except(['update']);
Route::post('/inventarios/transferir', [InventarioController::class, 'transferir']);
Route::patch('/inventarios/{inventario}', [InventarioController::class, 'update']); // Ruta específica para PATCH

Route::get('/sucursales', function() {
    return App\Models\Sucursal::withCount(['ventas as ventas_hoy' => function($query) {
        $query->whereDate('fecha_venta', today());
    }])->get();
});

Route::get('/transacciones-recientes', function() {
    return App\Models\Venta::with('sucursal')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get()
        ->map(function($venta) {
            return [
                'tipo' => 'venta',
                'sucursal' => $venta->sucursal->nombre,
                'descripcion' => 'Venta de ' . $venta->productos->count() . ' productos',
                'monto' => $venta->total,
                'fecha' => $venta->created_at->diffForHumans()
            ];
        });
});