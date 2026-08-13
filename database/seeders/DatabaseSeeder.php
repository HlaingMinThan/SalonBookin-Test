<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => config('app.admin_email'),
        ]);

        $services = collect(['Haircut', 'Hair Color', 'Manicure'])->map(
            fn (string $name) => Service::create(['name' => $name]),
        );

        foreach ($services as $service) {
            foreach (range(1, 7) as $dayOffset) {
                $date = now()->addDays($dayOffset)->format('Y-m-d');
                foreach (['09:00', '10:00', '11:00', '13:00', '14:00', '15:00'] as $time) {
                    Slot::create([
                        'service_id' => $service->id,
                        'date' => $date,
                        'time' => $time,
                    ]);
                }
            }
        }
    }
}
