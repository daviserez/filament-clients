<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\DetailsAppointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'email' => 'test-data@bottega.ch',
        ]);

        User::factory()->create([
            'email' => 'test-empty@bottega.ch',
        ]);

        User::factory()->create([
            'email' => 'legacy@bottega.ch',
        ]);

        $clients = Client::factory(100)->create(['team_id' => 1]);
        $services = Service::factory(100)->create(['team_id' => 1]);

        $appointments = collect([]);

        // Create between 1 and 5 appointments for each client.
        foreach ($clients as $client) {
            $appointments = $appointments->merge(
                Appointment::factory(rand(1, 5))
                    ->for($client)
                    ->create()
            );
        }

        // Create between 1 and 8 details for each appointment.
        foreach ($appointments as $appointment) {
            for ($i = 0; $i < rand(1, 8); $i++) {
                DetailsAppointment::factory()
                    ->for($appointment)
                    ->for($services->random())
                    ->create();
            }
        }
    }
}
