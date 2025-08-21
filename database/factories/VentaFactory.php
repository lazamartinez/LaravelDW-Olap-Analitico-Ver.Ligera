<?php

namespace Database\Factories;

use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

class VentaFactory extends Factory
{
    public function definition()
    {
        $metodosPago = ['efectivo', 'tarjeta', 'transferencia', 'credito'];
        
        return [
            'sucursal_id' => Sucursal::inRandomOrder()->first()->id,
            'codigo_transaccion' => 'V-' . now()->format('Ymd') . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'fecha_venta' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'total' => $this->faker->randomFloat(2, 100, 5000),
            'impuestos' => $this->faker->randomFloat(2, 0.1, 0.21),
            'descuentos' => $this->faker->randomFloat(2, 0, 100),
            'metodo_pago' => $this->faker->randomElement($metodosPago),
            'estado' => 'completada',
            'empleado_id' => 1,
            'procesada_olap' => $this->faker->boolean(90),
            'detalles' => json_encode([
                'cliente' => $this->faker->name,
                'vendedor' => 'Empleado ' . $this->faker->firstName,
                'notas' => $this->faker->sentence(5)
            ]),
        ];
    }
}