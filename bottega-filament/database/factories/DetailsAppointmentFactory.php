<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DetailsAppointment>
 */
class DetailsAppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'color' => fake()->word,
            'comment' => fake()->sentence,
            'price' => fake()->randomFloat(2, 5, 400),
            'service_price' => fake()->randomFloat(2, 5, 400),
            'service_id' => Service::factory(),
            'appointment_id' => Appointment::factory(),
        ];
    }
}
