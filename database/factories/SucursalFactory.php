<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SucursalFactory extends Factory
{
    public function definition()
    {
        $ciudades = ['Buenos Aires', 'Córdoba', 'Rosario', 'Mendoza', 'La Plata', 'Mar del Plata', 'Salta', 'Santa Fe', 'San Juan', 'Resistencia'];
        $paises = ['Argentina', 'Chile', 'Uruguay', 'Paraguay', 'Brasil'];
        
        return [
            'nombre' => 'Sucursal ' . $this->faker->city,
            'direccion' => $this->faker->streetAddress,
            'ciudad' => $this->faker->randomElement($ciudades),
            'pais' => $this->faker->randomElement($paises),
            'codigo_postal' => $this->faker->postcode,
            'telefono' => $this->faker->phoneNumber,
            'email' => $this->faker->companyEmail,
            'latitud' => $this->faker->latitude(-34.0, -38.0),
            'longitud' => $this->faker->longitude(-58.0, -62.0),
            'configuracion' => json_encode([
                'port' => $this->faker->numberBetween(8100, 8200),
                'database' => 'sucursal_' . $this->faker->word
            ]),
            'activa' => $this->faker->boolean(80), // 80% de probabilidad de estar activa
        ];
    }
}