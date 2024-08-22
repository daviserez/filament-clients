<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->lastName(),
            'firstname' => fake()->firstName(),
            'primary_phone' => fake()->phoneNumber(),
            'secondary_phone' => fake()->phoneNumber(),
            'street' => fake()->streetName().' '.fake()->buildingNumber,
            'postcode' => fake()->postcode(),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'email' => fake()->email(),
            'notes' => fake()->sentence,
        ];
    }
}
