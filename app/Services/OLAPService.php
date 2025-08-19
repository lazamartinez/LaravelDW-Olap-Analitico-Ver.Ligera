<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\HechoVenta;
use App\Models\DimensionTiempo;


class OLAPService
{
    public function executeCubeQuery($dimensions, $measures)
    {
        $query = HechoVenta::query();
        
        // Seleccionar dimensiones
        foreach ($dimensions as $dimension) {
            switch ($dimension) {
                case 'sucursal':
                    $query->with('sucursal')
                         ->addSelect('sucursal_id');
                    break;
                case 'producto':
                    $query->with('producto')
                         ->addSelect('producto_id');
                    break;
                case 'tiempo':
                    $query->with('tiempo')
                         ->addSelect('tiempo_id');
                    break;
            }
        }
        
        // Agregar medidas
        foreach ($measures as $measure) {
            switch ($measure) {
                case 'ventas':
                    $query->addSelect(DB::raw('SUM(monto_total) as total_ventas'));
                    break;
                case 'cantidad':
                    $query->addSelect(DB::raw('SUM(cantidad) as total_cantidad'));
                    break;
                case 'ganancia':
                    $query->addSelect(DB::raw('SUM(ganancia) as total_ganancia'));
                    break;
            }
        }
        
        // Agrupar por dimensiones
        $query->groupBy($dimensions);
        
        return $query->get();
    }
    
    public function drillDown($dimension, $breakdown, $filters = [])
    {
        $query = HechoVenta::query();
        
        // Aplicar filtros
        foreach ($filters as $field => $value) {
            $query->where($field, $value);
        }
        
        switch ($dimension) {
            case 'tiempo':
                $query->join('dimension_tiempo', 'hecho_ventas.tiempo_id', '=', 'dimension_tiempo.id')
                    ->select(
                        DB::raw("DATE_TRUNC('{$breakdown}', dimension_tiempo.fecha) as periodo"),
                        DB::raw('SUM(monto_total) as total_ventas'),
                        DB::raw('SUM(ganancia) as total_ganancia')
                    )
                    ->groupBy('periodo')
                    ->orderBy('periodo');
                break;
                
            case 'sucursal':
                $query->with('sucursal')
                    ->select(
                        'sucursal_id',
                        DB::raw('SUM(monto_total) as total_ventas'),
                        DB::raw('SUM(ganancia) as total_ganancia')
                    )
                    ->groupBy('sucursal_id');
                break;
                
            case 'producto':
                $query->with('producto')
                    ->select(
                        'producto_id',
                        DB::raw('SUM(monto_total) as total_ventas'),
                        DB::raw('SUM(ganancia) as total_ganancia')
                    )
                    ->groupBy('producto_id');
                break;
        }
        
        return $query->get();
    }
    
    public function getTimeDimensions()
    {
        return [
            'year' => 'Año',
            'quarter' => 'Trimestre',
            'month' => 'Mes',
            'week' => 'Semana',
            'day' => 'Día'
        ];
    }
}