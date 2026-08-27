<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Kendaraan;
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
        User::factory()->admin()->create([
            'name' => 'Admin Utama',
            'email' => 'admin@example.com',
        ]);

        User::factory()->penyetuju()->create([
            'name' => 'Penyetuju Satu',
            'email' => 'penyetuju@example.com',
        ]);

        User::factory()->penyetuju()->create();

        Kendaraan::factory()->count(5)->create();
        Driver::factory()->count(5)->create();

        User::factory()->create();
    }
}
