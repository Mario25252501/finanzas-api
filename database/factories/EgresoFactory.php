<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Egreso;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Egreso>
 */
class EgresoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'categoria_id' => Categoria::factory()->egreso(),
            'subcategoria_id' => null,
            'fecha' => fake()->date(),
            'descripcion' => fake()->sentence(3),
            'monto' => fake()->numberBetween(20, 1000).'.00',
            'notas' => fake()->optional()->sentence(),
        ];
    }
}
