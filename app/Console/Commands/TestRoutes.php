<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;

class TestRoutes extends Command
{
    protected $signature = 'routes:test';
    protected $description = 'Testea todas las rutas registradas con un controlador asignado (GET sin parámetros)';

    public function handle()
    {
        $this->info("🔎 Probando rutas GET con controlador y sin parámetros...");

        $routes = collect(Route::getRoutes())->filter(function ($route) {
            return in_array('GET', $route->methods())
                && !Str::contains($route->uri(), '{')
                && isset($route->action['controller']); // solo rutas con controlador
        });

        foreach ($routes as $route) {
            $uri = '/' . ltrim($route->uri(), '/');
            $controller = $route->action['controller'];

            $this->info("Probando GET $uri ($controller) ...");

            try {
                App::call($controller);
                $this->info("✅ OK");
            } catch (\Exception $e) {
                $this->error("❌ ERROR: " . $e->getMessage());
            }
        }

        $this->info("✔️ Testeo completado.");
        return Command::SUCCESS;
    }
}
