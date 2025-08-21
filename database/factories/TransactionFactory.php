<?php

namespace Database\Factories;

use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition()
    {
        $origen = Sucursal::inRandomOrder()->first()->id;
        
        // Asegurarse de que el destino sea diferente al origen
        do {
            $destino = Sucursal::inRandomOrder()->first()->id;
        } while ($destino === $origen);
        
        $estados = ['pendiente', 'en_transito', 'completada', 'cancelada'];
        $prioridades = ['low', 'medium', 'high'];
        
        return [
            'origen_sucursal_id' => $origen,
            'destino_sucursal_id' => $destino,
            'codigo' => 'TRX-' . now()->format('Ymd') . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'estado' => $this->faker->randomElement($estados),
            'notas' => $this->faker->sentence(6),
            'prioridad' => $this->faker->randomElement($prioridades),
            'user_id' => 1,
        ];
    }
}