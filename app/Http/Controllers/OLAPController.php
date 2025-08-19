<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HechoVenta;
use App\Models\Sucursal;
use App\Models\DimensionTiempo;
use App\Http\Resources\OLAPResource;
use Illuminate\Support\Facades\DB;

class OLAPController extends Controller
{
    public function cuboVentas(Request $request)
    {
        $query = HechoVenta::query()
            ->with(['sucursal', 'producto', 'tiempo']);
            
        // Filtros
        if ($request->has('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }
        
        if ($request->has('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }
        
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereHas('tiempo', function($q) use ($request) {
                $q->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);
            });
        }
        
        // Agregaciones
        $resultados = $query->select([
            'sucursal_id',
            'producto_id',
            'tiempo_id',
            DB::raw('SUM(cantidad) as total_cantidad'),
            DB::raw('SUM(monto_total) as total_ventas'),
            DB::raw('SUM(costo_total) as total_costos'),
            DB::raw('SUM(ganancia) as total_ganancia')
        ])
        ->groupBy('sucursal_id', 'producto_id', 'tiempo_id')
        ->get();
        
        return OLAPResource::collection($resultados);
    }
    
    public function drillDown(Request $request)
    {
        $dimension = $request->dimension; // sucursal, producto o tiempo
        $nivel = $request->nivel; // año, mes, dia, etc.
        
        $query = HechoVenta::query();
        
        switch ($dimension) {
            case 'sucursal':
                $query->with('sucursal')
                    ->select([
                        'sucursal_id',
                        DB::raw('SUM(monto_total) as total_ventas')
                    ])
                    ->groupBy('sucursal_id');
                break;
                
            case 'producto':
                $query->with('producto')
                    ->select([
                        'producto_id',
                        DB::raw('SUM(monto_total) as total_ventas')
                    ])
                    ->groupBy('producto_id');
                break;
                
            case 'tiempo':
                $query->join('dimension_tiempo', 'hecho_ventas.tiempo_id', '=', 'dimension_tiempo.id')
                    ->select([
                        DB::raw("DATE_TRUNC('{$nivel}', dimension_tiempo.fecha) as periodo"),
                        DB::raw('SUM(monto_total) as total_ventas')
                    ])
                    ->groupBy('periodo')
                    ->orderBy('periodo');
                break;
        }
        
        return response()->json($query->get());
    }
    
    public function sliceDice(Request $request)
    {
        $query = HechoVenta::query()
            ->with(['sucursal', 'producto', 'tiempo']);
            
        // Slice (corte en una dimensión)
        if ($request->has('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }
        
        if ($request->has('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }
        
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereHas('tiempo', function($q) use ($request) {
                $q->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);
            });
        }
        
        // Dice (corte en múltiples dimensiones)
        if ($request->has('categorias')) {
            $query->whereHas('producto', function($q) use ($request) {
                $q->whereIn('categoria', $request->categorias);
            });
        }
        
        if ($request->has('metodos_pago')) {
            $query->whereHas('venta', function($q) use ($request) {
                $q->whereIn('metodo_pago', $request->metodos_pago);
            });
        }
        
        $resultados = $query->get();
        
        return OLAPResource::collection($resultados);
    }
    
    public function metricasTiempoReal()
    {
        // Usando Redis para métricas en tiempo real
        $ventasHoy = HechoVenta::whereHas('tiempo', function($q) {
            $q->where('fecha', today());
        })->sum('monto_total');
        
        $ventasMes = HechoVenta::whereHas('tiempo', function($q) {
            $q->whereMonth('fecha', now()->month)
              ->whereYear('fecha', now()->year);
        })->sum('monto_total');
        
        $productosPopulares = HechoVenta::with('producto')
            ->select('producto_id', DB::raw('SUM(cantidad) as total_vendido'))
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();
            
        $sucursalesTop = HechoVenta::with('sucursal')
            ->select('sucursal_id', DB::raw('SUM(monto_total) as total_ventas'))
            ->groupBy('sucursal_id')
            ->orderByDesc('total_ventas')
            ->limit(3)
            ->get();
            
        return response()->json([
            'ventas_hoy' => $ventasHoy,
            'ventas_mes' => $ventasMes,
            'productos_populares' => $productosPopulares,
            'sucursales_top' => $sucursalesTop,
            'ultima_actualizacion' => now()->toDateTimeString()
        ]);
    }
}