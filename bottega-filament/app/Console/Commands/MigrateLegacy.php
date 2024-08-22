<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\DetailsAppointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateLegacy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-legacy {sqlFile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sqlFile = $this->argument('sqlFile');

        $this->info('Create a user, all records will be associed to the first user.');

        // Create temporary database

        $tmpDbName = $this->ask('What is the name of the temporary database? Let empty to create a random one and drop it after migration (must have user permissions).');
        $tmpDbCreate = false;
        if (! $tmpDbName) {
            $tmpDbName = 'temp_db_'.time();
            DB::statement("CREATE DATABASE `$tmpDbName`");
            $this->info("Temporary database $tmpDbName created.");
            $tmpDbCreate = true;
        }
        DB::statement("use `$tmpDbName`");

        // Import SQL file into temporary database
        DB::unprepared(file_get_contents($sqlFile));

        try {
            // Migrate data from temporary to new database
            $this->migrateData($tmpDbName);
        } finally {
            if ($tmpDbCreate) {
                // Drop temporary database
                DB::statement("DROP DATABASE `$tmpDbName`");
                $this->info("Temporary database $tmpDbName dropped.");
            }
        }
    }

    private function migrateData($temporaryDatabase)
    {
        // Implement the logic to migrate data from the temporary database to the new database.
        // This could involve reading tables and data from the temporary database
        // and inserting them into the new database.
        $this->info("Data migration from $temporaryDatabase to the new database started.");
        $legacyClients = DB::table('clients')
            ->select()
            ->get();

        $legacyServices = DB::table('services')
            ->select()
            ->get();

        $legacyAppointments = DB::table('appointment')
            ->select()
            ->get();

        $legacyAppointmentsDetails = DB::table('appointmentdetails')
            ->select()
            ->get();

        $mapClientsId = collect([]);
        $mapServicesId = collect([]);
        $mapAppointmentsId = collect([]);

        $dbName = $this->ask('What is the target DB name?');
        DB::statement("use `$dbName`");

        $targetUser = $this->ask('What is the email of the user to link data?');
        $teamId = User::where('email', $targetUser)->first()->team_id;

        foreach ($legacyClients as $legacyClient) {
            $client = Client::create(
                [
                    'team_id' => $teamId,
                    'name' => $legacyClient->name,
                    'firstname' => $legacyClient->firstname,
                    'primary_phone' => $legacyClient->phone,
                    'street' => $legacyClient->address,
                    'city' => $legacyClient->city,
                    'postcode' => $legacyClient->npa,
                ]
            );

            $mapClientsId->put($legacyClient->id, $client->id);
        }

        foreach ($legacyServices as $legacyService) {
            $service = Service::create(
                [
                    'team_id' => $teamId,
                    'name' => $legacyService->service,
                    'price' => $legacyService->price,
                ]
            );

            $mapServicesId->put($legacyService->id, [$service->id, $service->price]);
        }

        foreach ($legacyAppointments as $legacyAppointment) {

            // Don't migrate appointment without date.
            if ($legacyAppointment->date) {
                $appointment = Appointment::create(
                    [
                        'client_id' => $mapClientsId->get($legacyAppointment->idclient),
                        'appointed_at' => $legacyAppointment->date,
                    ]
                );
            }

            $mapAppointmentsId->put($legacyAppointment->idappointment, $appointment->id);
        }

        foreach ($legacyAppointmentsDetails as $legacyAppointmentsDetail) {

            $_service = $mapServicesId->get($legacyAppointmentsDetail->idservice);

            $appointment = DetailsAppointment::create(
                [
                    'appointment_id' => $mapAppointmentsId->get($legacyAppointmentsDetail->idappointment),
                    'service_id' => $_service[0],
                    'price' => $legacyAppointmentsDetail->price,
                    'service_price' => $_service[1],
                    'color' => $legacyAppointmentsDetail->color,
                    'comment' => $legacyAppointmentsDetail->comment,
                ]
            );
        }

        $this->info('Data migration completed.');
    }
}
