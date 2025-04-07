<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Misplacement;
use App\Models\LostDocument;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Carbon\Carbon;

class MisplacementSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $peopleId = '73862ffd-2f9c-4480-d1d6-08dd72c661fc';

        $fechas = collect([
            Carbon::now()->subDays(5),
            Carbon::now()->subDays(4),
            Carbon::now()->subDays(3),
            Carbon::now()->subDays(2),
            Carbon::now()->subDay(),
            Carbon::now()
        ]);

        for ($i = 0; $i < 100; $i++) {
            // Fecha aleatoria entre las disponibles
            $fecha = $fechas->random()->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59));

            // Crear misplacement
            $misplacement = Misplacement::create([
                'lost_status_id' => 3,
                'people_id' => $peopleId,
                'hash_code' => Str::random(10),
                'document_number' => rand(1000, 9999),
                'document_api_id' => rand(1, 50),
                'registration_date' => $fecha,
                'validation_date' => null,
                'observations' => 'Generado automáticamente para prueba',
                'cancellation_date' => null,
                'cancellation_reason_id' => null,
                'cancellation_reason_description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Crear lost_document relacionado
            LostDocument::create([
                'misplacement_id' => $misplacement->id,
                'document_type_id' => rand(1, 5),
                'document_number' => strtoupper(Str::random(3)) . rand(100, 999),
                'document_owner' => $faker->name,
                'specification' => $faker->sentence(3),
                'registration_date' => $fecha,
                'active' => $faker->boolean(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
