<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run()
    {
        $sucursales = Sucursal::all();

        $productos = [
            // Tecnología
            ['codigo' => 'TEC001', 'nombre' => 'Laptop Gaming', 'descripcion' => 'Laptop para gaming de alto rendimiento', 'precio' => 1250.00, 'costo' => 850.00, 'stock' => 15, 'categoria' => 'Tecnología', 'marca' => 'GamingBrand'],
            ['codigo' => 'TEC002', 'nombre' => 'Smartphone Premium', 'descripcion' => 'Teléfono inteligente de última generación', 'precio' => 850.00, 'costo' => 550.00, 'stock' => 25, 'categoria' => 'Tecnología', 'marca' => 'PhoneTech'],
            ['codigo' => 'TEC003', 'nombre' => 'Tablet 10"', 'descripcion' => 'Tablet con pantalla de 10 pulgadas', 'precio' => 450.00, 'costo' => 280.00, 'stock' => 18, 'categoria' => 'Tecnología', 'marca' => 'TabTech'],
            ['codigo' => 'TEC004', 'nombre' => 'Monitor 24"', 'descripcion' => 'Monitor LED 24 pulgadas Full HD', 'precio' => 300.00, 'costo' => 180.00, 'stock' => 12, 'categoria' => 'Tecnología', 'marca' => 'ViewPlus'],
            
            // Hogar
            ['codigo' => 'HOG001', 'nombre' => 'Aspiradora Inteligente', 'descripcion' => 'Aspiradora robot con navegación inteligente', 'precio' => 350.00, 'costo' => 220.00, 'stock' => 8, 'categoria' => 'Hogar', 'marca' => 'CleanHome'],
            ['codigo' => 'HOG002', 'nombre' => 'Cafetera Automática', 'descripcion' => 'Cafetera programable con molinillo integrado', 'precio' => 180.00, 'costo' => 110.00, 'stock' => 14, 'categoria' => 'Hogar', 'marca' => 'BrewMaster'],
            
            // Deportes
            ['codigo' => 'DEP001', 'nombre' => 'Bicicleta Montaña', 'descripcion' => 'Bicicleta de montaña 21 velocidades', 'precio' => 420.00, 'costo' => 280.00, 'stock' => 6, 'categoria' => 'Deportes', 'marca' => 'TrailBlazer'],
            ['codigo' => 'DEP002', 'nombre' => 'Set de Pesas', 'descripcion' => 'Set de pesas ajustables 20kg', 'precio' => 150.00, 'costo' => 90.00, 'stock' => 10, 'categoria' => 'Deportes', 'marca' => 'FitPro'],
        ];

        foreach ($productos as $productoData) {
            // Asignar sucursal aleatoria antes de crear
            $productoData['sucursal_id'] = $sucursales->random()->id;

            Producto::create($productoData);
        }

        // Crear productos adicionales usando la factory y asignar sucursal aleatoria
        Producto::factory(20)->create()->each(function ($producto) use ($sucursales) {
            $producto->sucursal_id = $sucursales->random()->id;
            $producto->save();
        });
    }
}
