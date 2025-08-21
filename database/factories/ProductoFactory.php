<?php

namespace Database\Factories;

use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    public function definition()
    {
        $categorias = ['Tecnología', 'Hogar', 'Deportes', 'Ropa', 'Alimentos', 'Electrodomésticos', 'Juguetes', 'Libros'];
        $marcas = ['Samsung', 'LG', 'Sony', 'Apple', 'Philips', 'Nike', 'Adidas', 'Puma', 'KitchenAid', 'Dell'];
        
        $costo = $this->faker->randomFloat(2, 50, 500);
        $margen = $this->faker->randomFloat(2, 0.2, 0.5); // Margen de 20% a 50%
        $precio = $costo * (1 + $margen);
        
        return [
            'sucursal_id' => Sucursal::inRandomOrder()->first()->id,
            'codigo' => strtoupper($this->faker->randomLetter . $this->faker->randomLetter) . $this->faker->numberBetween(100, 999),
            'nombre' => $this->faker->words(3, true),
            'descripcion' => $this->faker->sentence(10),
            'precio' => $precio,
            'costo' => $costo,
            'stock' => $this->faker->numberBetween(0, 100),
            'categoria' => $this->faker->randomElement($categorias),
            'marca' => $this->faker->randomElement($marcas),
            'atributos' => json_encode([
                'color' => $this->faker->colorName,
                'peso' => $this->faker->randomFloat(2, 0.1, 10),
                'dimensiones' => [
                    'alto' => $this->faker->numberBetween(10, 100),
                    'ancho' => $this->faker->numberBetween(10, 100),
                    'profundidad' => $this->faker->numberBetween(10, 100)
                ]
            ]),
        ];
    }
}