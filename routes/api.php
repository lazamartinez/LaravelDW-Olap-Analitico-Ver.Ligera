<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\OLAPController;
use App\Http\Controllers\ETLController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rutas públicas (autenticación)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    // Dashboard
    Route::get('/dashboard-metrics', [DashboardController::class, 'metrics']);
    
    // Sucursales
    Route::get('/sucursales', [SucursalController::class, 'index']);
    Route::post('/sucursales', [SucursalController::class, 'store']);
    Route::get('/sucursales/{id}', [SucursalController::class, 'show']);
    Route::put('/sucursales/{id}', [SucursalController::class, 'update']);
    Route::delete('/sucursales/{id}', [SucursalController::class, 'destroy']);
    Route::get('/sucursales/{id}/metrics', [SucursalController::class, 'metrics']);
    
    // Productos
    Route::get('/productos', [ProductoController::class, 'index']);
    Route::post('/productos', [ProductoController::class, 'store']);
    Route::get('/productos/{id}', [ProductoController::class, 'show']);
    Route::put('/productos/{id}', [ProductoController::class, 'update']);
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);
    Route::get('/productos/{id}/metrics', [ProductoController::class, 'metrics']);
    
    // Inventario
    Route::get('/inventario', [InventarioController::class, 'index']);
    Route::post('/inventario', [InventarioController::class, 'store']);
    Route::get('/inventario/{id}', [InventarioController::class, 'show']);
    Route::put('/inventario/{id}', [InventarioController::class, 'update']);
    Route::delete('/inventario/{id}', [InventarioController::class, 'destroy']);
    Route::post('/inventario/transferir', [InventarioController::class, 'transferir']);
    
    // Transacciones
    Route::get('/transacciones', [TransactionController::class, 'index']);
    Route::post('/transacciones', [TransactionController::class, 'store']);
    Route::get('/transacciones/{id}', [TransactionController::class, 'show']);
    Route::put('/transacciones/{id}/estado', [TransactionController::class, 'updateStatus']);
    Route::get('/transacciones/realtime', [TransactionController::class, 'realtimeFeed']);
    
    // Ventas
    Route::get('/ventas', [VentaController::class, 'index']);
    Route::post('/ventas', [VentaController::class, 'store']);
    Route::get('/ventas/{id}', [VentaController::class, 'show']);
    
    // OLAP
    Route::post('/olap/cube', [OLAPController::class, 'cube3D']);
    Route::post('/olap/drilldown', [OLAPController::class, 'drillDown3D']);
    Route::get('/olap/timeseries', [OLAPController::class, 'timeSeriesAnalysis']);
    Route::get('/olap/spatial', [OLAPController::class, 'spatialAnalysis']);
    
    // ETL
    Route::post('/etl/process', [ETLController::class, 'procesarETL']);
    Route::post('/etl/dimension-tiempo', [ETLController::class, 'cargarDimensionTiempo']);
    
    // Usuario actual
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});