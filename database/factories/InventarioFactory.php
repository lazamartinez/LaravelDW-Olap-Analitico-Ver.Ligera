<?php

namespace Database\Factories;

use App\Models\Sucursal;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventarioFactory extends Factory
{
    public function definition()
    {
        return [
            'sucursal_id' => Sucursal::inRandomOrder()->first()->id,
            'producto_id' => Producto::inRandomOrder()->first()->id,
            'cantidad' => $this->faker->numberBetween(0, 100),
            'minimo_stock' => $this->faker->numberBetween(5, 15),
            'ubicacion' => 'Estante ' . $this->faker->randomLetter . $this->faker->numberBetween(1, 20),
        ];
    }
}