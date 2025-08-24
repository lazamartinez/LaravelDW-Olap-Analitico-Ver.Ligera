<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\HechoVenta;
use App\Models\DimensionTiempo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ETLController extends Controller
{
    public function procesarETL()
    {
        try {
            DB::transaction(function () {
                // Paso 1: Extraer datos de ventas no procesadas
                $ventas = Venta::where('procesada_olap', false)->get();
                
                // Paso 2: Transformar y cargar en el data warehouse
                foreach ($ventas as $venta) {
                    $this->procesarVenta($venta);
                    
                    // Marcar como procesada
                    $venta->update(['procesada_olap' => true]);
                }
            });
            
            return response()->json([
                'success' => true,
                'message' => 'ETL completado exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar ETL',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }
    
    protected function procesarVenta($venta)
    {
        // Obtener o crear dimensión de tiempo
        $fechaVenta = Carbon::parse($venta->fecha_venta);
        $tiempo = DimensionTiempo::firstOrCreate(
            ['fecha' => $fechaVenta->toDateString()],
            [
                'dia' => $fechaVenta->day,
                'mes' => $fechaVenta->month,
                'anio' => $fechaVenta->year,
                'trimestre' => ceil($fechaVenta->month / 3),
                'semana' => $fechaVenta->weekOfYear,
                'dia_semana' => $fechaVenta->dayName,
                'es_fin_de_semana' => $fechaVenta->isWeekend(),
                'es_feriado' => false
            ]
        );
        
        // Procesar cada producto de la venta
        foreach ($venta->productos as $producto) {
            HechoVenta::create([
                'sucursal_id' => $venta->sucursal_id,
                'producto_id' => $producto->id,
                'tiempo_id' => $tiempo->id,
                'cantidad' => $producto->pivot->cantidad,
                'monto_total' => $producto->pivot->cantidad * $producto->pivot->precio_unitario,
                'costo_total' => $producto->pivot->cantidad * $producto->costo,
                'ganancia' => ($producto->pivot->cantidad * $producto->pivot->precio_unitario) - 
                              ($producto->pivot->cantidad * $producto->costo)
            ]);
        }
    }
    
    public function cargarDimensionTiempo(Request $request)
    {
        try {
            $inicio = Carbon::parse($request->fecha_inicio);
            $fin = Carbon::parse($request->fecha_fin);
            
            for ($fecha = $inicio; $fecha->lte($fin); $fecha->addDay()) {
                DimensionTiempo::firstOrCreate(
                    ['fecha' => $fecha->toDateString()],
                    [
                        'dia' => $fecha->day,
                        'mes' => $fecha->month,
                        'anio' => $fecha->year,
                        'trimestre' => ceil($fecha->month / 3),
                        'semana' => $fecha->weekOfYear,
                        'dia_semana' => $fecha->dayName,
                        'es_fin_de_semana' => $fecha->isWeekend(),
                        'es_feriado' => false
                    ]
                );
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Dimensión tiempo actualizada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar dimensión tiempo',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    // API: Obtener estado del ETL
    public function estadoETL()
    {
        try {
            $ventasPendientes = Venta::where('procesada_olap', false)->count();
            $totalVentas = Venta::count();
            $totalHechoVentas = HechoVenta::count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'ventas_pendientes' => $ventasPendientes,
                    'total_ventas' => $totalVentas,
                    'total_hecho_ventas' => $totalHechoVentas,
                    'porcentaje_procesado' => $totalVentas > 0 ? (($totalVentas - $ventasPendientes) / $totalVentas) * 100 : 0
                ],
                'message' => 'Estado del ETL obtenido exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estado del ETL',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }
}