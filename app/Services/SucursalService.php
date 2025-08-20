<?php

namespace App\Services;

use App\Models\Sucursal;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class SucursalService
{
    public function setupDockerContainer(Sucursal $sucursal)
    {
        // Lógica para configurar contenedor Docker
        $port = $this->getNextAvailablePort();
        
        return [
            'port' => $port,
            'status' => 'pending',
            'container_id' => null,
            'image' => 'sucursal-image:latest'
        ];
    }

    public function removeDockerContainer(Sucursal $sucursal)
    {
        // Lógica para eliminar contenedor Docker
        if ($sucursal->docker_config && isset($sucursal->docker_config['container_id'])) {
            try {
                $process = new Process(['docker', 'rm', '-f', $sucursal->docker_config['container_id']]);
                $process->run();
            } catch (\Exception $e) {
                Log::error("Error removing container: " . $e->getMessage());
            }
        }
    }

    public function calculateMetrics(Sucursal $sucursal)
    {
        // Calcular métricas para la sucursal
        return [
            'ventas_totales' => $sucursal->ventas()->sum('total'),
            'ventas_promedio' => $sucursal->ventas()->avg('total'),
            'productos_vendidos' => $sucursal->productos()->count(),
            'transacciones_activas' => $sucursal->transaccionesOrigen()->where('estado', 'pendiente')->count() + 
                                     $sucursal->transaccionesDestino()->where('estado', 'pendiente')->count()
        ];
    }

    public function getRealtimeTransactions(Sucursal $sucursal)
    {
        return [
            'transacciones_salientes' => $sucursal->transaccionesOrigen()
                ->with('destinoSucursal', 'productos')
                ->whereIn('estado', ['pendiente', 'en_transito'])
                ->latest()
                ->limit(5)
                ->get(),
            'transacciones_entrantes' => $sucursal->transaccionesDestino()
                ->with('origenSucursal', 'productos')
                ->whereIn('estado', ['pendiente', 'en_transito'])
                ->latest()
                ->limit(5)
                ->get()
        ];
    }

    protected function getNextAvailablePort()
    {
        $usedPorts = Sucursal::whereNotNull('configuracion->port')
            ->pluck('configuracion->port')
            ->toArray();

        $port = 8100;
        while (in_array($port, $usedPorts)) {
            $port++;
        }
        return $port;
    }
}