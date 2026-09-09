<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Ingreso;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ingreso>
 */
class IngresoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'categoria_id' => Categoria::factory()->ingreso(),
            'fecha' => fake()->date(),
            'fuente' => fake()->company(),
            'monto' => fake()->numberBetween(500, 5000).'.00',
            'notas' => fake()->optional()->sentence(),
        ];
    }
}
