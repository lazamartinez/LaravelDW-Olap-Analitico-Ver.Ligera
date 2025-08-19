<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sucursal;
use Illuminate\Support\Facades\File;

class GenerateDockerCompose extends Command
{
    protected $signature = 'docker:generate';
    protected $description = 'Generate dynamic docker-compose file for branches';

    public function handle()
    {
        $sucursales = Sucursal::all();
        $services = [];
        
        foreach ($sucursales as $sucursal) {
            $services["sucursal_{$sucursal->id}"] = [
                'image' => 'sucursal-image:latest',
                'container_name' => "sucursal_{$sucursal->id}",
                'ports' => ["{$sucursal->configuracion['port']}:80"],
                'environment' => [
                    "SUCURSAL_ID={$sucursal->id}",
                    "DB_HOST=db",
                    "DB_PORT=5432",
                    "DB_DATABASE=sucursal_{$sucursal->id}",
                    "DB_USERNAME=postgres",
                    "DB_PASSWORD=secret"
                ],
                'networks' => ['sucursales-network'],
                'restart' => 'unless-stopped'
            ];
        }
        
        $compose = [
            'version' => '3.8',
            'services' => $services,
            'networks' => [
                'sucursales-network' => [
                    'driver' => 'bridge'
                ]
            ]
        ];
        
        File::put(
            base_path('docker-compose.sucursales.yml'),
            $this->arrayToYaml($compose)
        );
        
        $this->info('Docker compose file generated successfully!');
    }
    
    protected function arrayToYaml(array $data, $indent = 0)
    {
        $yaml = '';
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $yaml .= str_repeat(' ', $indent) . "$key:\n";
                $yaml .= $this->arrayToYaml($value, $indent + 2);
            } else {
                $yaml .= str_repeat(' ', $indent) . "$key: $value\n";
            }
        }
        return $yaml;
    }
}