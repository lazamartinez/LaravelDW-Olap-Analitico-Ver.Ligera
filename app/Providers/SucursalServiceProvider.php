<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class SucursalServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('sucursal.docker', function ($app) {
            return new class {
                public function createContainer(Sucursal $sucursal)
                {
                    $containerName = "sucursal_{$sucursal->id}";
                    $port = $this->findAvailablePort();
                    
                    $process = new Process([
                        'docker', 'run', '-d',
                        '--name', $containerName,
                        '-p', "{$port}:80",
                        '-e', "SUCURSAL_ID={$sucursal->id}",
                        '-e', "DB_HOST=host.docker.internal",
                        '-e', "DB_PORT=5432",
                        '-e', "DB_DATABASE=sucursal_{$sucursal->id}",
                        '-e', "DB_USERNAME=postgres",
                        '-e', "DB_PASSWORD=secret",
                        '--network', 'sucursales-network',
                        'sucursal-image:latest'
                    ]);
                    
                    $process->run();
                    
                    if (!$process->isSuccessful()) {
                        Log::error("Error al crear contenedor: " . $process->getErrorOutput());
                        throw new \RuntimeException("Error al crear contenedor Docker");
                    }
                    
                    return [
                        'container_id' => trim($process->getOutput()),
                        'port' => $port,
                        'status' => 'running'
                    ];
                }
                
                protected function findAvailablePort()
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
            };
        });
    }
}