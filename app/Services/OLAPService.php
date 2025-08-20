<?php

namespace App\Services;

use App\Models\HechoVenta;
use App\Models\Sucursal;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonPeriod;

class OLAPService
{
    public function get3DCubeData(array $dimensions, array $measures, array $filters = [])
    {
        $query = HechoVenta::query();

        // Aplicar filtros
        foreach ($filters as $field => $value) {
            $query->where($field, $value);
        }

        // Construir consulta para cubo 3D
        $selects = [];
        $groups = [];

        foreach ($dimensions as $dimension) {
            switch ($dimension) {
                case 'sucursal':
                    $query->with('sucursal');
                    $selects[] = 'sucursal_id';
                    $groups[] = 'sucursal_id';
                    break;

                case 'producto':
                    $query->with('producto');
                    $selects[] = 'producto_id';
                    $groups[] = 'producto_id';
                    break;

                case 'tiempo':
                    $query->with('tiempo');
                    $selects[] = 'tiempo_id';
                    $groups[] = 'tiempo_id';
                    break;

                case 'geografia':
                    $query->join('sucursals', 'hecho_ventas.sucursal_id', '=', 'sucursals.id')
                        ->addSelect([
                            'sucursals.latitud as lat',
                            'sucursals.longitud as lng'
                        ]);
                    $groups[] = 'lat';
                    $groups[] = 'lng';
                    break;
            }
        }

        foreach ($measures as $measure) {
            switch ($measure) {
                case 'ventas':
                    $selects[] = DB::raw('SUM(monto_total) as ventas');
                    break;
                case 'cantidad':
                    $selects[] = DB::raw('SUM(cantidad) as cantidad');
                    break;
                case 'ganancia':
                    $selects[] = DB::raw('SUM(ganancia) as ganancia');
                    break;
                case 'margen':
                    $selects[] = DB::raw('(SUM(ganancia) / SUM(monto_total)) * 100 as margen');
                    break;
            }
        }

        return $query->select($selects)
            ->groupBy($groups)
            ->get();
    }

    public function spatialAnalysis($lat, $lng, $radius, $metric)
    {
        // Fórmula Haversine para cálculo de distancia
        $haversine = "(6371 * acos(cos(radians($lat)) 
                      * cos(radians(sucursals.latitud)) 
                      * cos(radians(sucursals.longitud) 
                      - radians($lng)) 
                      + sin(radians($lat)) 
                      * sin(radians(sucursals.latitud))))";

        return HechoVenta::join('sucursals', 'hecho_ventas.sucursal_id', '=', 'sucursals.id')
            ->select([
                'sucursals.id',
                'sucursals.nombre',
                'sucursals.latitud as lat',
                'sucursals.longitud as lng',
                DB::raw("$haversine as distancia"),
                DB::raw("SUM($metric) as valor")
            ])
            ->whereRaw("$haversine < ?", [$radius])
            ->groupBy('sucursals.id', 'sucursals.nombre', 'sucursals.latitud', 'sucursals.longitud')
            ->orderBy('distancia')
            ->get();
    }

    public function timeSeriesAnalysis($startDate, $endDate, $granularity, $sucursalId = null, $productoId = null)
    {
        $query = HechoVenta::join('dimension_tiempo', 'hecho_ventas.tiempo_id', '=', 'dimension_tiempo.id');

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        if ($productoId) {
            $query->where('producto_id', $productoId);
        }

        $dateFormat = match ($granularity) {
            'hourly' => 'YYYY-MM-DD HH24:00',
            'daily' => 'YYYY-MM-DD',
            'weekly' => 'IYYY-IW',
            'monthly' => 'YYYY-MM',
            default => 'YYYY-MM-DD'
        };

        return $query
            ->select([
                DB::raw("to_char(dimension_tiempo.fecha, '$dateFormat') as periodo"),
                DB::raw('SUM(monto_total) as ventas'),
                DB::raw('SUM(ganancia) as ganancia'),
                DB::raw('SUM(cantidad) as cantidad')
            ])
            ->whereBetween('dimension_tiempo.fecha', [$startDate, $endDate])
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();
    }
    public function drillDown3D($dimension, $breakdown, $coordinates, $filters = [])
    {
        $query = HechoVenta::query();

        // Aplicar filtros
        foreach ($filters as $field => $value) {
            $query->where($field, $value);
        }

        // Lógica específica para drill down 3D
        switch ($dimension) {
            case 'tiempo':
                return $this->drillDownTiempo($query, $breakdown, $coordinates);
            case 'sucursal':
                return $this->drillDownSucursal($query, $breakdown, $coordinates);
            case 'producto':
                return $this->drillDownProducto($query, $breakdown, $coordinates);
            default:
                return collect();
        }
    }

    protected function drillDownTiempo($query, $breakdown, $coordinates)
    {
        $query->join('dimension_tiempo', 'hecho_ventas.tiempo_id', '=', 'dimension_tiempo.id')
            ->select(
                DB::raw("DATE_TRUNC('{$breakdown}', dimension_tiempo.fecha) as periodo"),
                DB::raw('SUM(monto_total) as ventas'),
                DB::raw('SUM(ganancia) as ganancia'),
                DB::raw('SUM(cantidad) as cantidad')
            )
            ->whereBetween('dimension_tiempo.fecha', [
                $coordinates['start_date'],
                $coordinates['end_date']
            ])
            ->groupBy('periodo')
            ->orderBy('periodo');

        return $query->get();
    }

    protected function drillDownSucursal($query, $breakdown, $coordinates)
    {
        $query->with('sucursal')
            ->select(
                'sucursal_id',
                DB::raw('SUM(monto_total) as ventas'),
                DB::raw('SUM(ganancia) as ganancia'),
                DB::raw('SUM(cantidad) as cantidad')
            )
            ->whereIn('sucursal_id', $coordinates['sucursal_ids'])
            ->groupBy('sucursal_id');

        return $query->get();
    }

    protected function drillDownProducto($query, $breakdown, $coordinates)
    {
        $query->with('producto')
            ->select(
                'producto_id',
                DB::raw('SUM(monto_total) as ventas'),
                DB::raw('SUM(ganancia) as ganancia'),
                DB::raw('SUM(cantidad) as cantidad')
            )
            ->whereIn('producto_id', $coordinates['producto_ids'])
            ->groupBy('producto_id');

        return $query->get();
    }

    protected function getColorForValue($value)
    {
        // Lógica para asignar colores basados en valores
        if ($value < 1000) return '#ff6b6b';
        if ($value < 5000) return '#ffe066';
        return '#51cf66';
    }
}
