<?php

namespace App\Services;

use App\Models\Sucursal;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;

class RealtimeService
{
    public function getSucursales3DData()
    {
        return Cache::remember('sucursales_3d_data', 60, function () {
            return Sucursal::withCount(['ventas', 'productos'])
                ->with(['ventas' => function ($query) {
                    $query->selectRaw('sucursal_id, SUM(total) as total_ventas')
                        ->groupBy('sucursal_id');
                }])
                ->get()
                ->map(function ($sucursal) {
                    return [
                        'id' => $sucursal->id,
                        'nombre' => $sucursal->nombre,
                        'ciudad' => $sucursal->ciudad,
                        'lat' => $sucursal->latitud,
                        'lng' => $sucursal->longitud,
                        'ventas' => $sucursal->ventas->first()->total_ventas ?? 0,
                        'productos' => $sucursal->productos_count,
                        'activa' => $sucursal->activa,
                        'transacciones' => $this->getActiveTransactions($sucursal)
                    ];
                });
        });
    }

    protected function getActiveTransactions(Sucursal $sucursal)
    {
        return Transaction::where(function ($query) use ($sucursal) {
            $query->where('origen_sucursal_id', $sucursal->id)
                ->orWhere('destino_sucursal_id', $sucursal->id);
        })
            ->whereIn('estado', ['pendiente', 'en_transito'])
            ->with(['origenSucursal', 'destinoSucursal', 'productos'])
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'codigo' => $transaction->codigo,
                    'origen' => $transaction->origenSucursal->nombre,
                    'destino' => $transaction->destinoSucursal->nombre,
                    'estado' => $transaction->estado,
                    'productos' => $transaction->productos->count(),
                    'created_at' => $transaction->created_at->diffForHumans()
                ];
            });
    }

    public function getOLAPCube3DData($dimensions, $measures)
    {
        // Implementación para generar datos del cubo OLAP en 3D
        // Puede usar el OLAPService internamente
    }
}
