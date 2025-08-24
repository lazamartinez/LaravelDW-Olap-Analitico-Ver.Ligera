<?php

namespace App\Services;

use App\Models\HechoVenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class OLAPService
{
    /**
     * Obtener cubo 3D real
     */
    public function get3DCubeData(array $dimensions, array $measures, array $filters = []): Collection
    {
        try {
            $query = HechoVenta::query();

            // Aplicar filtros válidos
            foreach ($filters as $field => $value) {
                if (in_array($field, ['sucursal_id','producto_id','tiempo_id'])) {
                    $query->where($field, $value);
                }
            }

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
                        $selects[] = DB::raw('(SUM(ganancia)/SUM(monto_total))*100 as margen');
                        break;
                }
            }

            return $query->select($selects)
                         ->groupBy($groups)
                         ->get();

        } catch (\Exception $e) {
            Log::error('OLAPService get3DCubeData error: '.$e->getMessage());
            return $this->get3DCubeDataTest(); // devolver datos de prueba en caso de error
        }
    }

    /**
     * Cubo de prueba para test
     */
    public function get3DCubeDataTest(): Collection
    {
        return collect([
            ['x'=>'2025-01','y'=>'Sucursal 1','z'=>100],
            ['x'=>'2025-01','y'=>'Sucursal 2','z'=>150],
            ['x'=>'2025-02','y'=>'Sucursal 1','z'=>120],
            ['x'=>'2025-02','y'=>'Sucursal 2','z'=>180],
        ]);
    }

    /**
     * Análisis espacial
     */
    public function spatialAnalysis($lat, $lng, $radius, $metric): Collection
    {
        try {
            $metric = in_array($metric, ['ventas','ganancia','cantidad']) ? $metric : 'ventas';
            $haversine = "(6371 * acos(cos(radians($lat)) 
                          * cos(radians(sucursals.latitud)) 
                          * cos(radians(sucursals.longitud) - radians($lng)) 
                          + sin(radians($lat)) * sin(radians(sucursals.latitud))))";

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

        } catch (\Exception $e) {
            Log::error('OLAPService spatialAnalysis error: '.$e->getMessage());
            return collect(); // colección vacía si falla
        }
    }

    /**
     * Series temporales
     */
    public function timeSeriesAnalysis($startDate, $endDate, $granularity, $sucursalId = null, $productoId = null): Collection
    {
        try {
            $query = HechoVenta::join('dimension_tiempo', 'hecho_ventas.tiempo_id', '=', 'dimension_tiempo.id');

            if($sucursalId) $query->where('sucursal_id', $sucursalId);
            if($productoId) $query->where('producto_id', $productoId);

            $dateFormat = match($granularity){
                'hourly'=>'YYYY-MM-DD HH24:00',
                'daily'=>'YYYY-MM-DD',
                'weekly'=>'IYYY-IW',
                'monthly'=>'YYYY-MM',
                default=>'YYYY-MM-DD'
            };

            return $query->select([
                DB::raw("to_char(dimension_tiempo.fecha, '$dateFormat') as periodo"),
                DB::raw('SUM(monto_total) as ventas'),
                DB::raw('SUM(ganancia) as ganancia'),
                DB::raw('SUM(cantidad) as cantidad')
            ])
            ->whereBetween('dimension_tiempo.fecha', [$startDate,$endDate])
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        } catch (\Exception $e) {
            Log::error('OLAPService timeSeriesAnalysis error: '.$e->getMessage());
            return collect(); // colección vacía
        }
    }

    /**
     * Drill down 3D
     */
    public function drillDown3D($dimension, $breakdown, $coordinates, $filters = []): Collection
    {
        try {
            $query = HechoVenta::query();

            foreach($filters as $field => $value){
                if(in_array($field, ['sucursal_id','producto_id','tiempo_id'])){
                    $query->where($field, $value);
                }
            }

            return match($dimension){
                'tiempo' => $this->drillDownTiempo($query, $breakdown, $coordinates),
                'sucursal' => $this->drillDownSucursal($query, $breakdown, $coordinates),
                'producto' => $this->drillDownProducto($query, $breakdown, $coordinates),
                default => collect(),
            };

        } catch (\Exception $e) {
            Log::error('OLAPService drillDown3D error: '.$e->getMessage());
            return collect(); // colección vacía
        }
    }

    protected function drillDownTiempo($query, $breakdown, $coordinates): Collection
    {
        if(empty($coordinates['start_date']) || empty($coordinates['end_date'])) return collect();

        $query->join('dimension_tiempo','hecho_ventas.tiempo_id','=','dimension_tiempo.id')
              ->select(
                  DB::raw("DATE_TRUNC('{$breakdown}', dimension_tiempo.fecha) as periodo"),
                  DB::raw('SUM(monto_total) as ventas'),
                  DB::raw('SUM(ganancia) as ganancia'),
                  DB::raw('SUM(cantidad) as cantidad')
              )
              ->whereBetween('dimension_tiempo.fecha', [$coordinates['start_date'],$coordinates['end_date']])
              ->groupBy('periodo')
              ->orderBy('periodo');

        return $query->get();
    }

    protected function drillDownSucursal($query, $breakdown, $coordinates): Collection
    {
        $sucursalIds = $coordinates['sucursal_ids'] ?? [];
        if(empty($sucursalIds)) return collect();

        $query->with('sucursal')
              ->select(
                  'sucursal_id',
                  DB::raw('SUM(monto_total) as ventas'),
                  DB::raw('SUM(ganancia) as ganancia'),
                  DB::raw('SUM(cantidad) as cantidad')
              )
              ->whereIn('sucursal_id', $sucursalIds)
              ->groupBy('sucursal_id');

        return $query->get();
    }

    protected function drillDownProducto($query, $breakdown, $coordinates): Collection
    {
        $productoIds = $coordinates['producto_ids'] ?? [];
        if(empty($productoIds)) return collect();

        $query->with('producto')
              ->select(
                  'producto_id',
                  DB::raw('SUM(monto_total) as ventas'),
                  DB::raw('SUM(ganancia) as ganancia'),
                  DB::raw('SUM(cantidad) as cantidad')
              )
              ->whereIn('producto_id', $productoIds)
              ->groupBy('producto_id');

        return $query->get();
    }

    /**
     * Asigna colores según valor
     */
    protected function getColorForValue($value): string
    {
        if($value < 1000) return '#ff6b6b';
        if($value < 5000) return '#ffe066';
        return '#51cf66';
    }
}
