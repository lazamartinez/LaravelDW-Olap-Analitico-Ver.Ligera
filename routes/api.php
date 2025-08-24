<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ETLController;
use App\Http\Controllers\OLAPDashboardController;

// Ruta pública de test
Route::get('/status', fn() => response()->json(['status' => 'ok']));

// Rutas protegidas con sesión
Route::middleware('auth')->group(function () {
    // Sucursales
    Route::get('sucursales/{sucursal}/metrics', [SucursalController::class, 'apiMetrics']);
    Route::get('sucursales/{sucursal}/realtime', [SucursalController::class, 'apiRealtimeTransactions']);
    Route::apiResource('sucursales', SucursalController::class)
        ->except(['create','edit'])
        ->names('api.sucursales'); // 👈 prefijo

    // Productos
    Route::get('productos/categories', [ProductoController::class, 'categories']);
    Route::get('productos/{producto}/metrics', [ProductoController::class, 'metrics']);
    Route::apiResource('productos', ProductoController::class)
        ->except(['create','edit'])
        ->names('api.productos');

    // Inventario
    Route::get('inventario/alertas', [InventarioController::class, 'alertas']);
    Route::post('inventario/transferir', [InventarioController::class, 'transferir']);
    Route::apiResource('inventario', InventarioController::class)
        ->except(['create','edit'])
        ->names('api.inventario');

    // Transacciones
    Route::get('transacciones/realtime', [TransactionController::class, 'apiRealtimeFeed']);
    Route::put('transacciones/{transaction}/status', [TransactionController::class, 'apiUpdateStatus']);
    Route::apiResource('transacciones', TransactionController::class)
        ->except(['create','edit'])
        ->names('api.transacciones');

    // Ventas
    Route::get('ventas/metrics', [VentaController::class, 'apiMetrics']);
    Route::apiResource('ventas', VentaController::class)
        ->except(['create','edit'])
        ->names('api.ventas');

    // ETL
    Route::get('etl/procesar', [ETLController::class, 'procesarETL']);
    Route::post('etl/dimension-tiempo', [ETLController::class, 'cargarDimensionTiempo']);
    Route::get('etl/estado', [ETLController::class, 'estadoETL']);

    // OLAP
    Route::get('olap/cube', [OLAPDashboardController::class, 'getCubeData']);
});
