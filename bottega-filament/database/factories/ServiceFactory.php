<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 5, 400),
        ];
    }
}
